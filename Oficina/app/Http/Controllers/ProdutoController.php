<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Produto;
use App\Services\ReservaEstoque;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );

        $validacao = Validator::make($request->query(), [
            'busca' => ['nullable', 'string', 'max:255'],
            'estoque_baixo' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ], [
            'estoque_baixo.boolean' => 'Selecione um filtro de estoque válido.',
            'busca.string' => 'Informe um texto para pesquisar.',
            'busca.max' => 'A pesquisa deve ter até 255 caracteres.',
            'page.integer' => 'A página deve ser um número inteiro.',
            'page.min' => 'A página deve ser maior que zero.',
        ]);

        if ($validacao->fails()) {
            return redirect()->route('produtos.index')
                ->withErrors($validacao)
                ->withInput($request->except('page'));
        }

        $dados = $validacao->validated();
        $busca = trim($dados['busca'] ?? '');

        $consulta = Produto::query();

        if ($busca !== '') {
            $consulta->where(function ($consultaBusca) use ($busca) {
                $consultaBusca->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('descricao', 'like', '%' . $busca . '%')
                    ->orWhereHas('categoria', function ($categorias) use ($busca) {
                        $categorias->where('nome', 'like', '%' . $busca . '%');
                    });
            });
        }

        if (!empty($dados['estoque_baixo'])) {
            $consulta->whereColumn('estoque', '<=', 'estoque_minimo');
        }

        $valorEstoque = (clone $consulta)->sum(DB::raw('estoque * preco'));

        $produtos = $consulta->with('categoria')
            ->comReservas()
            ->orderBy('nome')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('produtos.index', compact('produtos', 'valorEstoque'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();

        return view('produtos.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $dados = $this->prepararImagem($request, $dados);

        $produto = ReservaEstoque::transacao(
            fn() => Produto::create($dados)
        );

        return redirect()->route('produtos.index')
            ->with('mensagem', "O produto '{$produto->nome}' foi inserido com sucesso!");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produto = Produto::with('categoria')
            ->comReservas()
            ->findOrFail($id);

        return view('produtos.show', compact('produto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produto = Produto::comReservas()->findOrFail($id);
        $categorias = Categoria::all();

        return view('produtos.edit', compact('produto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $produto = Produto::findOrFail($id);
        $dados = $this->validar($request, false);

        $quantidadeRecebida = (int) ($dados['quantidade_recebida'] ?? 0);

        unset($dados['quantidade_recebida']);

        $dados = $this->prepararImagem($request, $dados);

        // Soma a entrada ao saldo atual bloqueado e completa as reservas pendentes.
        try {
            $alterado = ReservaEstoque::transacao(function () use ($id, $dados, $quantidadeRecebida) {
                $produto = Produto::lockForUpdate()->findOrFail($id);

                if ($quantidadeRecebida > 2147483647 - $produto->estoque) {
                    throw ValidationException::withMessages([
                        'quantidade_recebida' => 'A entrada ultrapassa o limite permitido para o estoque.',
                    ]);
                }

                if ($quantidadeRecebida > 0) {
                    $dados['estoque'] = $produto->estoque + $quantidadeRecebida;
                }

                return $produto->update($dados) ? $produto : null;
            });
        } catch (\Throwable $erro) {
            if ($request->hasFile('imagem_arquivo') && isset($dados['imagem'])) {
                File::delete(public_path($dados['imagem']));
            }

            throw $erro;
        }

        if ($alterado) {
            $produto = $alterado;

            return redirect()->route('produtos.index')
                ->with('mensagem', $quantidadeRecebida > 0
                    ? "Produto atualizado! Entrada de {$quantidadeRecebida} unidade(s) registrada. Estoque físico: {$produto->estoque}."
                    : "O produto '{$produto->nome}' foi alterado com sucesso!");
        }

        return back()->withInput()
            ->withErrors([
                'produto' => 'Não foi possível alterar o produto.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produto = Produto::findOrFail($id);
        $nomeProduto = $produto->nome;

        try {
            $excluido = ReservaEstoque::transacao(function () use ($id) {
                return Produto::lockForUpdate()->findOrFail($id)->delete();
            });

            if (!$excluido) {
                return redirect()->route('produtos.index')
                    ->with('erro', 'Não foi possível excluir o produto.');
            }
        } catch (QueryException $erro) {
            if (($erro->errorInfo[1] ?? null) == 1451) {
                return redirect()->route('produtos.index')
                    ->with('erro', 'Este produto possui registros vinculados, como itens de ordens de serviço, e não pode ser excluído.');
            }

            throw $erro;
        }

        return redirect()->route('produtos.index')
            ->with('mensagem', "O produto '{$nomeProduto}' foi excluído com sucesso!");
    }

    private function validar(Request $request, bool $cadastro = true)
    {
        $regras = [
            'nome' => ['required', 'string', 'max:255'],
            'categoria_id' => [
                'required',
                'integer',
                'exists:categorias,id',
            ],
            'descricao' => ['required', 'string'],
            'preco' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:99999999.99',
            ],
            'estoque_minimo' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'imagem_arquivo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:2048',
            ],
            'imagem_url' => [
                'nullable',
                'url:http,https',
                'max:255',
            ],
        ];

        $regras['estoque'] = $cadastro
            ? ['required', 'integer', 'min:0', 'max:2147483647']
            : ['prohibited'];

        $regras['quantidade_recebida'] = $cadastro
            ? ['prohibited']
            : ['nullable', 'integer', 'min:0', 'max:2147483647'];

        $dados = $request->validate($regras, [
            'nome.required' => 'Informe o nome do produto.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome deve ter até 255 caracteres.',

            'categoria_id.required' => 'Selecione uma categoria.',
            'categoria_id.integer' => 'Selecione uma categoria válida.',
            'categoria_id.exists' => 'Selecione uma categoria cadastrada.',

            'descricao.required' => 'Informe a descrição do produto.',
            'descricao.string' => 'A descrição deve ser um texto.',

            'preco.required' => 'Informe o preço do produto.',
            'preco.numeric' => 'O preço deve ser um número.',
            'preco.decimal' => 'O preço deve ter no máximo duas casas decimais.',
            'preco.min' => 'O preço não pode ser negativo.',
            'preco.max' => 'O preço máximo permitido é R$ 99.999.999,99.',

            'estoque.required' => 'Informe o estoque inicial.',
            'estoque.integer' => 'O estoque deve ser um número inteiro.',
            'estoque.min' => 'O estoque não pode ser negativo.',
            'estoque.max' => 'A quantidade em estoque ultrapassa o limite permitido.',
            'estoque.prohibited' => 'O estoque atual é somente para consulta. Informe a quantidade recebida para registrar uma entrada.',

            'quantidade_recebida.prohibited' => 'No cadastro, informe a quantidade no campo Estoque inicial.',
            'quantidade_recebida.integer' => 'A quantidade recebida deve ser um número inteiro.',
            'quantidade_recebida.min' => 'A quantidade recebida não pode ser negativa.',
            'quantidade_recebida.max' => 'A quantidade recebida ultrapassa o limite permitido.',

            'estoque_minimo.required' => 'Informe o estoque mínimo.',
            'estoque_minimo.integer' => 'O estoque mínimo deve ser um número inteiro.',
            'estoque_minimo.min' => 'O estoque mínimo não pode ser negativo.',
            'estoque_minimo.max' => 'O estoque mínimo ultrapassa o limite permitido.',

            'imagem_arquivo.uploaded' => 'Não foi possível enviar a imagem. Confira o tamanho do arquivo.',
            'imagem_arquivo.image' => 'Selecione um arquivo de imagem válido.',
            'imagem_arquivo.mimes' => 'A imagem deve ser JPG, JPEG, PNG, WEBP ou GIF.',
            'imagem_arquivo.max' => 'A imagem deve ter no máximo 2 MB.',

            'imagem_url.url' => 'Informe um endereço de imagem começando com http:// ou https://.',
            'imagem_url.max' => 'O endereço da imagem deve ter até 255 caracteres.',
        ]);

        if ($cadastro) {
            unset($dados['quantidade_recebida']);
        } else {
            unset($dados['estoque']);
        }

        return $dados;
    }

    private function prepararImagem(Request $request, array $dados)
    {
        unset($dados['imagem_arquivo'], $dados['imagem_url']);

        if ($request->hasFile('imagem_arquivo')) {
            $imagem = $request->file('imagem_arquivo');
            $nomeImagem = $imagem->hashName();

            File::ensureDirectoryExists(public_path('img/produtos'));

            $imagem->move(public_path('img/produtos'), $nomeImagem);

            $dados['imagem'] = 'img/produtos/' . $nomeImagem;
        } elseif ($request->filled('imagem_url')) {
            $dados['imagem'] = $request->input('imagem_url');
        }

        return $dados;
    }
}

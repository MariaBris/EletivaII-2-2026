<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validacao = Validator::make($request->query(), [
            'busca' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ], [
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

        $consulta = Produto::with('categoria'); // Prepara a consulta dos produtos com suas categorias

        if ($busca !== '') {
            $consulta->where(function ($consultaBusca) use ($busca) {
                $consultaBusca->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('descricao', 'like', '%' . $busca . '%')
                    ->orWhereHas('categoria', function ($categorias) use ($busca) {
                        $categorias->where('nome', 'like', '%' . $busca . '%');
                    });
            });
        }

        $produtos = $consulta->orderBy('nome')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('produtos.index', compact('produtos')); // Retorna a view de listagem enviando a variável com os produtos
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all(); // Busca todas as categorias para preencher o select do formulário

        return view('produtos.create', compact('categorias')); // Abre a tela de cadastro enviando as categorias
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $this->validar($request); // Recolhe somente os dados validados do formulário

        $dados = $this->prepararImagem($request, $dados);

        $produto = Produto::create($dados); // Grava o novo produto no banco de dados

        return redirect()->route('produtos.index')
            ->with('mensagem', "O produto '{$produto->nome}' foi inserido com sucesso!");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produto = Produto::with('categoria')->findOrFail($id); // Procura o produto pelo ID ou mostra erro 404 se não encontrar

        return view('produtos.show', compact('produto')); // Retorna a tela de consulta/exclusão
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produto = Produto::findOrFail($id); // Encontra o produto que vai ser editado
        $categorias = Categoria::all(); // Busca as categorias para o select continuar funcional na edição

        return view('produtos.edit', compact('produto', 'categorias')); // Abre o formulário de edição com os dados atuais
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $produto = Produto::findOrFail($id); // Localiza o registo que será alterado
        $dados = $this->validar($request); // Pega os novos dados validados do formulário

        $dados = $this->prepararImagem($request, $dados);

        // Executa a atualização no banco de dados com os dados tratados
        if ($produto->update($dados)) {
            return redirect()->route('produtos.index')
                ->with('mensagem', "O produto '{$produto->nome}' foi alterado com sucesso!");
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
        $produto = Produto::findOrFail($id); // Encontra o produto antes de apagar
        $nomeProduto = $produto->nome; // Guarda o nome para exibir na mensagem de sucesso após a exclusão

        try {
            if (!$produto->delete()) { // Executa a exclusão do registo
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

    private function validar(Request $request)
    {
        return $request->validate([
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
            'estoque' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
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
        ], [
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

            'estoque.required' => 'Informe a quantidade em estoque.',
            'estoque.integer' => 'O estoque deve ser um número inteiro.',
            'estoque.min' => 'O estoque não pode ser negativo.',
            'estoque.max' => 'A quantidade em estoque ultrapassa o limite permitido.',

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
    }

    private function prepararImagem(Request $request, array $dados)
    {
        unset($dados['imagem_arquivo'], $dados['imagem_url']);

        // Verifica se o utilizador enviou um ficheiro de imagem do dispositivo (computador/telemóvel)
        if ($request->hasFile('imagem_arquivo')) {
            $imagem = $request->file('imagem_arquivo');
            $nomeImagem = $imagem->hashName(); // Gera um nome para evitar conflitos entre os ficheiros

            File::ensureDirectoryExists(public_path('img/produtos'));

            $imagem->move(public_path('img/produtos'), $nomeImagem); // Move o ficheiro para a pasta public/img/produtos
            $dados['imagem'] = 'img/produtos/' . $nomeImagem; // Grava o caminho relativo que será salvo no banco
        }
        // Se o utilizador preferiu colar um link direto da internet
        elseif ($request->filled('imagem_url')) {
            $dados['imagem'] = $request->input('imagem_url');
        }

        return $dados;
    }
}

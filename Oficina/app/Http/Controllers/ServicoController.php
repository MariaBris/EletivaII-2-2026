<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicoController extends Controller
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
            return redirect()->route('servicos.index')
                ->withErrors($validacao)
                ->withInput($request->except('page'));
        }

        $dados = $validacao->validated();
        $busca = trim($dados['busca'] ?? '');

        $consulta = Servico::query();

        if ($busca !== '') {
            $consulta->where(function ($consultaBusca) use ($busca) {
                $consultaBusca->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('descricao', 'like', '%' . $busca . '%');
            });
        }

        $servicos = $consulta->orderBy('nome')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('servicos.index', compact('servicos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('servicos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Servico::create($this->validar($request));

        return redirect()->route('servicos.index')
            ->with('mensagem', 'Serviço cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Servico $servico)
    {
        return view('servicos.show', compact('servico'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Servico $servico)
    {
        return view('servicos.edit', compact('servico'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Servico $servico)
    {
        $servico->update($this->validar($request));

        return redirect()->route('servicos.index')
            ->with('mensagem', 'Serviço atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servico $servico)
    {
        try {
            $servico->delete();
        } catch (QueryException $erro) {
            if (($erro->errorInfo[1] ?? null) == 1451) {
                return redirect()->route('servicos.index')
                    ->with('erro', 'Este serviço possui registros vinculados e não pode ser excluído.');
            }

            throw $erro;
        }

        return redirect()->route('servicos.index')
            ->with('mensagem', 'Serviço excluído com sucesso!');
    }

    private function validar(Request $request)
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'preco' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:99999999.99',
            ],
        ], [
            'nome.required' => 'Informe o nome do serviço.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome deve ter até 255 caracteres.',
            'descricao.string' => 'A descrição deve ser um texto.',
            'descricao.max' => 'A descrição deve ter até 5.000 caracteres.',
            'preco.required' => 'Informe o valor da mão de obra.',
            'preco.numeric' => 'Informe um preço válido.',
            'preco.decimal' => 'O preço deve ter no máximo duas casas decimais.',
            'preco.min' => 'O preço não pode ser negativo.',
            'preco.max' => 'O preço ultrapassa o limite permitido.',
        ]);
    }
}

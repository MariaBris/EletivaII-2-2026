<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Veiculos;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class VeiculosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->autorizarAcesso($request);

        $veiculos = Veiculos::with('cliente')
            ->orderBy('placa')
            ->get();

        return view('veiculos.index', compact('veiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);

        $clientes = Cliente::orderBy('nome')->get();

        return view('veiculos.create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validar($request);
        $dados['user_id'] = null;

        Veiculos::create($dados);

        return redirect()->route('veiculos.index')
            ->with('mensagem', 'Veículo cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $veiculo = Veiculos::with('cliente')->findOrFail($id);

        return view('veiculos.show', compact('veiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $veiculo = Veiculos::findOrFail($id);
        $clientes = Cliente::orderBy('nome')->get();

        return view('veiculos.edit', compact('veiculo', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $veiculo = Veiculos::findOrFail($id);
        $dados = $this->validar($request, $veiculo->id);
        $dados['user_id'] = null;

        DB::transaction(function () use ($id, $dados) {
            $veiculo = Veiculos::lockForUpdate()->findOrFail($id);

            $veiculo->update($dados);
        });

        return redirect()->route('veiculos.index')
            ->with('mensagem', 'Veículo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $veiculo = Veiculos::findOrFail($id);

        if ($this->temHistorico($id)) {
            return redirect()->route('veiculos.index')
                ->with('erro', 'Este veículo possui histórico e não pode ser excluído.');
        }

        try {
            $veiculo->delete();
        } catch (QueryException $erro) {
            if (($erro->errorInfo[1] ?? null) == 1451) {
                return redirect()->route('veiculos.index')
                    ->with('erro', 'Este veículo possui registros vinculados e não pode ser excluído.');
            }

            throw $erro;
        }

        return redirect()->route('veiculos.index')
            ->with('mensagem', 'Veículo excluído com sucesso!');
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
    }

    private function validar(Request $request, $id = null)
    {
        if (is_string($request->input('placa'))) {
            $request->merge([
                'placa' => strtoupper(
                    str_replace(['-', ' '], '', trim($request->placa))
                ),
            ]);
        }

        $regraPlaca = Rule::unique('veiculos', 'placa');

        if ($id !== null) {
            $regraPlaca->ignore($id);
        }

        $dados = $request->validate([
            'placa' => ['required', 'string', 'max:10', $regraPlaca],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'ano' => ['nullable', 'integer', 'between:1886,2100'],
            'cliente_id' => [
                'nullable',
                'integer',
                Rule::exists('clientes', 'id'),
            ],
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'max' => 'O campo :attribute deve ter até :max caracteres.',
            'placa.unique' => 'Esta placa já está cadastrada.',
            'ano.integer' => 'O ano deve ser um número inteiro.',
            'ano.between' => 'Informe um ano entre 1886 e 2100.',
            'cliente_id.integer' => 'Selecione um cliente válido.',
            'cliente_id.exists' => 'Selecione um cliente cadastrado.',
        ]);

        $dados['cliente_id'] = $dados['cliente_id'] ?? null;

        return $dados;
    }

    private function temHistorico($id)
    {
        foreach (['pedidos', 'ordens_servico'] as $tabela) {
            if (
                Schema::hasTable($tabela)
                && DB::table($tabela)->where('veiculo_id', $id)->exists()
            ) {
                return true;
            }
        }

        return false;
    }
}

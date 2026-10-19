<?php

namespace App\Http\Controllers;

use App\Models\Veiculos;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VeiculosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $consulta = Veiculos::with('usuario');

        if ($request->user()->role !== 'ADM') {
            $consulta->where('user_id', $request->user()->id);
        }

        $veiculos = $consulta->get();

        return view('veiculos.index', compact('veiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->user()->role === 'ADM') {
            $usuarios = User::where('role', 'CLI')
                ->orderBy('name')
                ->get();
        } else {
            $usuarios = User::where('id', $request->user()->id)->get();
        }

        return view('veiculos.create', compact('usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'ADM') {
            $request->merge([
                'user_id' => $request->user()->id,
            ]);
        }

        Veiculos::create($this->validar($request));

        return redirect()->route('veiculos.index')
            ->with('mensagem', 'Veículo cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $consulta = Veiculos::with('usuario');

        if ($request->user()->role !== 'ADM') {
            $consulta->where('user_id', $request->user()->id);
        }

        $veiculo = $consulta->findOrFail($id);

        return view('veiculos.show', compact('veiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $veiculo = Veiculos::findOrFail($id);
        $usuarios = User::where('role', 'CLI')->orderBy('name')->get();

        return view('veiculos.edit', compact('veiculo', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $veiculo = Veiculos::findOrFail($id);
        $dados = $this->validar($request, $veiculo->id);

        DB::transaction(function () use ($id, $dados) {
            $veiculo = Veiculos::lockForUpdate()->findOrFail($id);

            if (
                $veiculo->user_id != $dados['user_id']
                && $this->temHistorico($id)
            ) {
                throw ValidationException::withMessages([
                    'user_id' => 'Este veículo possui histórico e não pode mudar de proprietário.',
                ]);
            }

            $veiculo->update($dados);
        });

        return redirect()->route('veiculos.index')
            ->with('mensagem', 'Veículo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
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

        return $request->validate([
            'placa' => ['required', 'string', 'max:10', $regraPlaca],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'ano' => ['nullable', 'integer', 'between:1886,2100'],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'CLI'),
            ],
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'max' => 'O campo :attribute deve ter até :max caracteres.',
            'placa.unique' => 'Esta placa já está cadastrada.',
            'ano.integer' => 'O ano deve ser um número inteiro.',
            'ano.between' => 'Informe um ano entre 1886 e 2100.',
            'user_id.integer' => 'Selecione um cliente válido.',
            'user_id.exists' => 'Selecione um cliente cadastrado com perfil CLI.',
        ]);
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

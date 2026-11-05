<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Veiculos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AgendamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->autorizarAcesso($request);

        $validacao = Validator::make($request->query(), [
            'data' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'string', 'in:AGENDADO,ATENDIDO,CANCELADO'],
            'busca' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ], [
            'data.date_format' => 'Informe uma data válida.',
            'status.in' => 'Selecione uma situação válida.',
            'busca.max' => 'A pesquisa deve ter até 255 caracteres.',
            'page.integer' => 'Informe uma página válida.',
            'page.min' => 'Informe uma página maior que zero.',
        ]);

        if ($validacao->fails()) {
            return redirect()->route('agendamentos.index')->withErrors($validacao);
        }

        $filtros = $validacao->validated();
        $data = $request->has('data') ? ($filtros['data'] ?? '') : now('America/Sao_Paulo')->toDateString();
        $consulta = Agendamento::with(['veiculo', 'cliente', 'pedido']);

        if ($data !== '') {
            $consulta->whereDate('data_hora', $data);
        }

        if (!empty($filtros['status'])) {
            $consulta->where('status', $filtros['status']);
        }

        $busca = trim($filtros['busca'] ?? '');

        if ($busca !== '') {
            $consulta->where(function ($q) use ($busca) {
                $q->whereHas('veiculo', function ($veiculos) use ($busca) {
                    $veiculos->where(function ($q) use ($busca) {
                        $q->where('placa', 'like', '%' . $busca . '%')
                            ->orWhere('modelo', 'like', '%' . $busca . '%');
                    });
                })->orWhereHas('cliente', function ($clientes) use ($busca) {
                    $clientes->where('nome', 'like', '%' . $busca . '%');
                });
            });
        }

        $agendamentos = $consulta->orderBy('data_hora')->orderBy('id')
            ->paginate(15)->withQueryString();

        return view('agendamentos.index', compact('agendamentos', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);
        $agendamento = null;
        $veiculos = Veiculos::with('cliente')->orderBy('placa')->get();

        return view('agendamentos.form', compact('agendamento', 'veiculos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);
        $dados = $this->validar($request);

        $agendamento = DB::transaction(function () use ($request, $dados) {
            $veiculo = Veiculos::lockForUpdate()->findOrFail($dados['veiculo_id']);
            $this->conferirHorario($veiculo->id, $dados['data_hora']);

            return Agendamento::create([
                ...$dados,
                'cliente_id' => $veiculo->cliente_id,
                'usuario_id' => $request->user()->id,
                'status' => 'AGENDADO',
            ]);
        }, 3);

        return redirect()->route('agendamentos.show', $agendamento->id)
            ->with('mensagem', 'Agendamento cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $agendamento)
    {
        $this->autorizarAcesso($request);
        $agendamento = Agendamento::with(['veiculo', 'cliente', 'operador', 'pedido'])
            ->findOrFail($agendamento);

        return view('agendamentos.show', compact('agendamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $agendamento)
    {
        $this->autorizarAcesso($request);
        $agendamento = Agendamento::findOrFail($agendamento);

        if ($agendamento->status !== 'AGENDADO') {
            return redirect()->route('agendamentos.show', $agendamento->id)
                ->with('erro', 'Somente agendamentos ainda agendados podem ser alterados.');
        }

        $veiculos = Veiculos::with('cliente')->orderBy('placa')->get();

        return view('agendamentos.form', compact('agendamento', 'veiculos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $agendamento)
    {
        $this->autorizarAcesso($request);
        $dados = $this->validar($request);

        DB::transaction(function () use ($agendamento, $dados) {
            // Mesma ordem de bloqueio usada na abertura da OS: veículo, depois agendamento.
            $veiculo = Veiculos::lockForUpdate()->findOrFail($dados['veiculo_id']);
            $registro = Agendamento::lockForUpdate()->findOrFail($agendamento);
            $this->exigirAgendado($registro);
            $this->conferirHorario($veiculo->id, $dados['data_hora'], $registro->id);

            $registro->update([
                ...$dados,
                'cliente_id' => $veiculo->cliente_id,
            ]);
        }, 3);

        return redirect()->route('agendamentos.show', $agendamento)
            ->with('mensagem', 'Agendamento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $agendamento)
    {
        $this->autorizarAcesso($request);

        DB::transaction(function () use ($agendamento) {
            $registro = Agendamento::lockForUpdate()->findOrFail($agendamento);

            if ($registro->status === 'CANCELADO') {
                return;
            }

            $this->exigirAgendado($registro);
            $registro->status = 'CANCELADO';
            $registro->save();
        }, 3);

        return redirect()->route('agendamentos.show', $agendamento)
            ->with('mensagem', 'Agendamento cancelado. O histórico foi preservado.');
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless($request->user() && in_array($request->user()->role, ['ADM', 'FUNC'], true), 403);
    }

    private function exigirAgendado(Agendamento $agendamento)
    {
        if ($agendamento->status !== 'AGENDADO' || $agendamento->pedido_id !== null) {
            throw ValidationException::withMessages([
                'agendamento' => 'Este agendamento já foi atendido ou cancelado e não pode ser alterado.',
            ]);
        }
    }

    private function conferirHorario($veiculoId, string $dataHora, $ignorar = null)
    {
        $consulta = Agendamento::where('veiculo_id', $veiculoId)
            ->where('data_hora', $dataHora)->where('status', 'AGENDADO');

        if ($ignorar !== null) {
            $consulta->where('id', '<>', $ignorar);
        }

        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'hora' => 'Este veículo já possui um agendamento ativo para essa data e horário.',
            ]);
        }
    }

    private function validar(Request $request): array
    {
        $dados = $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'data' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo' => ['required', 'string', 'max:5000'],
        ], [
            'veiculo_id.required' => 'Selecione o veículo.',
            'veiculo_id.exists' => 'Selecione um veículo cadastrado.',
            'data.required' => 'Informe a data do agendamento.',
            'data.date_format' => 'Informe uma data válida.',
            'hora.required' => 'Informe o horário.',
            'hora.date_format' => 'Informe um horário válido.',
            'motivo.required' => 'Descreva o motivo do atendimento.',
            'motivo.string' => 'O motivo deve ser um texto.',
            'motivo.max' => 'O motivo deve ter até 5.000 caracteres.',
        ]);

        return [
            'veiculo_id' => $dados['veiculo_id'],
            'data_hora' => $dados['data'] . ' ' . $dados['hora'] . ':00',
            'motivo' => $dados['motivo'],
        ];
    }
}

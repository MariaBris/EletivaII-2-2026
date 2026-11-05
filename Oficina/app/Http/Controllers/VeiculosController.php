<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Veiculos;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VeiculosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->autorizarAcesso($request);

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
            return redirect()->route('veiculos.index')
                ->withErrors($validacao)
                ->withInput($request->except('page'));
        }

        $dados = $validacao->validated();
        $busca = trim($dados['busca'] ?? '');

        $consulta = Veiculos::with('cliente');

        if ($busca !== '') {
            $placaBusca = strtoupper(
                str_replace(['-', ' '], '', $busca)
            );

            $consulta->where(function ($consultaBusca) use ($busca, $placaBusca) {
                $consultaBusca->where('marca', 'like', '%' . $busca . '%')
                    ->orWhere('modelo', 'like', '%' . $busca . '%')
                    ->orWhereHas('cliente', function ($clientes) use ($busca) {
                        $clientes->where('nome', 'like', '%' . $busca . '%');
                    });

                if ($placaBusca !== '') {
                    $consultaBusca->orWhere('placa', 'like', '%' . $placaBusca . '%');
                }
            });
        }

        $veiculos = $consulta->orderBy('placa')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('veiculos.index', compact('veiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);

        $validacao = Validator::make(
            $request->query(),
            $this->regrasRetorno()
        );

        if ($validacao->fails()) {
            return redirect()->route('veiculos.index')
                ->withErrors($validacao);
        }

        $clientes = Cliente::orderBy('nome')->get();

        return view('veiculos.create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);

        $retorno = $request->validate($this->regrasRetorno());
        $dados = $this->validar($request);
        $dados['user_id'] = null;

        $veiculo = Veiculos::create($dados);

        if (($retorno['origem'] ?? null) === 'os') {
            return redirect()->route('pedidos.create', [
                'veiculo_id' => $veiculo->id,
                'retomar' => 1,
            ])->with('mensagem', 'Veículo cadastrado! Continue preenchendo a ordem de serviço.');
        }

        if (($retorno['origem'] ?? null) === 'agendamento') {
            $parametros = [
                'veiculo_id' => $veiculo->id,
                'retomar' => 1,
            ];

            if (!empty($retorno['agendamento_id'])) {
                $parametros['agendamento'] = $retorno['agendamento_id'];
                $rota = 'agendamentos.edit';
            } else {
                $rota = 'agendamentos.create';
            }

            return redirect()->route($rota, $parametros)
                ->with('mensagem', 'Veículo cadastrado! Continue preenchendo o agendamento.');
        }

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

        $historicoServicos = $veiculo->servicosRealizados()
            ->with(['servico', 'pedido'])
            ->orderByDesc('pedidos.concluido_em')
            ->orderByDesc('pedidos.id')
            ->orderByDesc('itens_servico.id')
            ->paginate(10, ['itens_servico.*'], 'historico_page')
            ->withQueryString();

        return view('veiculos.show', compact('veiculo', 'historicoServicos'));
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

    private function regrasRetorno(): array
    {
        return [
            'origem' => [
                'nullable',
                'string',
                Rule::in(['os', 'agendamento']),
            ],
            'agendamento_id' => [
                'nullable',
                'integer',
                'exists:agendamentos,id',
            ],
        ];
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
            'quilometragem_informada' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'oleo_data_informada' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . now('America/Sao_Paulo')->toDateString()],
            'oleo_km_informado' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'revisao_data_informada' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . now('America/Sao_Paulo')->toDateString()],
            'revisao_km_informado' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
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
            'quilometragem_informada.integer' => 'A quilometragem deve ser um número inteiro.',
            'quilometragem_informada.min' => 'A quilometragem não pode ser negativa.',
            'quilometragem_informada.max' => 'A quilometragem ultrapassa o limite permitido.',
            'oleo_data_informada.date_format' => 'Informe uma data válida no formato dia/mês/ano.',
            'oleo_data_informada.before_or_equal' => 'A data da manutenção não pode estar no futuro.',
            'oleo_km_informado.integer' => 'A quilometragem deve ser um número inteiro.',
            'oleo_km_informado.min' => 'A quilometragem não pode ser negativa.',
            'oleo_km_informado.max' => 'A quilometragem ultrapassa o limite permitido.',
            'revisao_data_informada.date_format' => 'Informe uma data válida no formato dia/mês/ano.',
            'revisao_data_informada.before_or_equal' => 'A data da manutenção não pode estar no futuro.',
            'revisao_km_informado.integer' => 'A quilometragem deve ser um número inteiro.',
            'revisao_km_informado.min' => 'A quilometragem não pode ser negativa.',
            'revisao_km_informado.max' => 'A quilometragem ultrapassa o limite permitido.',
        ]);

        $dados['cliente_id'] = $dados['cliente_id'] ?? null;

        foreach (['quilometragem_informada', 'oleo_data_informada', 'oleo_km_informado', 'revisao_data_informada', 'revisao_km_informado'] as $campo) {
            $dados[$campo] = $dados[$campo] ?? null;
        }

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

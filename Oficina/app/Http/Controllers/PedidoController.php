<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Agendamento;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Veiculos;
use App\Models\FotoEntrada;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use App\Services\ReservaEstoque;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->autorizarAcesso($request);

        $regras = [
            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],
            'busca' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:ABERTO,EM_EXECUCAO,CONCLUIDO,CANCELADO'],
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];

        if ($request->filled('data_inicio')) {
            $regras['data_fim'][] = 'after_or_equal:data_inicio';
        }

        $validacao = Validator::make($request->query(), $regras, [
            'veiculo_id.integer' => 'Selecione um veículo válido.',
            'veiculo_id.exists' => 'O veículo informado não foi encontrado.',
            'busca.string' => 'Informe um texto para pesquisar.',
            'busca.max' => 'A pesquisa deve ter até 255 caracteres.',
            'status.string' => 'Selecione um andamento válido.',
            'status.in' => 'Selecione um andamento válido.',
            'data_inicio.date_format' => 'Informe uma data inicial válida.',
            'data_fim.date_format' => 'Informe uma data final válida.',
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à inicial.',
            'page.integer' => 'A página deve ser um número inteiro.',
            'page.min' => 'A página deve ser maior que zero.',
        ]);

        if ($validacao->fails()) {
            $parametros = [];

            if (
                !$validacao->errors()->has('veiculo_id')
                && $request->filled('veiculo_id')
            ) {
                $parametros['veiculo_id'] = $request->query('veiculo_id');
            }

            return redirect()->route('pedidos.index', $parametros)
                ->withErrors($validacao)
                ->withInput($request->except('page'));
        }

        $dados = $validacao->validated();
        $consulta = Pedido::with(['cliente', 'veiculo', 'operador']);
        $veiculoFiltrado = null;

        if (!empty($dados['veiculo_id'])) {
            $veiculoFiltrado = Veiculos::findOrFail($dados['veiculo_id']);
            $consulta->where('veiculo_id', $veiculoFiltrado->id);
        }

        $busca = trim($dados['busca'] ?? '');

        if ($busca !== '') {
            $numero = trim(ltrim($busca, '#'));

            $numeroOs = filter_var($numero, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            $consulta->where(function ($consultaBusca) use ($busca, $numeroOs) {
                $consultaBusca->whereHas('cliente', function ($clientes) use ($busca) {
                    $clientes->where('nome', 'like', '%' . $busca . '%');
                });

                $consultaBusca->orWhereHas('veiculo', function ($veiculos) use ($busca) {
                    $veiculos->where('placa', 'like', '%' . $busca . '%');
                });

                if ($numeroOs !== false) {
                    $consultaBusca->orWhere('pedidos.id', $numeroOs);
                }
            });
        }

        if (!empty($dados['status'])) {
            $consulta->where('status', $dados['status']);
        }

        if (!empty($dados['data_inicio'])) {
            $consulta->whereDate('created_at', '>=', $dados['data_inicio']);
        }

        if (!empty($dados['data_fim'])) {
            $consulta->whereDate('created_at', '<=', $dados['data_fim']);
        }

        $pedidos = $consulta->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('pedidos.index', compact('pedidos', 'veiculoFiltrado'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);

        $agendamento = null;

        if ($request->filled('agendamento_id')) {
            $validacao = Validator::make($request->query(), [
                'agendamento_id' => ['required', 'integer', 'exists:agendamentos,id'],
            ]);

            if ($validacao->fails()) {
                return redirect()->route('agendamentos.index')->withErrors($validacao);
            }

            $agendamento = Agendamento::with(['veiculo', 'cliente'])
                ->findOrFail($validacao->validated()['agendamento_id']);

            if ($agendamento->status === 'ATENDIDO' && $agendamento->pedido_id !== null) {
                return redirect()->route('pedidos.show', $agendamento->pedido_id);
            }

            if ($agendamento->status !== 'AGENDADO') {
                return redirect()->route('agendamentos.show', $agendamento->id)
                    ->with('erro', 'Este agendamento não está disponível para abrir uma OS.');
            }
        }

        $veiculos = Veiculos::with('cliente')->orderBy('placa')->get();
        $produtos = Produto::comReservas()->orderBy('nome')->get();
        $servicos = Servico::orderBy('nome')->get();

        return view('pedidos.create', compact('veiculos', 'produtos', 'servicos', 'agendamento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarFormulario($request, true);
        $fotos = $this->guardarFotos($request);

        try {
            $pedido = ReservaEstoque::transacao(function () use ($dados, $request, $fotos) {
                $veiculo = Veiculos::lockForUpdate()
                    ->findOrFail($dados['veiculo_id']);

                $agendamento = null;

                if (!empty($dados['agendamento_id'])) {
                    $agendamento = Agendamento::lockForUpdate()->findOrFail($dados['agendamento_id']);

                    if ($agendamento->status !== 'AGENDADO' || $agendamento->pedido_id !== null) {
                        $this->erro('agendamento', 'Este agendamento já foi atendido ou cancelado. Nenhuma nova OS foi criada.');
                    }

                    if ((string) $agendamento->veiculo_id !== (string) $veiculo->id) {
                        $this->erro('veiculo_id', 'O veículo precisa corresponder ao agendamento selecionado.');
                    }
                }

                $pedido = Pedido::create([
                    'status' => 'ABERTO',
                    'total' => '0.00',
                    'total_orcamento' => '0.00',
                    'user_id' => null,
                    'cliente_id' => $veiculo->cliente_id,
                    'usuario_id' => $request->user()->id,
                    'veiculo_id' => $veiculo->id,
                    'quilometragem_entrada' => $dados['quilometragem_entrada'] ?? null,
                    'combustivel_entrada' => $dados['combustivel_entrada'] ?? null,
                    'avarias_entrada' => $dados['avarias_entrada'] ?? null,
                    'responsavel' => $dados['responsavel'] ?? null,
                    'observacoes' => $dados['observacoes'] ?? null,
                    'estoque_baixado' => false,
                ]);

                $this->incluirItens(
                    $pedido,
                    $dados['produtos'],
                    $dados['servicos']
                );

                $this->registrarFotos(
                    $pedido,
                    $fotos,
                    $request->user()->id
                );

                $this->salvarChecklist($pedido, $dados['checklist'] ?? [], $request->user()->id);

                if ($agendamento) {
                    $agendamento->pedido_id = $pedido->id;
                    $agendamento->status = 'ATENDIDO';
                    $agendamento->save();
                }

                return $pedido;
            }, 3);
        } catch (\Throwable $erro) {
            Storage::disk('local')->delete(array_values($fotos));

            throw $erro;
        }

        return redirect()->route('pedidos.show', $pedido->id)
            ->with('mensagem', 'OS aberta! Os itens do orçamento aguardam autorização do cliente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $pedido = Pedido::with([
            'cliente',
            'veiculo',
            'operador',
            'itens.produto',
            'itens.autorizacaoPor',
            'itens.canceladoPor',
            'itens.interrompidoPor',
            'servicos.servico',
            'servicos.autorizacaoPor',
            'servicos.canceladoPor',
            'servicos.interrompidoPor',
            'fotosEntrada.operador',
            'checklistItens.conferente',
        ])->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $pedido = Pedido::with([
            'cliente',
            'veiculo',
            'itens.produto',
            'itens.autorizacaoPor',
            'itens.canceladoPor',
            'itens.interrompidoPor',
            'servicos.servico',
            'servicos.autorizacaoPor',
            'servicos.canceladoPor',
            'servicos.interrompidoPor',
            'fotosEntrada',
            'checklistItens.conferente',
        ])->findOrFail($id);

        $produtos = Produto::comReservas()->orderBy('nome')->get();
        $servicos = Servico::orderBy('nome')->get();

        return view('pedidos.edit', compact('pedido', 'produtos', 'servicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarFormulario($request, false);
        $fotos = $this->guardarFotos($request);

        try {
            $concluidaAutomaticamente = ReservaEstoque::transacao(function () use ($request, $id, $dados, $fotos) {
                $pedido = Pedido::lockForUpdate()->findOrFail($id);
                $dados['status'] = $pedido->status;

                $encerrada = in_array(
                    $pedido->status,
                    ['CONCLUIDO', 'CANCELADO'],
                    true
                );

                $temItens = count($dados['novos_produtos']) > 0
                    || count($dados['novos_servicos']) > 0;

                if ($encerrada) {
                    if ($temItens || count($fotos) > 0 || !empty($dados['checklist'])) {
                        $this->erro(
                            'pedido',
                            'Uma OS encerrada não pode receber itens ou fotos.'
                        );
                    }

                    foreach (['quilometragem_entrada', 'combustivel_entrada', 'avarias_entrada'] as $campo) {
                        if (
                            array_key_exists($campo, $dados)
                            && (string) ($dados[$campo] ?? '') !== (string) ($pedido->{$campo} ?? '')
                        ) {
                            $this->erro(
                                $campo,
                                'Os dados de entrada de uma OS encerrada não podem ser alterados.'
                            );
                        }
                    }
                }

                if (
                    $dados['status'] === 'CANCELADO'
                    && ($temItens || count($fotos) > 0)
                ) {
                    $this->erro(
                        'status',
                        'Salve as novas inclusões antes de cancelar, ou remova-as do formulário.'
                    );
                }

                $this->conferirAndamento($pedido, $dados['status']);

                if (!$encerrada && $dados['status'] !== 'CANCELADO') {
                    $this->incluirItens(
                        $pedido,
                        $dados['novos_produtos'],
                        $dados['novos_servicos']
                    );

                    $this->registrarFotos(
                        $pedido,
                        $fotos,
                        $request->user()->id
                    );

                    foreach (['quilometragem_entrada', 'combustivel_entrada', 'avarias_entrada'] as $campo) {
                        if (array_key_exists($campo, $dados)) {
                            $pedido->{$campo} = $dados[$campo];
                        }
                    }
                }

                if (!$encerrada) {
                    $this->salvarChecklist($pedido, $dados['checklist'] ?? [], $request->user()->id);
                }

                $pedido->responsavel = $dados['responsavel'] ?? null;
                $pedido->observacoes = $dados['observacoes'] ?? null;

                // Todos os dados, fotos e checklist já foram gravados nesta transação.
                $this->sincronizarAndamento($pedido, $request->user()->id);

                return !$encerrada && $pedido->status === 'CONCLUIDO';
            }, 3);
        } catch (\Throwable $erro) {
            Storage::disk('local')->delete(array_values($fotos));

            throw $erro;
        }

        return redirect()->route('pedidos.show', $id)
            ->with('mensagem', $concluidaAutomaticamente
                ? 'OS atualizada e concluída automaticamente. Todos os trabalhos foram resolvidos!'
                : 'Ordem de serviço atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        ReservaEstoque::transacao(function () use ($request, $id) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            $this->aplicarAndamento(
                $pedido,
                'CANCELADO',
                $request->user()->id
            );
        }, 3);

        return redirect()->route('pedidos.show', $id)
            ->with('mensagem', 'OS cancelada. O histórico foi preservado.');
    }

    public function autorizarEIniciarSelecionados(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarSelecao($request);

        $produtosBaixados = ReservaEstoque::transacao(function () use ($request, $id, $dados) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            $this->exigirOsAberta($pedido);

            $registros = $this->carregarSelecionados($pedido, $dados['selecionados']);

            $produtosBaixados = $this->autorizarEIniciarRegistros(
                $pedido,
                $registros,
                $request->user()->id
            );

            $this->recalcularTotais($pedido);
            $this->atualizarIndicadorEstoque($pedido);

            $pedido->save();

            return $produtosBaixados;
        }, 3);

        return redirect()->route('pedidos.edit', $id)
            ->with('mensagem', 'Itens autorizados e iniciados! O estoque das peças foi atualizado.')
            ->with('alertas_estoque', $this->alertasEstoque($produtosBaixados));
    }

    public function finalizarSelecionados(Request $request, string $id)
    {
        $this->autorizarAcesso($request);
        $dados = $this->validarSelecao($request);

        $concluida = ReservaEstoque::transacao(function () use ($request, $id, $dados) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);
            $this->exigirOsAberta($pedido);
            $registros = $this->carregarSelecionados($pedido, $dados['selecionados']);

            foreach ($registros as $registro) {
                if (!in_array($registro->situacao, ['EM_EXECUCAO', 'EXECUTADO'], true)) {
                    $this->erro('selecionados', 'Somente trabalhos em execução podem ser marcados como executados. Confira os itens selecionados.');
                }
            }

            foreach ($registros as $registro) {
                if ($registro->situacao === 'EM_EXECUCAO') {
                    $registro->situacao = 'EXECUTADO';
                    $registro->save();
                }
            }

            $this->recalcularTotais($pedido);
            $this->atualizarIndicadorEstoque($pedido);
            $this->sincronizarAndamento($pedido, $request->user()->id);

            return $pedido->status === 'CONCLUIDO';
        }, 3);

        return redirect()->route($concluida ? 'pedidos.show' : 'pedidos.edit', $id)
            ->with('mensagem', $concluida
                ? 'Trabalhos executados e OS concluída automaticamente!'
                : 'Trabalhos selecionados marcados como executados. A OS ainda possui itens a resolver.');
    }

    public function atualizarItem(Request $request, string $id, string $tipo, string $item)
    {
        $this->autorizarAcesso($request);

        abort_unless(in_array($tipo, ['peca', 'servico'], true), 404);

        $regras = [
            'acao' => [
                'required',
                'string',
                'in:autorizar,recusar,aguardar,alterar_quantidade,autorizar_iniciar,finalizar,cancelar,interromper',
            ],
            'quantidade' => [
                'nullable',
                'required_if:acao,alterar_quantidade',
                'integer',
                'min:1',
                'max:1000000',
            ],
            'motivo_cancelamento' => [
                'nullable',
                'required_if:acao,cancelar',
                'string',
                'max:1000',
            ],
            'motivo_interrupcao' => [
                'nullable',
                'required_if:acao,interromper',
                'string',
                'max:1000',
            ],
            'valor_cobrado' => [
                'nullable',
                'required_if:acao,interromper',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:99999999.99',
            ],
            'quantidade_devolvida' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],
        ];

        if (
            $request->input('acao') === 'interromper'
            && $tipo === 'peca'
            && is_numeric($request->input('quantidade_devolvida'))
            && (float) $request->input('quantidade_devolvida') > 0
        ) {
            $regras['confirmar_devolucao'] = ['required', 'accepted'];
        }

        $dados = $request->validate($regras, [
            'required' => 'O campo :attribute é obrigatório.',
            'required_if' => 'O campo :attribute é obrigatório para esta ação.',
            'string' => 'O campo :attribute deve ser um texto.',
            'in' => 'Selecione uma ação válida.',
            'numeric' => 'Informe um valor numérico.',
            'decimal' => 'Informe no máximo duas casas decimais.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute ultrapassa o limite permitido.',
            'confirmar_devolucao.required' => 'Confirme que as peças devolvidas podem retornar ao estoque.',
            'confirmar_devolucao.accepted' => 'Confirme que as peças devolvidas podem retornar ao estoque.',
        ], [
            'motivo_cancelamento' => 'motivo do cancelamento',
            'motivo_interrupcao' => 'motivo da interrupção',
            'valor_cobrado' => 'valor total combinado para o item',
            'quantidade_devolvida' => 'quantidade devolvida',
            'quantidade' => 'quantidade',
        ]);

        $resultado = ReservaEstoque::transacao(function () use ($request, $id, $tipo, $item, $dados) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            $this->exigirOsAberta($pedido);

            $relacao = $tipo === 'peca'
                ? $pedido->itens()
                : $pedido->servicos();

            $registro = $relacao->lockForUpdate()->findOrFail($item);
            $produtosBaixados = [];

            if (in_array($dados['acao'], ['autorizar', 'recusar', 'aguardar'], true)) {
                $this->registrarAutorizacao(
                    $registro,
                    $dados['acao'],
                    $request->user()->id
                );
            } elseif ($dados['acao'] === 'alterar_quantidade') {
                $this->exigirItemPendente($registro);

                if ((int) $registro->quantidade !== (int) $dados['quantidade']) {
                    $registro->quantidade = $dados['quantidade'];
                    $registro->autorizacao = 'AGUARDANDO';
                    $registro->autorizacao_em = null;
                    $registro->autorizacao_por = null;
                    $registro->save();
                }
            } elseif ($dados['acao'] === 'autorizar_iniciar') {
                $produtosBaixados = $this->autorizarEIniciarRegistros(
                    $pedido,
                    collect([$registro]),
                    $request->user()->id
                );
            } elseif ($dados['acao'] === 'finalizar') {
                if ($registro->situacao !== 'EXECUTADO') {
                    if ($registro->situacao !== 'EM_EXECUCAO') {
                        $this->erro(
                            'item',
                            'Somente um trabalho em execução pode ser finalizado.'
                        );
                    }

                    $registro->situacao = 'EXECUTADO';
                    $registro->save();
                }
            } elseif ($dados['acao'] === 'cancelar') {
                $motivo = trim($dados['motivo_cancelamento'] ?? '');

                if ($motivo === '') {
                    $this->erro(
                        'motivo_cancelamento',
                        'Informe o motivo do cancelamento.'
                    );
                }

                $this->cancelarRegistro(
                    $registro,
                    $request->user()->id,
                    $motivo
                );
            } else {
                $this->interromperRegistro(
                    $registro,
                    $dados,
                    $request->user()->id
                );
            }

            $this->recalcularTotais($pedido);
            $this->atualizarIndicadorEstoque($pedido);

            $this->sincronizarAndamento($pedido, $request->user()->id);

            return [
                'produtos' => $produtosBaixados,
                'concluida' => $pedido->status === 'CONCLUIDO',
            ];
        }, 3);

        $mensagem = $dados['acao'] === 'alterar_quantidade'
            ? 'Quantidade atualizada. Se houve alteração, o item voltou a aguardar autorização.'
            : 'Item atualizado com sucesso!';

        if ($resultado['concluida']) {
            $mensagem .= ' OS concluída automaticamente!';
        }

        return redirect()->route($resultado['concluida'] ? 'pedidos.show' : 'pedidos.edit', $id)
            ->with('mensagem', $mensagem)
            ->with('alertas_estoque', $this->alertasEstoque($resultado['produtos']));
    }

    public function foto(Request $request, string $id, string $foto)
    {
        $this->autorizarAcesso($request);

        $registro = FotoEntrada::where('pedido_id', $id)
            ->findOrFail($foto);

        $disco = Storage::disk('local');

        abort_unless(
            Str::startsWith($registro->caminho, 'fotos-entrada/')
                && $disco->exists($registro->caminho),
            404
        );

        return response()->file($disco->path($registro->caminho), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function imprimir(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $dados = Validator::make($request->query(), [
            'modo' => ['nullable', 'string', 'in:completo,resumo'],
        ])->validate();

        $pedido = Pedido::with([
            'cliente',
            'veiculo',
            'operador',
            'itens.produto',
            'itens.autorizacaoPor',
            'itens.canceladoPor',
            'itens.interrompidoPor',
            'servicos.servico',
            'servicos.autorizacaoPor',
            'servicos.canceladoPor',
            'servicos.interrompidoPor',
            'checklistItens.conferente',
        ])->findOrFail($id);

        $modo = $dados['modo'] ?? 'completo';
        $modelo = false;
        $grupos = config('checklist_os');

        return view('pedidos.imprimir', compact('pedido', 'modo', 'modelo', 'grupos'));
    }

    public function modelo(Request $request)
    {
        $this->autorizarAcesso($request);

        $modelo = true;
        $modo = 'modelo';
        $grupos = config('checklist_os');

        return view('pedidos.imprimir', compact('modelo', 'modo', 'grupos'));
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
    }

    private function exigirOsAberta(Pedido $pedido)
    {
        if (!in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true)) {
            $this->erro(
                'pedido',
                'Os itens de uma OS encerrada não podem ser alterados.'
            );
        }
    }

    private function exigirItemPendente($registro)
    {
        if ($registro->situacao !== 'PENDENTE') {
            $this->erro(
                'item',
                'Esta alteração só é permitida antes do início do trabalho.'
            );
        }

        if (
            $registro instanceof ItemPedido
            && $registro->estoque_baixado
        ) {
            $this->erro(
                'estoque',
                'Este item já possui baixa de estoque e não pode ser alterado como orçamento.'
            );
        }
    }

    private function registrarAutorizacao($registro, string $acao, $usuarioId)
    {
        $this->exigirItemPendente($registro);

        $opcoes = [
            'autorizar' => 'AUTORIZADO',
            'recusar' => 'RECUSADO',
            'aguardar' => 'AGUARDANDO',
        ];

        $autorizacao = $opcoes[$acao];

        if ($registro->autorizacao === $autorizacao) {
            return;
        }

        $registro->autorizacao = $autorizacao;
        $registro->autorizacao_em = now();
        $registro->autorizacao_por = $usuarioId;

        $registro->save();
    }

    private function validarSelecao(Request $request): array
    {
        return $request->validate([
            'selecionados' => ['required', 'array', 'min:1', 'max:200'],
            'selecionados.*' => [
                'required',
                'string',
                'distinct',
                'regex:/^(peca|servico):[1-9][0-9]{0,17}$/',
            ],
        ], [
            'selecionados.required' => 'Selecione ao menos um item.',
            'selecionados.array' => 'Confira os itens selecionados.',
            'selecionados.min' => 'Selecione ao menos um item.',
            'selecionados.max' => 'Selecione até 200 itens por operação.',
            'selecionados.*.distinct' => 'Não repita os itens selecionados.',
            'selecionados.*.regex' => 'Um dos itens selecionados é inválido.',
        ]);
    }

    private function carregarSelecionados(Pedido $pedido, array $selecionados)
    {
        $ids = [
            'peca' => [],
            'servico' => [],
        ];

        foreach ($selecionados as $selecionado) {
            [$tipo, $numero] = explode(':', $selecionado);
            $ids[$tipo][] = $numero;
        }

        $pecas = $pedido->itens()
            ->whereIn('id', $ids['peca'])
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $servicos = $pedido->servicos()
            ->whereIn('id', $ids['servico'])
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if (
            $pecas->count() !== count($ids['peca'])
            || $servicos->count() !== count($ids['servico'])
        ) {
            $this->erro(
                'selecionados',
                'Um dos itens não pertence a esta OS ou não foi encontrado.'
            );
        }

        return $pecas->concat($servicos);
    }

    private function autorizarEIniciarRegistros(Pedido $pedido, $registros, $usuarioId): array
    {
        // Chamado dentro da transação: falha de estoque desfaz também as autorizações.
        foreach ($registros as $registro) {
            $this->exigirItemPendente($registro);

            if (!in_array($registro->autorizacao, ['AGUARDANDO', 'AUTORIZADO'], true)) {
                $this->erro('autorizacao', 'Um item está recusado. Confira a decisão do cliente antes de iniciar.');
            }
        }

        foreach ($registros as $registro) {
            $this->registrarAutorizacao($registro, 'autorizar', $usuarioId);
        }

        return $this->iniciarRegistros($pedido, $registros);
    }

    private function iniciarRegistros(Pedido $pedido, $registros): array
    {
        ReservaEstoque::conferirInicio($registros);

        $quantidades = [];

        foreach ($registros as $registro) {
            $this->exigirItemPendente($registro);

            if ($registro->autorizacao !== 'AUTORIZADO') {
                $this->erro(
                    'autorizacao',
                    'Somente itens autorizados pelo cliente podem ser iniciados.'
                );
            }

            if ($registro instanceof ItemPedido) {
                $produtoId = $registro->produto_id;

                $quantidades[$produtoId] = ($quantidades[$produtoId] ?? 0)
                    + $registro->quantidade;
            }
        }

        $produtos = Produto::whereIn('id', array_keys($quantidades))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($quantidades as $produtoId => $quantidade) {
            $produto = $produtos->get($produtoId);

            if (!$produto || $quantidade <= 0 || $produto->estoque < $quantidade) {
                $nome = $produto?->nome ?? 'peça não encontrada';

                $this->erro(
                    'estoque',
                    'Estoque insuficiente para iniciar os trabalhos: ' . $nome . '. Nenhum dos itens selecionados foi iniciado.'
                );
            }
        }

        foreach ($quantidades as $produtoId => $quantidade) {
            $produtos->get($produtoId)->decrement('estoque', $quantidade);
        }

        foreach ($registros as $registro) {
            $registro->situacao = 'EM_EXECUCAO';

            if ($registro instanceof ItemPedido) {
                $registro->estoque_baixado = true;
                $registro->quantidade_reservada = 0;
            }

            $registro->save();
        }

        $pedido->status = 'EM_EXECUCAO';

        return array_keys($quantidades);
    }

    private function validarFormulario(Request $request, bool $cadastro)
    {
        $listaProdutos = $cadastro ? 'produtos' : 'novos_produtos';
        $listaServicos = $cadastro ? 'servicos' : 'novos_servicos';

        foreach ([$listaProdutos, $listaServicos] as $lista) {
            if (!$request->has($lista)) {
                $request->merge([$lista => []]);
            }
        }

        $regras = [
            'quilometragem_entrada' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'combustivel_entrada' => ['nullable', 'string', 'in:VAZIO,UM_QUARTO,METADE,TRES_QUARTOS,CHEIO'],
            'avarias_entrada' => ['nullable', 'string', 'max:5000'],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['required', 'string', 'max:5000'],

            'fotos_entrada' => [
                'nullable',
                'array:FRENTE,TRASEIRA,LATERAL_ESQUERDA,LATERAL_DIREITA',
            ],

            $listaProdutos => ['present', 'array', 'max:100'],
            $listaProdutos . '.*' => ['array:produto_id,quantidade'],
            $listaProdutos . '.*.produto_id' => [
                'required',
                'integer',
                'distinct',
                'exists:produtos,id',
            ],
            $listaProdutos . '.*.quantidade' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],

            $listaServicos => ['present', 'array', 'max:100'],
            $listaServicos . '.*' => ['array:servico_id,quantidade'],
            $listaServicos . '.*.servico_id' => [
                'required',
                'integer',
                'distinct',
                'exists:servicos,id',
            ],
            $listaServicos . '.*.quantidade' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],
        ];

        if ($cadastro) {
            $regras['agendamento_id'] = ['nullable', 'integer', 'exists:agendamentos,id'];
            $regras['veiculo_id'] = [
                'required',
                'integer',
                'exists:veiculos,id',
            ];
        }

        foreach (FotoEntrada::POSICOES as $posicao => $titulo) {
            $regras['fotos_entrada.' . $posicao] = [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:15360',
            ];
        }

        $camposChecklist = $this->camposChecklist();
        $regras['checklist'] = ['nullable', 'array:' . implode(',', array_keys($camposChecklist))];

        foreach ($camposChecklist as $codigo => $campo) {
            $prefixo = 'checklist.' . $codigo;
            $opcoes = $campo['grupo'] === 'ACESSORIOS'
                ? 'NAO_VERIFICADO,PRESENTE,AUSENTE'
                : 'NAO_VERIFICADO,OK,ATENCAO';
            $regras[$prefixo] = ['sometimes', 'array:situacao,observacoes'];
            $regras[$prefixo . '.situacao'] = ['nullable', 'string', 'in:' . $opcoes];
            $regras[$prefixo . '.observacoes'] = ['nullable', 'string', 'max:1000'];

            if ($codigo === 'acessorios_outros') {
                $regras[$prefixo . '.observacoes'][] = 'required_if:' . $prefixo . '.situacao,PRESENTE';
            }
        }

        return $request->validate($regras, [
            'required' => 'O campo :attribute é obrigatório.',
            'observacoes.required' => 'Descreva o relato ou a solicitação do cliente.',
            'required_if' => 'Descreva quais outros acessórios estão presentes.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'string' => 'O campo :attribute deve ser um texto.',
            'array' => 'Confira os dados enviados em :attribute.',
            'distinct' => 'Não repita o mesmo item na lista de novas inclusões.',
            'exists' => 'Um dos registros selecionados não foi encontrado.',
            'in' => 'Selecione uma opção válida para :attribute.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute ultrapassa o limite permitido.',
            'fotos_entrada.*.uploaded' => 'Não foi possível enviar uma das fotos. Confira o tamanho.',
            'fotos_entrada.*.image' => 'Selecione uma imagem válida.',
            'fotos_entrada.*.mimes' => 'As fotos devem ser JPG, JPEG, PNG ou WEBP.',
            'fotos_entrada.*.max' => 'Cada foto deve ter no máximo 15 MB.',
        ]);
    }

    private function camposChecklist(): array
    {
        $campos = [];
        $ordem = 0;

        foreach (config('checklist_os') as $grupo => $dados) {
            foreach ($dados['itens'] as $codigo => $nome) {
                $campos[$codigo] = ['grupo' => $grupo, 'nome' => $nome, 'ordem' => ++$ordem];
            }
        }

        return $campos;
    }

    private function salvarChecklist(Pedido $pedido, array $dados, $usuarioId): void
    {
        $salvos = $pedido->checklistItens()->get()->keyBy('codigo');

        foreach ($this->camposChecklist() as $codigo => $campo) {
            // Uma atualização parcial não apaga os demais itens.
            if (!array_key_exists($codigo, $dados)) {
                continue;
            }

            $situacao = $dados[$codigo]['situacao'] ?? 'NAO_VERIFICADO';
            $observacoes = trim($dados[$codigo]['observacoes'] ?? '');
            $observacoes = $observacoes === '' ? null : $observacoes;
            $existente = $salvos->get($codigo);

            if (!$existente && $situacao === 'NAO_VERIFICADO' && $observacoes === null) {
                continue;
            }

            if ($existente && $existente->situacao === $situacao && $existente->observacoes === $observacoes) {
                continue;
            }

            $conferido = $situacao !== 'NAO_VERIFICADO' || $observacoes !== null;

            $pedido->checklistItens()->updateOrCreate(['codigo' => $codigo], [
                ...$campo,
                'situacao' => $situacao,
                'observacoes' => $observacoes,
                'conferido_por' => $conferido ? $usuarioId : null,
                'conferido_em' => $conferido ? now() : null,
            ]);
        }
    }

    private function guardarFotos(Request $request): array
    {
        $guardadas = [];
        $pasta = 'fotos-entrada/' . Str::uuid();

        try {
            foreach (FotoEntrada::POSICOES as $posicao => $titulo) {
                if (!$request->hasFile('fotos_entrada.' . $posicao)) {
                    continue;
                }

                $caminho = $request->file('fotos_entrada.' . $posicao)
                    ->store($pasta, 'local');

                if (!$caminho) {
                    $this->erro(
                        'fotos_entrada',
                        'Não foi possível guardar uma das fotos.'
                    );
                }

                $guardadas[$posicao] = $caminho;
            }
        } catch (\Throwable $erro) {
            Storage::disk('local')->delete(array_values($guardadas));

            throw $erro;
        }

        return $guardadas;
    }

    private function registrarFotos(Pedido $pedido, array $fotos, $usuarioId)
    {
        foreach ($fotos as $posicao => $caminho) {
            if (
                $pedido->fotosEntrada()
                ->where('posicao', $posicao)
                ->exists()
            ) {
                $this->erro(
                    'fotos_entrada',
                    'Já existe uma foto para ' . FotoEntrada::POSICOES[$posicao] . '. A original foi preservada.'
                );
            }

            $pedido->fotosEntrada()->create([
                'usuario_id' => $usuarioId,
                'posicao' => $posicao,
                'caminho' => $caminho,
            ]);
        }
    }

    private function incluirItens(Pedido $pedido, array $novosProdutos, array $novosServicos)
    {
        if (!$novosProdutos && !$novosServicos) {
            return;
        }

        $produtos = Produto::whereIn(
            'id',
            array_column($novosProdutos, 'produto_id')
        )->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $servicos = Servico::whereIn(
            'id',
            array_column($novosServicos, 'servico_id')
        )->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($novosProdutos as $item) {
            $produto = $produtos->get($item['produto_id']);

            if (!$produto) {
                $this->erro(
                    'produtos',
                    'Uma das peças selecionadas não está mais disponível no cadastro.'
                );
            }

            $this->centavos($produto->preco);

            $pedido->itens()->create([
                'produto_id' => $produto->id,
                'quantidade' => $item['quantidade'],
                'preco' => $produto->preco,
                'situacao' => 'PENDENTE',
                'autorizacao' => 'AGUARDANDO',
                'estoque_baixado' => false,
                'quantidade_devolvida' => 0,
            ]);
        }

        foreach ($novosServicos as $item) {
            $servico = $servicos->get($item['servico_id']);

            if (!$servico) {
                $this->erro(
                    'servicos',
                    'Um dos serviços selecionados não está mais disponível.'
                );
            }

            $this->centavos($servico->preco);

            $pedido->servicos()->create([
                'servico_id' => $servico->id,
                'quantidade' => $item['quantidade'],
                'preco' => $servico->preco,
                'situacao' => 'PENDENTE',
                'autorizacao' => 'AGUARDANDO',
            ]);
        }

        $this->recalcularTotais($pedido);
        $this->atualizarIndicadorEstoque($pedido);

        $pedido->save();
    }

    private function sincronizarAndamento(Pedido $pedido, $usuarioId): void
    {
        // Os chamadores já mantêm a OS bloqueada dentro da transação.
        if (in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true)) {
            $pedido->save();
            return;
        }

        $registros = $pedido->itens()->where('situacao', '!=', 'CANCELADO')->get()
            ->concat($pedido->servicos()->where('situacao', '!=', 'CANCELADO')->get());

        $aguardando = $registros->contains(fn ($item) => $item->autorizacao === 'AGUARDANDO');
        $autorizados = $registros->where('autorizacao', 'AUTORIZADO');

        $todosResolvidos = $autorizados->isNotEmpty()
            && $autorizados->every(fn ($item) => in_array($item->situacao, ['EXECUTADO', 'INTERROMPIDO'], true));

        if (!$aguardando && $todosResolvidos) {
            // Reutiliza as conferências existentes e grava concluido_em uma única vez.
            $this->aplicarAndamento($pedido, 'CONCLUIDO', $usuarioId);
            return;
        }

        $iniciada = $registros->contains(fn ($item) => in_array(
            $item->situacao,
            ['EM_EXECUCAO', 'EXECUTADO', 'INTERROMPIDO'],
            true
        ));

        if ($iniciada) {
            $pedido->status = 'EM_EXECUCAO';
        }

        $pedido->save();
    }

    private function conferirAndamento(Pedido $pedido, string $novoStatus)
    {
        if (
            in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true)
            && $novoStatus !== $pedido->status
        ) {
            $this->erro(
                'status',
                'Uma OS encerrada não pode mudar de andamento.'
            );
        }

        if (
            $pedido->status === 'EM_EXECUCAO'
            && $novoStatus === 'ABERTO'
        ) {
            $this->erro(
                'status',
                'Não é possível voltar a OS para ABERTO.'
            );
        }
    }

    private function aplicarAndamento(Pedido $pedido, string $novoStatus, $usuarioId)
    {
        $this->conferirAndamento($pedido, $novoStatus);

        if (
            $novoStatus === 'EM_EXECUCAO'
            && $pedido->status === 'ABERTO'
        ) {
            $iniciado = $pedido->itens()
                ->whereIn('situacao', ['EM_EXECUCAO', 'EXECUTADO', 'INTERROMPIDO'])
                ->exists()
                || $pedido->servicos()
                ->whereIn('situacao', ['EM_EXECUCAO', 'EXECUTADO', 'INTERROMPIDO'])
                ->exists();

            if (!$iniciado) {
                $this->erro(
                    'status',
                    'Utilize Iniciar selecionados nos itens autorizados para começar a execução.'
                );
            }
        }

        if (
            $novoStatus === 'CANCELADO'
            && $pedido->status !== 'CANCELADO'
        ) {
            $pecas = $pedido->itens()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $servicos = $pedido->servicos()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($pecas->concat($servicos) as $registro) {
                if (!in_array($registro->situacao, ['PENDENTE', 'CANCELADO'], true)) {
                    $this->erro(
                        'status',
                        'Há trabalho iniciado, executado ou interrompido. Cancele somente os itens pendentes.'
                    );
                }
            }

            foreach ($pecas->concat($servicos) as $registro) {
                $this->cancelarRegistro(
                    $registro,
                    $usuarioId,
                    'Cancelamento integral da ordem de serviço.'
                );
            }

            $this->recalcularTotais($pedido);
            $this->atualizarIndicadorEstoque($pedido);
        }

        if (
            $novoStatus === 'CONCLUIDO'
            && $pedido->status !== 'CONCLUIDO'
        ) {
            $registros = $pedido->itens()->get()
                ->concat($pedido->servicos()->get());

            foreach ($registros as $registro) {
                if ($registro->situacao === 'CANCELADO') {
                    continue;
                }

                if ($registro->autorizacao === 'AGUARDANDO') {
                    $this->erro(
                        'status',
                        'Registre a decisão do cliente nos itens que ainda aguardam autorização.'
                    );
                }

                if ($registro->autorizacao === 'RECUSADO') {
                    continue;
                }

                if (in_array($registro->situacao, ['PENDENTE', 'EM_EXECUCAO'], true)) {
                    $this->erro(
                        'status',
                        'Finalize, interrompa ou cancele os trabalhos autorizados antes de concluir a OS.'
                    );
                }
            }

            $this->recalcularTotais($pedido);
            $this->atualizarIndicadorEstoque($pedido);

            // Registra somente na transição para concluído, após todas as conferências.
            $pedido->concluido_em = now();
        }

        $pedido->status = $novoStatus;
        $pedido->save();
    }

    private function cancelarRegistro($registro, $usuarioId, string $motivo)
    {
        if ($registro->situacao === 'CANCELADO') {
            return;
        }

        $this->exigirItemPendente($registro);

        $registro->situacao = 'CANCELADO';
        $registro->cancelado_em = now();
        $registro->cancelado_por = $usuarioId;
        $registro->motivo_cancelamento = $motivo;

        $registro->save();
    }

    private function interromperRegistro($registro, array $dados, $usuarioId)
    {
        if ($registro->situacao === 'INTERROMPIDO') {
            return;
        }

        if ($registro->situacao !== 'EM_EXECUCAO') {
            $this->erro(
                'item',
                'Somente trabalhos em execução podem ser interrompidos.'
            );
        }

        $motivo = trim($dados['motivo_interrupcao'] ?? '');

        if ($motivo === '') {
            $this->erro(
                'motivo_interrupcao',
                'Informe o motivo da interrupção.'
            );
        }

        $valor = $this->centavos($dados['valor_cobrado']);
        $original = $this->centavos($registro->preco) * $registro->quantidade;

        if ($valor > $original) {
            $this->erro(
                'valor_cobrado',
                'O valor combinado não pode superar o subtotal original deste item.'
            );
        }

        $devolvida = (int) ($dados['quantidade_devolvida'] ?? 0);

        if ($registro instanceof ItemPedido) {
            if ($devolvida > $registro->quantidade) {
                $this->erro(
                    'quantidade_devolvida',
                    'A devolução não pode superar a quantidade registrada.'
                );
            }

            if ($devolvida > 0) {
                if (!$registro->estoque_baixado) {
                    $this->erro(
                        'estoque',
                        'Não existe baixa de estoque para realizar esta devolução.'
                    );
                }

                if (
                    !in_array(
                        $dados['confirmar_devolucao'] ?? null,
                        ['1', 1, true, 'yes', 'on'],
                        true
                    )
                ) {
                    $this->erro(
                        'confirmar_devolucao',
                        'Confirme que as peças devolvidas podem retornar ao estoque.'
                    );
                }

                $this->devolverEstoque($registro, $devolvida);
            }

            $registro->quantidade_devolvida = $devolvida;

            if ($devolvida === $registro->quantidade) {
                $registro->estoque_baixado = false;
            }
        } elseif ($devolvida > 0) {
            $this->erro(
                'quantidade_devolvida',
                'Serviços não possuem devolução de peças.'
            );
        }

        $registro->situacao = 'INTERROMPIDO';
        $registro->valor_cobrado = $this->decimal($valor);
        $registro->interrompido_em = now();
        $registro->interrompido_por = $usuarioId;
        $registro->motivo_interrupcao = $motivo;

        $registro->save();
    }

    private function devolverEstoque(ItemPedido $registro, int $quantidade)
    {
        $produto = Produto::lockForUpdate()
            ->findOrFail($registro->produto_id);

        if (
            $quantidade <= 0
            || $produto->estoque + $quantidade > 2147483647
        ) {
            $this->erro(
                'estoque',
                'Não foi possível devolver as peças. Confira a quantidade e o saldo.'
            );
        }

        $produto->increment('estoque', $quantidade);
    }

    private function recalcularTotais(Pedido $pedido)
    {
        $registros = $pedido->itens()
            ->where('situacao', '!=', 'CANCELADO')
            ->get()
            ->concat(
                $pedido->servicos()
                    ->where('situacao', '!=', 'CANCELADO')
                    ->get()
            );

        $orcamento = 0;
        $autorizado = 0;

        foreach ($registros as $registro) {
            if (
                $registro->quantidade <= 0
                || $registro->quantidade > 1000000
            ) {
                $this->erro(
                    'itens',
                    'Existe um item com quantidade inválida.'
                );
            }

            $subtotalOriginal = $this->centavos($registro->preco)
                * $registro->quantidade;

            $orcamento += $subtotalOriginal;

            if ($registro->autorizacao === 'AUTORIZADO') {
                if ($registro->situacao === 'INTERROMPIDO') {
                    if ($registro->valor_cobrado === null) {
                        $this->erro(
                            'valor_cobrado',
                            'Existe um item interrompido sem valor combinado.'
                        );
                    }

                    $autorizado += $this->centavos($registro->valor_cobrado);
                } else {
                    $autorizado += $subtotalOriginal;
                }
            }

            if ($orcamento > 9999999999 || $autorizado > 9999999999) {
                $this->erro(
                    'total',
                    'O total ultrapassa o limite permitido.'
                );
            }
        }

        $pedido->total_orcamento = $this->decimal($orcamento);
        $pedido->total = $this->decimal($autorizado);
    }

    private function atualizarIndicadorEstoque(Pedido $pedido)
    {
        $pedido->estoque_baixado = $pedido->itens()
            ->where('estoque_baixado', true)
            ->exists();
    }

    private function alertasEstoque(array $produtoIds): array
    {
        if (!$produtoIds) {
            return [];
        }

        return Produto::whereIn('id', $produtoIds)
            ->whereColumn('estoque', '<=', 'estoque_minimo')
            ->orderBy('nome')
            ->get(['nome', 'estoque', 'estoque_minimo'])
            ->toArray();
    }

    private function erro(string $campo, string $mensagem)
    {
        throw ValidationException::withMessages([
            $campo => $mensagem,
        ]);
    }

    private function centavos($valor): int
    {
        $valor = (string) $valor;

        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $valor)) {
            $this->erro(
                'preco',
                'Existe um valor inválido. Use números e no máximo duas casas decimais.'
            );
        }

        $partes = explode('.', $valor);

        return (int) $partes[0] * 100
            + (int) str_pad($partes[1] ?? '', 2, '0');
    }

    private function decimal(int $centavos): string
    {
        return intdiv($centavos, 100)
            . '.'
            . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}

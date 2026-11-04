<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Veiculos;
use App\Models\FotoEntrada;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'status' => [
                'nullable',
                'string',
                'in:ABERTO,EM_EXECUCAO,CONCLUIDO,CANCELADO',
            ],
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
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
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

        $consulta = Pedido::with([
            'cliente',
            'veiculo',
            'operador',
        ]);

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

        return view('pedidos.index', compact(
            'pedidos',
            'veiculoFiltrado'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);

        $veiculos = Veiculos::with('cliente')
            ->orderBy('placa')
            ->get();

        $produtos = Produto::where('estoque', '>', 0)
            ->orderBy('nome')
            ->get();

        $servicos = Servico::orderBy('nome')->get();

        return view('pedidos.create', compact(
            'veiculos',
            'produtos',
            'servicos'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarFormulario($request, true);
        $fotosGuardadas = $this->guardarFotos($request);

        try {
            $pedido = DB::transaction(function () use ($dados, $request, $fotosGuardadas) {
                $veiculo = Veiculos::lockForUpdate()
                    ->findOrFail($dados['veiculo_id']);

                $pedido = Pedido::create([
                    'status' => 'ABERTO',
                    'total' => '0.00',
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
                    $fotosGuardadas,
                    $request->user()->id
                );

                return $pedido;
            }, 3);
        } catch (\Throwable $erro) {
            Storage::disk('local')->delete(array_values($fotosGuardadas));

            throw $erro;
        }

        return redirect()->route('pedidos.show', $pedido->id)
            ->with('mensagem', 'Ordem de serviço cadastrada com sucesso!')
            ->with('alertas_estoque', $this->alertasEstoque($dados['produtos']));
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
            'itens.canceladoPor',
            'servicos.servico',
            'servicos.canceladoPor',
            'fotosEntrada.operador',
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
            'itens.canceladoPor',
            'servicos.servico',
            'servicos.canceladoPor',
            'fotosEntrada',
        ])->findOrFail($id);

        $produtos = Produto::where('estoque', '>', 0)
            ->orderBy('nome')
            ->get();

        $servicos = Servico::orderBy('nome')->get();

        return view('pedidos.edit', compact(
            'pedido',
            'produtos',
            'servicos'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarFormulario($request, false);
        $fotosGuardadas = $this->guardarFotos($request);

        try {
            DB::transaction(function () use ($id, $dados, $request, $fotosGuardadas) {
                $pedido = Pedido::lockForUpdate()->findOrFail($id);

                $encerrada = in_array(
                    $pedido->status,
                    ['CONCLUIDO', 'CANCELADO'],
                    true
                );

                $temItensNovos = count($dados['novos_produtos']) > 0
                    || count($dados['novos_servicos']) > 0;

                if ($encerrada) {
                    if ($temItensNovos || count($fotosGuardadas) > 0) {
                        throw ValidationException::withMessages([
                            'pedido' => 'Uma OS encerrada não pode receber novos itens ou fotos.',
                        ]);
                    }

                    foreach (['quilometragem_entrada', 'combustivel_entrada', 'avarias_entrada'] as $campo) {
                        if (array_key_exists($campo, $dados)) {
                            $novo = $dados[$campo] ?? null;
                            $atual = $pedido->{$campo};

                            if ((string) $novo !== (string) $atual) {
                                throw ValidationException::withMessages([
                                    $campo => 'Os dados de entrada de uma OS encerrada não podem ser alterados.',
                                ]);
                            }
                        }
                    }
                }

                if (
                    $dados['status'] === 'CANCELADO'
                    && ($temItensNovos || count($fotosGuardadas) > 0)
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'Salve os novos itens e fotos antes de cancelar, ou remova essas inclusões do formulário.',
                    ]);
                }

                if (
                    $temItensNovos
                    && !in_array(
                        $dados['confirmar_itens'] ?? null,
                        ['1', 1, true, 'yes', 'on'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'confirmar_itens' => 'Confirme que os novos itens foram autorizados pelo cliente.',
                    ]);
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
                        $fotosGuardadas,
                        $request->user()->id
                    );

                    foreach (['quilometragem_entrada', 'combustivel_entrada', 'avarias_entrada'] as $campo) {
                        if (array_key_exists($campo, $dados)) {
                            $pedido->{$campo} = $dados[$campo];
                        }
                    }
                }

                $pedido->responsavel = $dados['responsavel'] ?? null;
                $pedido->observacoes = $dados['observacoes'] ?? null;

                $this->aplicarAndamento(
                    $pedido,
                    $dados['status'],
                    $request->user()->id
                );
            }, 3);
        } catch (\Throwable $erro) {
            Storage::disk('local')->delete(array_values($fotosGuardadas));

            throw $erro;
        }

        return redirect()->route('pedidos.show', $id)
            ->with('mensagem', 'Ordem de serviço atualizada com sucesso!')
            ->with('alertas_estoque', $this->alertasEstoque($dados['novos_produtos']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        DB::transaction(function () use ($request, $id) {
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

    public function atualizarItem(Request $request, string $id, string $tipo, string $item)
    {
        $this->autorizarAcesso($request);

        abort_unless(in_array($tipo, ['peca', 'servico'], true), 404);

        $dados = $request->validate([
            'acao' => [
                'required',
                'string',
                'in:iniciar,finalizar,cancelar',
            ],
            'motivo_cancelamento' => [
                'nullable',
                'required_if:acao,cancelar',
                'string',
                'max:1000',
            ],
        ], [
            'acao.required' => 'Selecione uma ação para o item.',
            'acao.string' => 'Selecione uma ação válida.',
            'acao.in' => 'Selecione uma ação válida.',
            'motivo_cancelamento.required_if' => 'Informe o motivo do cancelamento.',
            'motivo_cancelamento.string' => 'O motivo deve ser informado em texto.',
            'motivo_cancelamento.max' => 'O motivo deve ter até 1000 caracteres.',
        ]);

        DB::transaction(function () use ($request, $id, $tipo, $item, $dados) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            if (in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true)) {
                throw ValidationException::withMessages([
                    'item' => 'Os itens de uma OS encerrada não podem ser alterados.',
                ]);
            }

            $relacao = $tipo === 'peca'
                ? $pedido->itens()
                : $pedido->servicos();

            $registro = $relacao->lockForUpdate()->findOrFail($item);

            if ($dados['acao'] === 'cancelar') {
                if ($registro->situacao === 'CANCELADO') {
                    return;
                }

                $motivo = trim($dados['motivo_cancelamento'] ?? '');

                if ($motivo === '') {
                    throw ValidationException::withMessages([
                        'motivo_cancelamento' => 'Informe o motivo do cancelamento.',
                    ]);
                }

                $this->cancelarRegistro(
                    $registro,
                    $request->user()->id,
                    $motivo
                );
            } elseif ($dados['acao'] === 'iniciar') {
                if ($registro->situacao === 'EM_EXECUCAO') {
                    return;
                }

                if ($registro->situacao !== 'PENDENTE') {
                    throw ValidationException::withMessages([
                        'item' => 'Somente um item pendente pode ser iniciado.',
                    ]);
                }

                if (
                    $registro instanceof ItemPedido
                    && $registro->estoque_baixado !== true
                ) {
                    throw ValidationException::withMessages([
                        'item' => 'Confira a baixa de estoque desta peça antes de iniciar sua utilização.',
                    ]);
                }

                $registro->situacao = 'EM_EXECUCAO';
                $registro->save();

                if ($pedido->status === 'ABERTO') {
                    $pedido->status = 'EM_EXECUCAO';
                }
            } else {
                if ($registro->situacao === 'EXECUTADO') {
                    return;
                }

                if ($registro->situacao !== 'EM_EXECUCAO') {
                    throw ValidationException::withMessages([
                        'item' => 'Inicie o item antes de marcá-lo como executado.',
                    ]);
                }

                $registro->situacao = 'EXECUTADO';
                $registro->save();
            }

            $this->recalcularTotal($pedido);
            $this->atualizarIndicadorEstoque($pedido);

            $pedido->save();
        }, 3);

        return redirect()->route('pedidos.edit', $id)
            ->with('mensagem', 'Item atualizado com sucesso!');
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
        return $this->show($request, $id);
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
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
            'quilometragem_entrada' => [
                'nullable',
                'integer',
                'min:0',
                'max:4294967295',
            ],
            'combustivel_entrada' => [
                'nullable',
                'string',
                'in:VAZIO,UM_QUARTO,METADE,TRES_QUARTOS,CHEIO',
            ],
            'avarias_entrada' => ['nullable', 'string', 'max:5000'],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:5000'],

            'fotos_entrada' => [
                $cadastro ? 'required' : 'nullable',
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
            $regras['veiculo_id'] = [
                'required',
                'integer',
                'exists:veiculos,id',
            ];
        } else {
            $regras['status'] = [
                'required',
                'string',
                'in:ABERTO,EM_EXECUCAO,CONCLUIDO,CANCELADO',
            ];

            $regras['confirmar_itens'] = ['nullable', 'accepted'];
        }

        foreach (FotoEntrada::POSICOES as $posicao => $titulo) {
            $regras['fotos_entrada.' . $posicao] = [
                $cadastro ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:15360',
            ];
        }

        return $request->validate($regras, [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'string' => 'O campo :attribute deve ser um texto.',
            'array' => 'Confira os dados enviados em :attribute.',
            'distinct' => 'Não repita o mesmo item na lista de novas inclusões.',
            'exists' => 'Um dos registros selecionados não foi encontrado.',
            'in' => 'Selecione uma opção válida para :attribute.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute ultrapassa o limite permitido.',
            'accepted' => 'Confirme que os novos itens foram autorizados pelo cliente.',

            'fotos_entrada.required' => 'Envie as quatro fotos de entrada.',
            'fotos_entrada.*.required' => 'Envie uma foto de cada posição do veículo.',
            'fotos_entrada.*.uploaded' => 'Não foi possível enviar uma das fotos. Confira o tamanho do arquivo.',
            'fotos_entrada.*.image' => 'Selecione uma imagem válida.',
            'fotos_entrada.*.mimes' => 'As fotos devem ser JPG, JPEG, PNG ou WEBP.',
            'fotos_entrada.*.max' => 'Cada foto deve ter no máximo 15 MB.',
        ], [
            'veiculo_id' => 'veículo',
            'status' => 'andamento',
            'quilometragem_entrada' => 'quilometragem',
            'combustivel_entrada' => 'combustível',
            'avarias_entrada' => 'avarias',
            'responsavel' => 'responsável',
            'observacoes' => 'observações',

            $listaProdutos => 'peças',
            $listaProdutos . '.*.produto_id' => 'peça',
            $listaProdutos . '.*.quantidade' => 'quantidade de peças',

            $listaServicos => 'serviços',
            $listaServicos . '.*.servico_id' => 'serviço',
            $listaServicos . '.*.quantidade' => 'quantidade de serviços',
        ]);
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
                    throw ValidationException::withMessages([
                        'fotos_entrada' => 'Não foi possível guardar uma das fotos. Tente novamente.',
                    ]);
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
                throw ValidationException::withMessages([
                    'fotos_entrada' => 'Já existe uma foto para ' . FotoEntrada::POSICOES[$posicao] . '. A foto original foi preservada.',
                ]);
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
        if (count($novosProdutos) === 0 && count($novosServicos) === 0) {
            return;
        }

        if (
            count($novosProdutos) > 0
            && $pedido->itens()
            ->where('situacao', '!=', 'CANCELADO')
            ->whereNull('estoque_baixado')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'novos_produtos' => 'Esta OS possui peças antigas com baixa de estoque ainda não conferida. Confira esse histórico antes de adicionar outras peças.',
            ]);
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

            if (!$produto || $produto->estoque < $item['quantidade']) {
                throw ValidationException::withMessages([
                    'produtos' => 'Estoque insuficiente para uma das peças selecionadas.',
                ]);
            }

            $this->centavos($produto->preco);

            $pedido->itens()->create([
                'produto_id' => $produto->id,
                'quantidade' => $item['quantidade'],
                'preco' => $produto->preco,
                'situacao' => 'PENDENTE',
                'estoque_baixado' => true,
            ]);

            $produto->decrement('estoque', $item['quantidade']);
        }

        foreach ($novosServicos as $item) {
            $servico = $servicos->get($item['servico_id']);

            if (!$servico) {
                throw ValidationException::withMessages([
                    'servicos' => 'Um dos serviços selecionados não está mais disponível.',
                ]);
            }

            $this->centavos($servico->preco);

            $pedido->servicos()->create([
                'servico_id' => $servico->id,
                'quantidade' => $item['quantidade'],
                'preco' => $servico->preco,
                'situacao' => 'PENDENTE',
            ]);
        }

        $this->recalcularTotal($pedido);
        $this->atualizarIndicadorEstoque($pedido);

        $pedido->save();
    }

    private function conferirAndamento(Pedido $pedido, string $novoStatus)
    {
        if (
            in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true)
            && $novoStatus !== $pedido->status
        ) {
            throw ValidationException::withMessages([
                'status' => 'Uma OS concluída ou cancelada não pode mudar de andamento.',
            ]);
        }

        if (
            $pedido->status === 'EM_EXECUCAO'
            && $novoStatus === 'ABERTO'
        ) {
            throw ValidationException::withMessages([
                'status' => 'Não é possível voltar a OS para ABERTO.',
            ]);
        }
    }

    private function aplicarAndamento(Pedido $pedido, string $novoStatus, $usuarioId)
    {
        $this->conferirAndamento($pedido, $novoStatus);

        if (
            $novoStatus === 'CANCELADO'
            && $pedido->status !== 'CANCELADO'
        ) {
            $pecas = $pedido->itens()
                ->orderBy('produto_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $servicos = $pedido->servicos()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($pecas->concat($servicos) as $registro) {
                if (
                    $registro->situacao !== 'CANCELADO'
                    && $registro->situacao !== 'PENDENTE'
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'Esta OS possui trabalho iniciado ou executado e não pode ser cancelada integralmente. Cancele somente os itens pendentes.',
                    ]);
                }
            }

            foreach ($pecas->concat($servicos) as $registro) {
                $this->cancelarRegistro(
                    $registro,
                    $usuarioId,
                    'Cancelamento integral da ordem de serviço.'
                );
            }

            $this->recalcularTotal($pedido);
            $this->atualizarIndicadorEstoque($pedido);
        }

        if (
            $novoStatus === 'CONCLUIDO'
            && $pedido->status !== 'CONCLUIDO'
        ) {
            $pecasPendentes = $pedido->itens()
                ->whereIn('situacao', ['PENDENTE', 'EM_EXECUCAO'])
                ->exists();

            $servicosPendentes = $pedido->servicos()
                ->whereIn('situacao', ['PENDENTE', 'EM_EXECUCAO'])
                ->exists();

            if ($pecasPendentes || $servicosPendentes) {
                throw ValidationException::withMessages([
                    'status' => 'Finalize os trabalhos ou cancele os itens pendentes antes de concluir a OS.',
                ]);
            }

            $this->recalcularTotal($pedido);
            $this->atualizarIndicadorEstoque($pedido);
        }

        $pedido->status = $novoStatus;
        $pedido->save();
    }

    private function cancelarRegistro($registro, $usuarioId, string $motivo)
    {
        if ($registro->situacao === 'CANCELADO') {
            return;
        }

        if ($registro->situacao !== 'PENDENTE') {
            throw ValidationException::withMessages([
                'item' => 'Somente itens pendentes podem ser cancelados. Trabalhos iniciados ou executados exigem avaliação da oficina.',
            ]);
        }

        if ($registro instanceof ItemPedido) {
            if ($registro->estoque_baixado === null) {
                throw ValidationException::withMessages([
                    'estoque' => 'A baixa de estoque desta peça antiga precisa ser conferida antes do cancelamento.',
                ]);
            }

            if ($registro->estoque_baixado === true) {
                $produto = Produto::lockForUpdate()
                    ->findOrFail($registro->produto_id);

                $novoSaldo = $produto->estoque + $registro->quantidade;

                if (
                    $registro->quantidade <= 0
                    || $novoSaldo > 2147483647
                ) {
                    throw ValidationException::withMessages([
                        'estoque' => 'Não foi possível devolver a peça ao estoque. Confira a quantidade e o saldo.',
                    ]);
                }

                $produto->increment('estoque', $registro->quantidade);
            }

            $registro->estoque_baixado = false;
        }

        $registro->situacao = 'CANCELADO';
        $registro->cancelado_em = now();
        $registro->cancelado_por = $usuarioId;
        $registro->motivo_cancelamento = $motivo;

        $registro->save();
    }

    private function recalcularTotal(Pedido $pedido)
    {
        $pecas = $pedido->itens()
            ->where('situacao', '!=', 'CANCELADO')
            ->get();

        $servicos = $pedido->servicos()
            ->where('situacao', '!=', 'CANCELADO')
            ->get();

        $total = 0;

        foreach ($pecas->concat($servicos) as $registro) {
            if (
                $registro->quantidade <= 0
                || $registro->quantidade > 1000000
            ) {
                throw ValidationException::withMessages([
                    'itens' => 'Existe um item com quantidade inválida. Confira a OS.',
                ]);
            }

            $total += $this->centavos($registro->preco)
                * $registro->quantidade;

            $this->conferirTotal($total);
        }

        $pedido->total = $this->decimal($total);
    }

    private function atualizarIndicadorEstoque(Pedido $pedido)
    {
        $pecasAtivas = $pedido->itens()
            ->where('situacao', '!=', 'CANCELADO')
            ->get();

        if ($pecasAtivas->contains(function ($item) {
            return $item->estoque_baixado === null;
        })) {
            $pedido->estoque_baixado = null;
            return;
        }

        $pedido->estoque_baixado = $pecasAtivas->contains(function ($item) {
            return $item->estoque_baixado === true;
        });
    }

    private function alertasEstoque(array $itens): array
    {
        return Produto::whereIn(
            'id',
            array_column($itens, 'produto_id')
        )->whereColumn('estoque', '<=', 'estoque_minimo')
            ->orderBy('nome')
            ->get(['nome', 'estoque', 'estoque_minimo'])
            ->toArray();
    }

    private function conferirTotal(int $total)
    {
        if ($total > 9999999999) {
            throw ValidationException::withMessages([
                'total' => 'O total da ordem de serviço ultrapassa o limite permitido.',
            ]);
        }
    }

    private function centavos($valor): int
    {
        $valor = (string) $valor;

        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $valor)) {
            throw ValidationException::withMessages([
                'preco' => 'Há uma peça ou serviço com preço inválido. Confira o cadastro.',
            ]);
        }

        $partes = explode('.', $valor);

        return ((int) $partes[0] * 100)
            + (int) str_pad($partes[1] ?? '', 2, '0');
    }

    private function decimal(int $centavos): string
    {
        return intdiv($centavos, 100)
            . '.'
            . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}

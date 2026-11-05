@php
$consulta = $consulta ?? false;
$agendamento = $agendamento ?? null;
$edicao = isset($pedido);
$encerrada = $edicao && in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true);
$pedidoAtual = $pedido ?? null;
$formularioId = $edicao ? 'formulario-editar-os' : 'formulario-os';
$fotos = $edicao ? $pedido->fotosEntrada->keyBy('posicao') : collect();
$checklistSalvo = $edicao ? $pedido->checklistItens->keyBy('codigo') : collect();
$prefixo = $edicao ? 'novos_' : '';
$grupos = [
$prefixo . 'produtos' => ['titulo' => 'Peças do orçamento', 'campo' => 'produto_id', 'itens' => $produtos ?? collect()],
$prefixo . 'servicos' => ['titulo' => 'Serviços e mão de obra', 'campo' => 'servico_id', 'itens' => $servicos ?? collect()],
];
$anteriores = [];
foreach ($grupos as $tipo => $grupo) {
$anteriores[$tipo] = is_array(old($tipo)) ? old($tipo) : [];
}
$andamentos = ['ABERTO' => 'Aberto', 'EM_EXECUCAO' => 'Em execução', 'CONCLUIDO' => 'Concluído', 'CANCELADO' => 'Cancelado'];

@endphp

@if(!$consulta)
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">{{ $edicao ? 'Atualizar OS #' . $pedido->id : 'Abrir OS' }}</h1>

    <div class="d-flex justify-content-end align-items-center flex-wrap gap-3">
        <div class="small text-end">
            <div><strong>Prévia do orçamento:</strong> <span id="total-orcamento-previsto" aria-live="polite">R$ {{ number_format($pedidoAtual?->total_orcamento ?? 0, 2, ',', '.') }}</span></div>
            <div><strong>Prévia do autorizado:</strong> <span id="total-autorizado-previsto" aria-live="polite">R$ {{ number_format($pedidoAtual?->total ?? 0, 2, ',', '.') }}</span></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" form="{{ $formularioId }}" class="btn btn-primary" @disabled(!$edicao && $veiculos->isEmpty())>{{ $edicao ? 'Salvar' : 'Abrir OS' }}</button>
            <a href="{{ $edicao ? route('pedidos.show', $pedido->id) : ($agendamento ? route('agendamentos.show', $agendamento->id) : route('pedidos.index')) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('alertas_estoque'))
<div class="alert alert-warning" role="alert">
    <p class="fw-bold mb-2">Atenção à reposição:</p>

    <ul class="mb-0">
        @foreach(session('alertas_estoque') as $alerta)
        <li>{{ $alerta['nome'] }} — Estoque: {{ $alerta['estoque'] }}; mínimo: {{ $alerta['estoque_minimo'] }}.</li>
        @endforeach
    </ul>
</div>
@endif

@if($encerrada)
<div class="alert alert-info">Esta OS está encerrada. Somente o responsável e as observações podem ser atualizados.</div>
@endif

@if($edicao)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <p class="mb-1"><strong>Cliente:</strong> {{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</p>
        <p class="mb-3"><strong>Veículo:</strong> {{ trim(($pedido->veiculo?->marca ?? '') . ' ' . ($pedido->veiculo?->modelo ?? '')) }} — {{ $pedido->veiculo?->placa }}</p>

    </div>
</div>

@else
@if($veiculos->isEmpty())
<div class="alert alert-warning">Cadastre um veículo antes de abrir uma OS.</div>
@endif



@endif
@if(!$edicao)
<p class="text-muted">Para abrir a OS, selecione o veículo e descreva o relato do cliente. As demais informações podem ser preenchidas depois.</p>
@endif
<form id="{{ $formularioId }}" method="POST" action="{{ $edicao ? route('pedidos.update', $pedido->id) : route('pedidos.store') }}" enctype="multipart/form-data" data-recuperados="{{ old('_method') === 'PUT' ? '1' : '0' }}" data-orcamento="{{ $pedidoAtual?->total_orcamento ?? '0.00' }}" data-autorizado="{{ $pedidoAtual?->total ?? '0.00' }}" @if(!$edicao) data-rascunho="mazotto.os.rascunho.{{ auth()->id() }}" @endif>
    @csrf
    @if(!$edicao && $agendamento)
    <input type="hidden" name="agendamento_id" value="{{ $agendamento->id }}">
    <div class="alert alert-info">Atendimento do agendamento de {{ $agendamento->data_hora->format('d/m/Y H:i') }}. Ele será marcado como atendido somente ao salvar esta OS.</div>
    @endif
    @if($edicao)
    @method('PUT')
    @endif
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h2 class="h5 mb-0">Dados do atendimento</h2>

                @if(!$edicao && !$agendamento)
                <a id="cadastrar-veiculo-os" href="{{ route('veiculos.create', ['origem' => 'os']) }}" class="btn btn-outline-dark"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Cadastrar veículo</a>
                @endif
            </div>

            @if($edicao)
            <div class="mb-3">
                <p class="mb-1"><strong>Andamento:</strong> <span class="badge bg-secondary">{{ $andamentos[$pedido->status] ?? $pedido->status }}</span></p>
                <p class="form-text mb-0">O andamento é automático. A OS será concluída quando todos os trabalhos autorizados estiverem executados ou interrompidos e não houver itens aguardando decisão. Salve alterações antes de usar os botões dos itens.</p>
            </div>

            @else
            @php
            $veiculoSelecionado = $veiculos->firstWhere(
            'id',
            old('veiculo_id', $agendamento?->veiculo_id ?? request()->query('veiculo_id'))
            );

            $rotuloVeiculo = fn ($veiculo) => $veiculo->placa . ' - '
            . ($veiculo->modelo ?? 'Modelo não informado') . ' - '
            . ($veiculo->cliente?->nome ?? 'Sem cliente cadastrado');

            @endphp
            <div class="mb-3">
                <label for="veiculo-busca" class="form-label">Pesquisar veículo</label>
                <input id="veiculo-busca" name="veiculo_busca" type="text" list="lista-veiculos" value="{{ old('veiculo_busca', $veiculoSelecionado ? $rotuloVeiculo($veiculoSelecionado) : '') }}" class="form-control" autocomplete="off" placeholder="Digite a placa, o modelo ou o nome do cliente" aria-describedby="veiculo-ajuda" @readonly($agendamento !==null) required>
                <input id="veiculo_id" name="veiculo_id" type="hidden" value="{{ $veiculoSelecionado?->id }}">

                <datalist id="lista-veiculos">
                    @foreach($veiculos as $veiculo)
                    <option value="{{ $rotuloVeiculo($veiculo) }}" data-id="{{ $veiculo->id }}" data-placa="{{ $veiculo->placa }}"></option>
                    @endforeach
                </datalist>

                <p id="veiculo-ajuda" class="form-text mb-0">Escolha um veículo sugerido. A OS utiliza o cliente vinculado a ele, se houver.</p>
            </div>

            @endif
            <div class="mb-3">
                <label for="responsavel" class="form-label">Responsável pelo serviço</label>
                <input id="responsavel" name="responsavel" type="text" value="{{ old('responsavel', $pedidoAtual?->responsavel) }}" class="form-control" maxlength="255">
            </div>

            <div>
                <label for="observacoes" class="form-label">Relato do cliente / solicitação</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="4" maxlength="5000" required>{{ old('observacoes', $pedidoAtual?->observacoes ?? $agendamento?->motivo) }}</textarea>
            </div>
        </div>
    </div>

    <fieldset @disabled($encerrada)>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Condições de entrada</h2>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="quilometragem_entrada" class="form-label">Quilometragem de entrada</label>
                        <input id="quilometragem_entrada" name="quilometragem_entrada" type="number" value="{{ old('quilometragem_entrada', $pedidoAtual?->quilometragem_entrada) }}" class="form-control" min="0" max="4294967295" step="1">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="combustivel_entrada" class="form-label">Combustível na entrada</label>
                        <select id="combustivel_entrada" name="combustivel_entrada" class="form-select">
                            <option value="">Não informado</option>
                            @foreach(App\Models\Pedido::COMBUSTIVEIS as $valor => $titulo)
                            <option value="{{ $valor }}" @selected(old('combustivel_entrada', $pedidoAtual?->combustivel_entrada) === $valor)>{{ $titulo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="avarias_entrada" class="form-label">Avarias na entrada</label>
                        <textarea id="avarias_entrada" name="avarias_entrada" class="form-control" rows="3" maxlength="5000">{{ old('avarias_entrada', $pedidoAtual?->avarias_entrada) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Fotos da entrada</h2>
                <p class="text-muted">Fotos opcionais. Você pode registrar agora ou completar depois, enquanto a OS estiver aberta. Até 15 MB por imagem em JPG, JPEG, PNG ou WEBP. Se sair da página ou ocorrer erro, selecione os arquivos novamente.</p>

                <div class="row g-4">
                    @foreach(App\Models\FotoEntrada::POSICOES as $posicao => $titulo)
                    @php
                    $foto = $fotos->get($posicao);
                    @endphp

                    <div class="col-12 col-md-6">
                        <p class="fw-bold mb-2">{{ $titulo }}</p>

                        @if($foto)
                        <a href="{{ route('pedidos.fotos.show', ['id' => $pedido->id, 'foto' => $foto->id]) }}" target="_blank" rel="noopener">
                            <img src="{{ route('pedidos.fotos.show', ['id' => $pedido->id, 'foto' => $foto->id]) }}" alt="{{ $titulo }}" class="img-fluid rounded border" style="width: 100%; height: 210px; object-fit: contain;">
                        </a>
                        @else
                        <label for="foto-{{ $posicao }}" class="form-label">Adicionar {{ $titulo }}</label>
                        <input id="foto-{{ $posicao }}" name="fotos_entrada[{{ $posicao }}]" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" data-foto-entrada>
                        <img id="previa-{{ $posicao }}" alt="Prévia: {{ $titulo }}" class="img-fluid rounded border mt-2" style="width: 100%; height: 210px; object-fit: contain;" hidden>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <details class="card border-0 shadow-sm mb-4" @if($consulta || old('checklist')) open @endif>
            <summary class="card-header bg-white p-3 fw-bold">Checklist de inspeção — clique para abrir ou fechar</summary>
            <div class="card-body p-4">
                <p class="text-muted">Preenchimento opcional. Marcar um problema não autoriza o reparo nem adiciona cobrança.</p>

                @foreach(config('checklist_os') as $grupoChecklist => $dadosGrupo)
                @php
                $opcoes = $grupoChecklist === 'ACESSORIOS'
                ? ['PRESENTE' => 'Presente', 'AUSENTE' => 'Ausente']
                : ['OK' => 'OK', 'ATENCAO' => 'NÃO OK'];
                $temMarcados = collect($dadosGrupo['itens'])->keys()->contains(function ($codigo) use ($checklistSalvo) {
                $registro = $checklistSalvo->get($codigo);
                return $registro && ($registro->situacao !== 'NAO_VERIFICADO' || filled($registro->observacoes));
                });
                @endphp

                @if(!$consulta || $temMarcados)
                <h3 class="h6 mt-4">{{ $dadosGrupo['titulo'] }}</h3>
                <div class="table-responsive">
                    <table class="table table-striped align-middle tabela-checklist" style="width: 100%; min-width: 720px; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 33.333%;">
                            <col style="width: 33.333%;">
                            <col style="width: 33.333%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Situação</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dadosGrupo['itens'] as $codigo => $nome)
                            @php
                            $registro = $checklistSalvo->get($codigo);
                            $situacao = $consulta ? ($registro?->situacao ?? 'NAO_VERIFICADO') : (old('checklist.' . $codigo) !== null ? old('checklist.' . $codigo . '.situacao', 'NAO_VERIFICADO') : ($registro?->situacao ?? 'NAO_VERIFICADO'));
                            @endphp
                            @if(!$consulta || ($registro && ($registro->situacao !== 'NAO_VERIFICADO' || filled($registro->observacoes))))
                            <tr data-checklist-linha>
                                <td style="overflow-wrap: anywhere;">{{ $nome }}</td>
                                <td style="overflow-wrap: anywhere;">
                                    @if($consulta)
                                    {{ $opcoes[$situacao] ?? 'Não verificado' }}
                                    @else
                                    <div role="group" aria-label="Situação de {{ $nome }}" class="d-flex flex-wrap gap-3">
                                        @foreach($opcoes as $valor => $titulo)
                                        <div class="form-check">
                                            <input id="check-{{ $codigo }}-{{ $valor }}" name="checklist[{{ $codigo }}][situacao]" value="{{ $valor }}" type="radio" class="form-check-input" @checked($situacao===$valor)>
                                            <label for="check-{{ $codigo }}-{{ $valor }}" class="form-check-label">{{ $titulo }}</label>
                                        </div>
                                        @endforeach
                                        <button type="button" class="btn btn-sm btn-link p-0" data-limpar-checklist>Desmarcar</button>
                                    </div>
                                    @endif
                                </td>
                                <td style="overflow-wrap: anywhere;">
                                    @if($consulta)
                                    <span style="white-space: pre-wrap;">{{ $registro?->observacoes }}</span>
                                    @else
                                    <label for="check-observacao-{{ $codigo }}" class="visually-hidden">Observações de {{ $nome }}</label>
                                    <textarea id="check-observacao-{{ $codigo }}" name="checklist[{{ $codigo }}][observacoes]" class="form-control" rows="1" maxlength="1000">{{ old('checklist.' . $codigo . '.observacoes', $registro?->observacoes) }}</textarea>
                                    @endif
                                    @if($registro?->conferido_em)
                                    <small class="text-muted d-block">{{ $registro->conferido_em->format('d/m/Y H:i') }} — {{ $registro->conferente?->name ?? 'Não informado' }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @endforeach
                @if($consulta && !$checklistSalvo->contains(fn ($registro) => $registro->situacao !== 'NAO_VERIFICADO' || filled($registro->observacoes)))
                <p class="text-muted mb-0">Nenhum item conferido ou observado.</p>
                @endif
            </div>
        </details>

        @if(!$consulta)
    </fieldset>
</form>

@if($edicao)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Itens registrados</h2>
        <p class="text-muted">As decisões do cliente e as ações desta tabela são salvas imediatamente.</p>
        @include('pedidos.itens-tabela', ['modo' => 'editar'])
    </div>
</div>
@endif
@if(!$encerrada)
<div id="aviso-itens" class="alert alert-warning d-none" role="alert" tabindex="-1"></div>

@foreach($grupos as $tipo => $grupo)
<div class="card border-0 shadow-sm mb-4" data-grupo="{{ $tipo }}" data-campo="{{ $grupo['campo'] }}">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">{{ $grupo['titulo'] }}</h2>

        <div class="row g-3 align-items-end mb-3">
            <div class="col-12 col-md-7">
                <label for="{{ $tipo }}-selecao" class="form-label">Selecione um item</label>
                <select id="{{ $tipo }}-selecao" class="form-select" data-selecao>
                    <option value="">Selecione...</option>
                    @foreach($grupo['itens'] as $item)
                    <option value="{{ $item->id }}" data-nome="{{ $item->nome }}" data-preco="{{ $item->preco }}">{{ $item->nome }} — R$ {{ number_format($item->preco, 2, ',', '.') }}@if(in_array($tipo, ['produtos', 'novos_produtos'], true)) — Físico: {{ $item->estoque }} · Reservado: {{ $item->estoque_reservado }} · Disponível: {{ $item->estoque_disponivel }}@endif</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="{{ $tipo }}-quantidade" class="form-label">Quantidade</label>
                <input id="{{ $tipo }}-quantidade" type="number" value="1" class="form-control" min="1" max="1000000" step="1" data-quantidade-nova>
            </div>

            <div class="col-6 col-md-3">
                <button type="button" class="btn btn-outline-primary w-100" data-adicionar>Adicionar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nova inclusão</th>
                        <th>Preço unitário</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ação</th>
                    </tr>
                </thead>

                <tbody data-itens>
                    <tr data-vazio>
                        <td colspan="5">Nenhuma nova inclusão.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

<p class="text-muted">Salvar acrescenta os itens ao orçamento e reserva as peças disponíveis, sem baixar o estoque físico. O que faltar fica aguardando reposição. A baixa acontece ao iniciar.</p>
@endif
<template id="modelo-nova-inclusao">
    <tr>
        <td>
            <span data-nome></span>
            <input type="hidden" data-identificador>
        </td>
        <td class="text-nowrap" data-preco></td>
        <td>
            <label class="visually-hidden" data-rotulo>Quantidade</label>
            <input type="number" class="form-control" min="1" max="1000000" step="1" required data-quantidade style="min-width: 90px;">
        </td>
        <td class="text-nowrap" data-subtotal></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-remover>Remover</button></td>
    </tr>
</template>

<script id="novas-inclusoes-anteriores" type="application/json">
    @json($anteriores)
</script>

@include('pedidos.editar-script')
@endif

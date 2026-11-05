@php
$editandoItens = $modo === 'editar'
&& in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true);

$imprimindoItens = $modo === 'imprimir';

$situacoes = [
'PENDENTE' => 'Pendente',
'EM_EXECUCAO' => 'Em execução',
'EXECUTADO' => 'Executado',
'CANCELADO' => 'Cancelado',
'INTERROMPIDO' => 'Interrompido',
];

$autorizacoes = [
'AGUARDANDO' => 'Aguardando',
'AUTORIZADO' => 'Autorizado',
'RECUSADO' => 'Recusado',
];

$coresAutorizacao = [
'AGUARDANDO' => 'bg-secondary',
'AUTORIZADO' => 'bg-success',
'RECUSADO' => 'bg-danger',
];

$listasItens = [
'peca' => $pedido->itens,
'servico' => $pedido->servicos,
];
@endphp

@if(!$imprimindoItens && in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true))
@php
$pecasAguardandoReposicao = $pedido->itens->filter(fn ($item) => $item->quantidade_pendente > 0);
@endphp
@if($pecasAguardandoReposicao->isNotEmpty())
<div class="alert alert-warning" role="alert">
    <p class="fw-bold mb-2">Peças aguardando reposição</p>
    <ul class="mb-2">
        @foreach($pecasAguardandoReposicao as $pecaPendente)
        <li>{{ $pecaPendente->produto?->nome ?? 'Peça não encontrada' }}: {{ $pecaPendente->quantidade_reservada }} de {{ $pecaPendente->quantidade }} reservada(s); faltam {{ $pecaPendente->quantidade_pendente }}.</li>
        @endforeach
    </ul>
    <p class="mb-0">O orçamento pode continuar salvo. A execução dessas peças será liberada quando a quantidade estiver totalmente reservada. Após atualizar o estoque, recarregue esta tela para conferir.</p>
</div>
@endif
@endif

@if($editandoItens)
<form id="iniciar-lote" method="POST" action="{{ route('pedidos.itens.autorizar-iniciar', $pedido->id) }}" data-acao-item data-lote>
    @csrf
    @method('PATCH')
</form>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <p class="text-muted mb-0">Selecione os itens. Autorizar e iniciar atua nos pendentes; marcar executados atua nos que estão em execução.</p>
    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="iniciar-lote" data-acao-lote="autorizar-iniciar" class="btn btn-primary" id="botao-iniciar-lote" disabled>Autorizar e iniciar selecionados (0)</button>
        <button type="submit" form="iniciar-lote" formaction="{{ route('pedidos.itens.finalizar-lote', $pedido->id) }}" data-acao-lote="finalizar" class="btn btn-success" id="botao-finalizar-lote" disabled>Marcar selecionados como executados (0)</button>
    </div>
</div>
@endif

<div @class(['table-responsive'=> !$imprimindoItens])>
    <table @class(['table table-striped align-middle bg-white'=> !$imprimindoItens])>
        <thead>
            <tr>
                @if($editandoItens)
                <th><input type="checkbox" id="selecionar-todos-itens" class="form-check-input" aria-label="Selecionar todos os itens disponíveis para iniciar ou marcar como executados"></th>
                @endif

                <th>Tipo / item</th>
                <th>Quantidade</th>
                <th>Preço unitário</th>
                <th>Autorização / execução</th>
                <th>Orçado</th>
                <th>Autorizado</th>

                @if($editandoItens)
                <th>Ações</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @foreach($listasItens as $tipoItem => $registros)
            @foreach($registros as $item)
            @php
            $chave = $tipoItem . '-' . $item->id;
            $alvo = $modo . '-item-' . $chave;
            $peca = $tipoItem === 'peca';
            $cancelado = $item->situacao === 'CANCELADO';
            $interrompido = $item->situacao === 'INTERROMPIDO';

            $pendente = $item->situacao === 'PENDENTE'
            && (!$peca || !$item->estoque_baixado);

            $podeIniciar = $pendente && $item->autorizacao === 'AUTORIZADO';
            $selecionavel = ($pendente && in_array($item->autorizacao, ['AGUARDANDO', 'AUTORIZADO'], true))
            || $item->situacao === 'EM_EXECUCAO';

            $subtotalOriginal = $item->quantidade * $item->preco;
            $subtotalOrcado = $cancelado ? 0 : $subtotalOriginal;
            $subtotalAutorizado = 0;

            if (!$cancelado && $item->autorizacao === 'AUTORIZADO') {
            $subtotalAutorizado = $interrompido
            ? $item->valor_cobrado
            : $subtotalOriginal;
            }

            $rotaItem = route('pedidos.itens.update', [
            'id' => $pedido->id,
            'tipo' => $tipoItem,
            'item' => $item->id,
            ]);

            $recuperarItem = old('item_retorno') === $chave;

            $quantidadeExibida = $recuperarItem && old('acao') === 'alterar_quantidade'
            ? old('quantidade', $item->quantidade)
            : $item->quantidade;
            @endphp

            <tr id="{{ $alvo }}" @class(['table-secondary'=> $cancelado && !$imprimindoItens])>
                @if($editandoItens)
                <td>
                    @if($selecionavel)
                    <input type="checkbox" value="{{ $tipoItem }}:{{ $item->id }}" form="iniciar-lote" class="form-check-input" data-selecao-item data-situacao="{{ $item->situacao }}" data-autorizacao="{{ $item->autorizacao }}" aria-label="Selecionar {{ $peca ? $item->produto?->nome : $item->servico?->nome }}">
                    @endif
                </td>
                @endif

                <td>
                    <strong>{{ $peca ? 'Peça' : 'Serviço' }}</strong>
                    <div>{{ $peca ? ($item->produto?->nome ?? 'Peça não encontrada') : ($item->servico?->nome ?? 'Serviço não encontrado') }}</div>

                    @if(!$imprimindoItens && $peca && $pendente && $item->autorizacao !== 'RECUSADO' && in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true))
                    <div class="small mt-2">
                        <span class="badge {{ $item->quantidade_pendente > 0 ? 'bg-warning text-dark' : 'bg-success' }}">Reservado: {{ $item->quantidade_reservada }} de {{ $item->quantidade }}</span>
                        @if($item->quantidade_pendente > 0)
                        <span class="d-block text-muted mt-1">Aguardando reposição: {{ $item->quantidade_pendente }}</span>
                        @endif
                    </div>
                    @endif

                    @if($cancelado)
                    <div class="small mt-2">
                        <div><strong>Motivo:</strong> {{ $item->motivo_cancelamento }}</div>
                        <div><strong>Funcionário:</strong> {{ $item->canceladoPor?->name }}</div>
                        <div>{{ $item->cancelado_em?->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif

                    @if($interrompido)
                    <div class="small mt-2">
                        <div><strong>Motivo:</strong> {{ $item->motivo_interrupcao }}</div>
                        <div><strong>Funcionário:</strong> {{ $item->interrompidoPor?->name }}</div>
                        <div>{{ $item->interrompido_em?->format('d/m/Y H:i') }}</div>
                        <div><strong>Valor combinado:</strong> R$ {{ number_format($item->valor_cobrado ?? 0, 2, ',', '.') }}</div>

                        @if($peca)
                        <div><strong>Devolvido ao estoque:</strong> {{ $item->quantidade_devolvida }} unidade(s)</div>
                        @endif
                    </div>
                    @endif
                </td>

                <td style="min-width: 100px;">
                    @if($editandoItens && $pendente)
                    <form method="POST" action="{{ $rotaItem }}" data-acao-item data-alvo="{{ $alvo }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="acao" value="alterar_quantidade">
                        <input type="hidden" name="item_retorno" value="{{ $chave }}">

                        <label for="quantidade-{{ $chave }}" class="visually-hidden">Quantidade do item</label>
                        <input id="quantidade-{{ $chave }}" name="quantidade" type="number" class="form-control form-control-sm" min="1" max="1000000" step="1" value="{{ $quantidadeExibida }}" data-quantidade-orcamento data-original="{{ $item->quantidade }}" data-preco="{{ $item->preco }}" data-autorizado="{{ $item->autorizacao === 'AUTORIZADO' ? '1' : '0' }}" data-previa="previa-{{ $alvo }}" required>
                        <button type="submit" class="btn btn-sm btn-outline-dark mt-1">Salvar quantidade</button>
                    </form>
                    <p class="form-text mb-0">Alterar a quantidade exige nova autorização.</p>
                    @else
                    {{ $item->quantidade }}
                    @endif
                </td>

                <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>

                <td>
                    @if($imprimindoItens)
                    <div>{{ $autorizacoes[$item->autorizacao] ?? $item->autorizacao }}</div>
                    @else
                    <span class="badge {{ $coresAutorizacao[$item->autorizacao] ?? 'bg-secondary' }}">{{ $autorizacoes[$item->autorizacao] ?? $item->autorizacao }}</span>
                    @endif

                    <div class="mt-1">{{ $situacoes[$item->situacao] ?? $item->situacao }}</div>

                    @if($item->autorizacao_em)
                    <div class="small mt-1">Decisão registrada por {{ $item->autorizacaoPor?->name }} em {{ $item->autorizacao_em->format('d/m/Y H:i') }}</div>
                    @endif
                </td>

                <td class="text-nowrap"><span id="previa-{{ $alvo }}">R$ {{ number_format($subtotalOrcado, 2, ',', '.') }}</span></td>
                <td class="text-nowrap">R$ {{ number_format($subtotalAutorizado ?? 0, 2, ',', '.') }}</td>

                @if($editandoItens)
                <td style="min-width: 240px;">
                    <div class="d-flex align-items-start flex-wrap gap-2">
                        @if($pendente && $item->autorizacao !== 'RECUSADO')
                        <form method="POST" action="{{ $rotaItem }}" data-acao-item data-alvo="{{ $alvo }}" data-confirmacao="Confirma que o cliente liberou este item para execução? A autorização será registrada e o estoque da peça será descontado.">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="acao" value="autorizar_iniciar">
                            <button type="submit" class="btn btn-sm btn-primary">Autorizar e iniciar</button>
                        </form>
                        @endif

                        @if($item->situacao === 'EM_EXECUCAO')
                        <form method="POST" action="{{ $rotaItem }}" data-acao-item data-alvo="{{ $alvo }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="acao" value="finalizar">
                            <button type="submit" class="btn btn-sm btn-success">Marcar executado</button>
                        </form>

                        @endif

                        @if($pendente || $item->situacao === 'EM_EXECUCAO')
                        <details @if($recuperarItem) open @endif>
                            <summary class="btn btn-sm btn-outline-secondary">Mais opções</summary>
                            <div class="d-flex align-items-start flex-wrap gap-2 mt-2">
                                @if($pendente)
                        @foreach([
                        'autorizar' => ['titulo' => 'Autorizar sem iniciar', 'valor' => 'AUTORIZADO', 'classe' => 'btn-outline-success'],
                        'recusar' => ['titulo' => 'Recusar', 'valor' => 'RECUSADO', 'classe' => 'btn-outline-danger'],
                        'aguardar' => ['titulo' => 'Aguardar decisão', 'valor' => 'AGUARDANDO', 'classe' => 'btn-outline-secondary'],
                        ] as $acao => $opcao)
                        @if($item->autorizacao !== $opcao['valor'])
                        <form method="POST" action="{{ $rotaItem }}" data-acao-item data-alvo="{{ $alvo }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="acao" value="{{ $acao }}">
                            <button type="submit" class="btn btn-sm {{ $opcao['classe'] }}">{{ $opcao['titulo'] }}</button>
                        </form>
                        @endif
                        @endforeach

                        <details @if($recuperarItem && old('acao')==='cancelar' ) open @endif>
                            <summary class="btn btn-sm btn-outline-danger">Cancelar item</summary>

                            <form method="POST" action="{{ $rotaItem }}" class="mt-2" data-acao-item data-alvo="{{ $alvo }}" data-confirmacao="Cancelar este item? Ele sairá dos totais e permanecerá no histórico.">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="acao" value="cancelar">
                                <input type="hidden" name="item_retorno" value="{{ $chave }}">

                                <label for="motivo-cancelar-{{ $chave }}" class="form-label">Motivo</label>
                                <textarea id="motivo-cancelar-{{ $chave }}" name="motivo_cancelamento" class="form-control mb-2" rows="2" maxlength="1000" required>{{ $recuperarItem ? old('motivo_cancelamento') : '' }}</textarea>
                                <button type="submit" class="btn btn-sm btn-danger">Confirmar cancelamento</button>
                            </form>
                        </details>
                                @endif
                                @if($item->situacao === 'EM_EXECUCAO')
                        <details @if($recuperarItem && old('acao')==='interromper' ) open @endif>
                            <summary class="btn btn-sm btn-outline-danger">Interromper</summary>

                            <form method="POST" action="{{ $rotaItem }}" class="mt-2" data-acao-item data-alvo="{{ $alvo }}" data-confirmacao="Registrar a interrupção com o valor combinado e a devolução informada? Confira os dados antes de confirmar.">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="acao" value="interromper">
                                <input type="hidden" name="item_retorno" value="{{ $chave }}">

                                <div class="mb-2">
                                    <label for="motivo-interromper-{{ $chave }}" class="form-label">Motivo da interrupção</label>
                                    <textarea id="motivo-interromper-{{ $chave }}" name="motivo_interrupcao" class="form-control" rows="2" maxlength="1000" required>{{ $recuperarItem ? old('motivo_interrupcao') : '' }}</textarea>
                                </div>

                                <div class="mb-2">
                                    <label for="valor-{{ $chave }}" class="form-label">Valor total combinado para o item (R$)</label>
                                    <input id="valor-{{ $chave }}" name="valor_cobrado" type="number" class="form-control" min="0" max="{{ number_format(min($subtotalOriginal, 99999999.99), 2, '.', '') }}" step="0.01" value="{{ $recuperarItem ? old('valor_cobrado') : number_format($subtotalOriginal, 2, '.', '') }}" required>
                                    <p class="form-text mb-0">Informe o valor total, não unitário. Pode ser zero.</p>
                                </div>

                                @if($peca)
                                <div class="mb-2">
                                    <label for="devolucao-{{ $chave }}" class="form-label">Quantidade que retorna ao estoque</label>
                                    <input id="devolucao-{{ $chave }}" name="quantidade_devolvida" type="number" class="form-control" min="0" max="{{ $item->estoque_baixado ? $item->quantidade : 0 }}" step="1" value="{{ $recuperarItem ? old('quantidade_devolvida', 0) : 0 }}">
                                    <p class="form-text mb-0">Deixe zero se não houver peças reaproveitáveis para devolver.</p>
                                </div>

                                <div class="form-check mb-2">
                                    <input id="confirmar-devolucao-{{ $chave }}" name="confirmar_devolucao" type="checkbox" value="1" class="form-check-input" @checked($recuperarItem && old('confirmar_devolucao'))>
                                    <label for="confirmar-devolucao-{{ $chave }}" class="form-check-label">Conferi que as unidades devolvidas podem ser reaproveitadas.</label>
                                </div>
                                @endif

                                <button type="submit" class="btn btn-sm btn-danger">Registrar interrupção</button>
                            </form>
                        </details>
                                @endif
                            </div>
                        </details>
                        @endif
                    </div>

                    @if(in_array($item->situacao, ['EXECUTADO', 'CANCELADO', 'INTERROMPIDO'], true))
                    <span class="small text-muted">Preservado no histórico</span>
                    @endif
                </td>
                @endif
            </tr>
            @endforeach
            @endforeach

            @if($pedido->itens->isEmpty() && $pedido->servicos->isEmpty())
            <tr>
                <td colspan="{{ $editandoItens ? 8 : 6 }}">Nenhum item registrado.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

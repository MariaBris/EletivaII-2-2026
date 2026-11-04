@extends('layout')

@section('title', 'Atualizar OS - Mazotto')

@section('content')
@php
$andamentos = [
'ABERTO' => 'Aberto',
'EM_EXECUCAO' => 'Em execução',
'CONCLUIDO' => 'Concluído',
'CANCELADO' => 'Cancelado',
];

$opcoesPorStatus = [
'ABERTO' => ['ABERTO', 'EM_EXECUCAO', 'CONCLUIDO', 'CANCELADO'],
'EM_EXECUCAO' => ['EM_EXECUCAO', 'CONCLUIDO', 'CANCELADO'],
'CONCLUIDO' => ['CONCLUIDO'],
'CANCELADO' => ['CANCELADO'],
];

$opcoes = $opcoesPorStatus[$pedido->status] ?? [$pedido->status];
$encerrada = in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true);
$fotos = $pedido->fotosEntrada->keyBy('posicao');

$grupos = [
'novos_produtos' => [
'titulo' => 'Adicionar peças utilizadas',
'campo' => 'produto_id',
'itens' => $produtos,
],
'novos_servicos' => [
'titulo' => 'Adicionar serviços e mão de obra',
'campo' => 'servico_id',
'itens' => $servicos,
],
];

$anteriores = [
'novos_produtos' => is_array(old('novos_produtos')) ? old('novos_produtos') : [],
'novos_servicos' => is_array(old('novos_servicos')) ? old('novos_servicos') : [],
];
@endphp

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Atualizar OS #{{ $pedido->id }}</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-editar-os" class="btn btn-primary">Salvar</button>
        <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-secondary">Cancelar</a>
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

@if($encerrada)
<div class="alert alert-info">Esta OS está encerrada. Somente o responsável e as observações podem ser atualizados.</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Cliente</dt>
            <dd class="col-sm-9">{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>

            <dt class="col-sm-3">Veículo</dt>
            <dd class="col-sm-9">{{ trim(($pedido->veiculo?->marca ?? '') . ' ' . ($pedido->veiculo?->modelo ?? '')) }} — {{ $pedido->veiculo?->placa }}</dd>

            <dt class="col-sm-3">Total registrado</dt>
            <dd class="col-sm-9">R$ {{ number_format($pedido->total, 2, ',', '.') }}</dd>
        </dl>
    </div>
</div>

<form id="formulario-editar-os" method="POST" action="{{ route('pedidos.update', $pedido->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Dados do atendimento</h2>

            <div class="mb-3">
                <label for="status" class="form-label">Andamento</label>
                <select id="status" name="status" class="form-select" required>
                    @foreach($opcoes as $status)
                    <option value="{{ $status }}" @selected(old('status', $pedido->status) === $status)>{{ $andamentos[$status] ?? $status }}</option>
                    @endforeach
                </select>
                <p class="form-text mb-0">Após concluir ou cancelar, a OS não poderá voltar para um andamento anterior.</p>
            </div>

            <div class="mb-3">
                <label for="responsavel" class="form-label">Responsável pelo serviço (opcional)</label>
                <input id="responsavel" name="responsavel" type="text" value="{{ old('responsavel', $pedido->responsavel) }}" class="form-control" maxlength="255">
            </div>

            <div>
                <label for="observacoes" class="form-label">Solicitação do cliente / observações (opcional)</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="4" maxlength="5000">{{ old('observacoes', $pedido->observacoes) }}</textarea>
            </div>
        </div>
    </div>

    <fieldset @disabled($encerrada)>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Condições de entrada</h2>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="quilometragem_entrada" class="form-label">Quilometragem de entrada (opcional)</label>
                        <input id="quilometragem_entrada" name="quilometragem_entrada" type="number" value="{{ old('quilometragem_entrada', $pedido->quilometragem_entrada) }}" class="form-control" min="0" max="4294967295" step="1">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="combustivel_entrada" class="form-label">Combustível na entrada (opcional)</label>
                        <select id="combustivel_entrada" name="combustivel_entrada" class="form-select">
                            <option value="">Não informado</option>
                            @foreach(App\Models\Pedido::COMBUSTIVEIS as $valor => $titulo)
                            <option value="{{ $valor }}" @selected(old('combustivel_entrada', $pedido->combustivel_entrada) === $valor)>{{ $titulo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="avarias_entrada" class="form-label">Avarias descritas na entrada (opcional)</label>
                        <textarea id="avarias_entrada" name="avarias_entrada" class="form-control" rows="3" maxlength="5000">{{ old('avarias_entrada', $pedido->avarias_entrada) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Fotos da entrada</h2>
                <p class="text-muted">Complete as posições que estiverem sem foto. As imagens já registradas são preservadas. Limite de 15 MB por imagem, em JPG, JPEG, PNG ou WEBP.</p>

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
                        <p class="form-text mb-0">Foto já registrada. Clique para ampliar.</p>
                        @else
                        <label for="foto-{{ $posicao }}" class="form-label">Adicionar foto: {{ $titulo }}</label>
                        <input id="foto-{{ $posicao }}" name="fotos_entrada[{{ $posicao }}]" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" data-foto-entrada>
                        <img id="previa-{{ $posicao }}" alt="Prévia: {{ $titulo }}" class="img-fluid rounded border mt-2" style="width: 100%; height: 210px; object-fit: contain;" hidden>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </fieldset>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Itens já registrados</h2>
            <p class="text-muted">Os itens e preços anteriores são preservados. As inclusões abaixo serão acrescentadas à OS.</p>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Item</th>
                            <th>Quantidade</th>
                            <th>Preço unitário</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($pedido->itens as $item)
                        <tr>
                            <td>Peça</td>
                            <td>{{ $item->produto?->nome ?? 'Peça não encontrada' }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                            <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach

                        @foreach($pedido->servicos as $item)
                        <tr>
                            <td>Serviço</td>
                            <td>{{ $item->servico?->nome ?? 'Serviço não encontrado' }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                            <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach

                        @if($pedido->itens->isEmpty() && $pedido->servicos->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhum item registrado.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                        <option value="{{ $item->id }}" data-nome="{{ $item->nome }}">{{ $item->nome }} — R$ {{ number_format($item->preco, 2, ',', '.') }}@if($tipo === 'novos_produtos') — Estoque: {{ $item->estoque }}@endif</option>
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
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nova inclusão</th>
                            <th>Quantidade</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>

                    <tbody data-itens>
                        <tr data-vazio>
                            <td colspan="3" class="text-center text-muted">Nenhuma nova inclusão.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="form-check">
                <input id="confirmar_itens" name="confirmar_itens" type="checkbox" value="1" class="form-check-input" @checked(old('confirmar_itens'))>
                <label for="confirmar_itens" class="form-check-label">Confirmo que as novas peças e os novos serviços foram autorizados pelo cliente.</label>
            </div>

            <p class="form-text mb-0">Ao salvar, o sistema confere os preços atuais, recalcula o total e desconta somente as novas peças do estoque. Não inclua propostas ainda sem autorização.</p>
        </div>
    </div>
    @endif
</form>

<template id="modelo-nova-inclusao">
    <tr>
        <td>
            <span data-nome></span>
            <input type="hidden" data-identificador>
        </td>
        <td>
            <label class="visually-hidden" data-rotulo>Quantidade</label>
            <input type="number" class="form-control" min="1" max="1000000" step="1" required data-quantidade style="min-width: 90px;">
        </td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remover>Remover</button></td>
    </tr>
</template>

<script id="novas-inclusoes-anteriores" type="application/json">
    @json($anteriores)
</script>

<script>
    (() => {
        const formulario = document.getElementById('formulario-editar-os');
        const modelo = document.getElementById('modelo-nova-inclusao');
        const aviso = document.getElementById('aviso-itens');
        const anteriores = JSON.parse(document.getElementById('novas-inclusoes-anteriores').textContent);
        const grupos = [];

        function informar(texto) {
            if (!aviso) return;

            aviso.textContent = texto;
            aviso.classList.remove('d-none');
            aviso.focus();
        }

        for (const bloco of document.querySelectorAll('[data-grupo]')) {
            const grupo = {
                tipo: bloco.dataset.grupo,
                campo: bloco.dataset.campo,
                selecao: bloco.querySelector('[data-selecao]'),
                quantidade: bloco.querySelector('[data-quantidade-nova]'),
                corpo: bloco.querySelector('[data-itens]'),
                vazio: bloco.querySelector('[data-vazio]'),
                itens: new Map(),
                indice: 0,
            };

            grupos.push(grupo);

            function adicionar(id, quantidade, restaurando = false) {
                const opcao = [...grupo.selecao.options].find(item => item.value === String(id) && item.value);

                if (!opcao) {
                    informar('Um item selecionado anteriormente não está mais disponível. Confira as novas inclusões.');
                    return false;
                }

                const numero = Number(quantidade);

                if (!restaurando && (!Number.isInteger(numero) || numero < 1 || numero > 1000000)) {
                    informar('Informe uma quantidade inteira entre 1 e 1.000.000.');
                    return false;
                }

                const existente = grupo.itens.get(opcao.value);

                if (existente) {
                    const soma = Number(existente.entrada.value) + numero;

                    if (!Number.isInteger(soma) || soma < 1 || soma > 1000000) {
                        informar('Confira a quantidade total desse item.');
                        return false;
                    }

                    existente.entrada.value = soma;
                    return true;
                }

                if (grupo.itens.size >= 100) {
                    informar('É permitido incluir até 100 tipos de peças e 100 tipos de serviços por atualização.');
                    return false;
                }

                const linha = modelo.content.firstElementChild.cloneNode(true);
                const indice = grupo.indice++;
                const identificador = linha.querySelector('[data-identificador]');
                const entrada = linha.querySelector('[data-quantidade]');
                const nome = opcao.dataset.nome;

                linha.querySelector('[data-nome]').textContent = nome;

                identificador.name = `${grupo.tipo}[${indice}][${grupo.campo}]`;
                identificador.value = opcao.value;

                entrada.id = `${grupo.tipo}-item-${indice}`;
                entrada.name = `${grupo.tipo}[${indice}][quantidade]`;
                entrada.value = quantidade;

                linha.querySelector('[data-rotulo]').htmlFor = entrada.id;
                linha.querySelector('[data-rotulo]').textContent = `Quantidade de ${nome}`;

                linha.querySelector('[data-remover]').addEventListener('click', () => {
                    grupo.itens.delete(opcao.value);
                    linha.remove();
                    grupo.vazio.hidden = grupo.itens.size > 0;
                });

                grupo.itens.set(opcao.value, {
                    entrada
                });
                grupo.corpo.append(linha);
                grupo.vazio.hidden = true;

                return true;
            }

            bloco.querySelector('[data-adicionar]').addEventListener('click', () => {
                if (!grupo.selecao.value) {
                    informar('Selecione uma peça ou um serviço antes de adicionar.');
                    return;
                }

                if (!grupo.quantidade.reportValidity()) return;
                if (!adicionar(grupo.selecao.value, grupo.quantidade.value)) return;

                grupo.selecao.value = '';
                grupo.quantidade.value = 1;
            });

            for (const item of Object.values(anteriores[grupo.tipo] ?? {})) {
                adicionar(item?.[grupo.campo], item?.quantidade ?? '', true);
            }
        }

        formulario.addEventListener('submit', evento => {
            if (grupos.some(grupo => grupo.selecao.value)) {
                evento.preventDefault();
                informar('Há um item selecionado que ainda não foi adicionado. Clique em Adicionar ou limpe a seleção.');
                return;
            }

            const temItens = grupos.some(grupo => grupo.itens.size > 0);
            const confirmacao = document.getElementById('confirmar_itens');

            if (temItens && !confirmacao?.checked) {
                evento.preventDefault();
                informar('Confirme que os novos itens foram autorizados pelo cliente.');
                return;
            }

            const temFotos = [...formulario.querySelectorAll('[data-foto-entrada]')]
                .some(campo => campo.files.length > 0);

            if (formulario.elements.namedItem('status').value === 'CANCELADO' && (temItens || temFotos)) {
                evento.preventDefault();
                informar('Para cancelar a OS, remova as novas inclusões e as fotos selecionadas, ou salve essas informações primeiro.');
            }
        });

        formulario.querySelectorAll('[data-foto-entrada]').forEach(campo => {
            const previa = document.getElementById('previa-' + campo.id.replace('foto-', ''));
            let endereco = null;

            campo.addEventListener('change', () => {
                if (endereco) {
                    URL.revokeObjectURL(endereco);
                    endereco = null;
                }

                previa.hidden = true;
                previa.removeAttribute('src');
                campo.setCustomValidity('');

                const arquivo = campo.files[0];

                if (!arquivo) return;

                if (arquivo.size > 15 * 1024 * 1024) {
                    campo.setCustomValidity('Cada foto deve ter no máximo 15 MB.');
                    campo.reportValidity();
                    return;
                }

                endereco = URL.createObjectURL(arquivo);
                previa.src = endereco;
                previa.hidden = false;
            });

            window.addEventListener('beforeunload', () => {
                if (endereco) URL.revokeObjectURL(endereco);
            });
        });
    })();
</script>
@endsection

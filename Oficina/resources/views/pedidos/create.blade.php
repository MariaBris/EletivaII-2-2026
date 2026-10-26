@extends('layout')

@section('title', 'Abrir ordem de serviço - Mazotto')

@section('content')
@php
$veiculoSelecionado = $veiculos->firstWhere('id', old('veiculo_id'));
$rotuloVeiculo = fn ($veiculo) => $veiculo->placa . ' — ' . ($veiculo->modelo ?? 'Modelo não informado') . ' — ' . ($veiculo->cliente?->nome ?? 'Sem cliente cadastrado');
$grupos = [
'produtos' => ['titulo' => 'Peças', 'botao' => 'Adicionar peça', 'itens' => $produtos],
'servicos' => ['titulo' => 'Serviços e mão de obra', 'botao' => 'Adicionar serviço', 'itens' => $servicos],
];
$itensAnteriores = [
'produtos' => is_array(old('produtos')) ? old('produtos') : [],
'servicos' => is_array(old('servicos')) ? old('servicos') : [],
];
@endphp

<h1 class="h3 mb-4">Abrir ordem de serviço</h1>

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($veiculos->isEmpty())
<div class="alert alert-warning">Cadastre um veículo antes de abrir uma ordem de serviço.</div>
@endif

@if($produtos->isEmpty() && $servicos->isEmpty())
<div class="alert alert-warning">É necessário ter uma peça disponível ou um serviço cadastrado para abrir a ordem de serviço.</div>
@endif

<div class="mb-4">
    <a href="{{ route('veiculos.create') }}" class="btn btn-outline-dark">Cadastrar veículo</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-os" method="POST" action="{{ route('pedidos.store') }}">
            @csrf

            <div class="mb-3">
                <label for="veiculo-busca" class="form-label">Pesquisar veículo</label>
                <input id="veiculo-busca" name="veiculo_busca" type="text" list="lista-veiculos" value="{{ old('veiculo_busca', $veiculoSelecionado ? $rotuloVeiculo($veiculoSelecionado) : '') }}" class="form-control" autocomplete="off" placeholder="Digite a placa, o modelo ou o nome do cliente" aria-describedby="veiculo-ajuda" required>
                <input id="veiculo_id" name="veiculo_id" type="hidden" value="{{ $veiculoSelecionado?->id }}">
                <datalist id="lista-veiculos">
                    @foreach($veiculos as $veiculo)
                    <option value="{{ $rotuloVeiculo($veiculo) }}" data-id="{{ $veiculo->id }}" data-placa="{{ $veiculo->placa }}"></option>
                    @endforeach
                </datalist>
                <p id="veiculo-ajuda" class="form-text mb-0">Digite para encontrar e escolha um veículo sugerido. A ordem de serviço utiliza o cliente vinculado a ele, se houver.</p>
            </div>

            <div class="mb-3">
                <label for="responsavel" class="form-label">Responsável pelo serviço (opcional)</label>
                <input id="responsavel" name="responsavel" type="text" value="{{ old('responsavel') }}" class="form-control" maxlength="255">
            </div>

            <div class="mb-4">
                <label for="observacoes" class="form-label">Solicitação do cliente / observações (opcional)</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="4" maxlength="5000">{{ old('observacoes') }}</textarea>
            </div>

            <div id="aviso-itens" class="alert alert-warning d-none" role="alert" tabindex="-1"></div>

            @foreach($grupos as $tipo => $grupo)
            <div class="mb-4" data-grupo="{{ $tipo }}">
                <h2 class="h5">{{ $grupo['titulo'] }}</h2>

                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-md-7">
                        <label for="{{ $tipo }}-selecao" class="form-label">Selecione um item</label>
                        <select id="{{ $tipo }}-selecao" class="form-select" data-selecao>
                            <option value="">Selecione...</option>
                            @foreach($grupo['itens'] as $item)
                            <option value="{{ $item->id }}" data-nome="{{ $item->nome }}" data-preco="{{ $item->preco }}" @if($tipo==='produtos' ) data-estoque="{{ $item->estoque }}" @endif>{{ $item->nome }} — R$ {{ number_format($item->preco, 2, ',', '.') }}@if($tipo === 'produtos') — Estoque: {{ $item->estoque }}@endif</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="{{ $tipo }}-quantidade" class="form-label">Quantidade</label>
                        <input id="{{ $tipo }}-quantidade" type="number" value="1" min="1" max="1000000" step="1" class="form-control" data-quantidade-nova disabled>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-outline-primary w-100" data-adicionar disabled>{{ $grupo['botao'] }}</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Preço unitário</th>
                                <th>Quantidade</th>
                                <th>Subtotal</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody data-itens>
                            <tr data-vazio>
                                <td colspan="5" class="text-center text-muted">Nenhum item adicionado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            <p class="h5 text-end">Total previsto: <span id="total-previsto" aria-live="polite">R$ 0,00</span></p>
            <p class="form-text">Ao salvar, o sistema confere os preços e o estoque e desconta as peças utilizadas.</p>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Voltar</a>
                <button type="submit" class="btn btn-primary" @disabled($veiculos->isEmpty() || ($produtos->isEmpty() && $servicos->isEmpty()))>Abrir ordem de serviço</button>
            </div>
        </form>
    </div>
</div>

<template id="modelo-item-os">
    <tr>
        <td data-nome></td>
        <td class="text-nowrap" data-preco></td>
        <td>
            <label class="visually-hidden" data-rotulo>Quantidade</label>
            <input type="hidden" data-identificador>
            <input type="number" class="form-control" min="1" step="1" style="min-width: 90px;" data-quantidade required>
        </td>
        <td class="text-nowrap" data-subtotal></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remover>Remover</button></td>
    </tr>
</template>

<script id="itens-anteriores-os" type="application/json">
    @json($itensAnteriores)
</script>
<script>
    (() => {
        const formulario = document.getElementById('formulario-os');
        if (!formulario) return;

        const aviso = document.getElementById('aviso-itens');
        const moeda = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
        const grupos = [];
        const modelo = document.getElementById('modelo-item-os');
        const busca = document.getElementById('veiculo-busca');
        const veiculoId = document.getElementById('veiculo_id');
        const veiculos = [...document.querySelectorAll('#lista-veiculos option')];
        const anteriores = JSON.parse(document.getElementById('itens-anteriores-os').textContent);
        const normalizar = texto => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();

        function informar(texto) {
            aviso.textContent = texto;
            aviso.classList.remove('d-none');
            aviso.focus();
        }

        function limparAviso() {
            aviso.textContent = '';
            aviso.classList.add('d-none');
        }

        function conferirVeiculo() {
            const texto = normalizar(busca.value);
            const encontrado = veiculos.find(opcao => normalizar(opcao.value) === texto || normalizar(opcao.dataset.placa) === texto);
            veiculoId.value = encontrado ? encontrado.dataset.id : '';
            busca.setCustomValidity(encontrado ? '' : 'Escolha um veículo cadastrado entre as sugestões.');
        }

        busca.addEventListener('input', conferirVeiculo);
        busca.addEventListener('change', conferirVeiculo);
        conferirVeiculo();

        function precoEmCentavos(texto) {
            const [inteiro, decimal = ''] = texto.split('.');
            return Number(inteiro) * 100 + Number(decimal.padEnd(2, '0'));
        }

        function limiteItem(grupo, opcao) {
            const preco = precoEmCentavos(opcao.dataset.preco);
            const limiteEstoque = grupo.tipo === 'produtos' ? Number(opcao.dataset.estoque) : 1000000;
            return Math.min(limiteEstoque, 1000000, preco > 0 ? Math.floor(9999999999 / preco) : 1000000);
        }

        function atualizarTotais() {
            let total = 0;
            for (const grupo of grupos) {
                grupo.vazio.hidden = grupo.itens.size > 0;
                for (const item of grupo.itens.values()) {
                    const quantidade = Number(item.quantidade.value);
                    const subtotal = Number.isInteger(quantidade) && quantidade > 0 && quantidade <= item.limite ? quantidade * item.preco : 0;
                    item.linha.querySelector('[data-subtotal]').textContent = moeda.format(subtotal / 100);
                    total += subtotal;
                }
            }
            document.getElementById('total-previsto').textContent = moeda.format(total / 100);
            return total;
        }

        function adicionar(grupo, opcao, quantidade, restaurando = false) {
            const existente = grupo.itens.get(opcao.value);
            const limite = limiteItem(grupo, opcao);
            const novaQuantidade = existente ? Number(existente.quantidade.value) + Number(quantidade) : Number(quantidade);

            if (!restaurando && (!Number.isInteger(novaQuantidade) || novaQuantidade < 1 || novaQuantidade > limite)) {
                informar(grupo.tipo === 'produtos' ? 'Confira a quantidade. Ela deve respeitar o estoque disponível.' : 'Informe uma quantidade válida para o serviço.');
                return false;
            }

            if (existente) {
                existente.quantidade.value = novaQuantidade;
                atualizarTotais();
                return true;
            }

            if (grupo.itens.size >= 100) {
                informar('É permitido adicionar até 100 tipos de peças e 100 tipos de serviços por ordem de serviço.');
                return false;
            }

            const linha = modelo.content.firstElementChild.cloneNode(true);
            const indice = grupo.proximoIndice++;
            const campo = grupo.tipo === 'produtos' ? 'produto_id' : 'servico_id';
            const identificador = linha.querySelector('[data-identificador]');
            const entrada = linha.querySelector('[data-quantidade]');
            const nome = opcao.dataset.nome;
            const preco = precoEmCentavos(opcao.dataset.preco);

            linha.querySelector('[data-nome]').textContent = nome;
            linha.querySelector('[data-preco]').textContent = moeda.format(preco / 100);
            identificador.name = `${grupo.tipo}[${indice}][${campo}]`;
            identificador.value = opcao.value;
            entrada.id = `${grupo.tipo}-item-${indice}`;
            entrada.name = `${grupo.tipo}[${indice}][quantidade]`;
            entrada.value = quantidade;
            entrada.max = limite;
            linha.querySelector('[data-rotulo]').htmlFor = entrada.id;
            linha.querySelector('[data-rotulo]').textContent = `Quantidade de ${nome}`;
            entrada.addEventListener('input', atualizarTotais);

            linha.querySelector('[data-remover]').addEventListener('click', () => {
                grupo.itens.delete(opcao.value);
                linha.remove();
                atualizarTotais();
            });

            grupo.itens.set(opcao.value, {
                linha,
                quantidade: entrada,
                preco,
                limite
            });
            grupo.corpo.append(linha);
            atualizarTotais();
            return true;
        }

        for (const bloco of document.querySelectorAll('[data-grupo]')) {
            const grupo = {
                tipo: bloco.dataset.grupo,
                selecao: bloco.querySelector('[data-selecao]'),
                quantidade: bloco.querySelector('[data-quantidade-nova]'),
                botao: bloco.querySelector('[data-adicionar]'),
                corpo: bloco.querySelector('[data-itens]'),
                vazio: bloco.querySelector('[data-vazio]'),
                itens: new Map(),
                proximoIndice: 0,
            };
            grupos.push(grupo);

            grupo.selecao.addEventListener('change', () => {
                const opcao = grupo.selecao.selectedOptions[0];
                const selecionado = Boolean(opcao?.value);
                grupo.quantidade.disabled = !selecionado;
                grupo.botao.disabled = !selecionado;
                grupo.quantidade.max = selecionado ? limiteItem(grupo, opcao) : 1000000;
            });

            grupo.botao.addEventListener('click', () => {
                limparAviso();
                const opcao = grupo.selecao.selectedOptions[0];
                if (!opcao?.value || !grupo.quantidade.reportValidity()) return;
                if (!adicionar(grupo, opcao, grupo.quantidade.value)) return;
                grupo.selecao.value = '';
                grupo.quantidade.value = 1;
                grupo.quantidade.disabled = true;
                grupo.botao.disabled = true;
            });
        }

        for (const grupo of grupos) {
            const campo = grupo.tipo === 'produtos' ? 'produto_id' : 'servico_id';
            for (const item of Object.values(anteriores[grupo.tipo] ?? {})) {
                const opcao = [...grupo.selecao.options].find(opcao => opcao.value && opcao.value === String(item?.[campo]));
                if (opcao) adicionar(grupo, opcao, item.quantidade ?? '', true);
                else informar('Um item do formulário anterior não está mais disponível. Confira a seleção antes de salvar.');
            }
        }

        formulario.addEventListener('submit', evento => {
            conferirVeiculo();
            if (!formulario.reportValidity()) {
                evento.preventDefault();
                return;
            }
            if (grupos.some(grupo => grupo.selecao.value)) {
                evento.preventDefault();
                informar('Há um item selecionado que ainda não foi adicionado. Clique em Adicionar peça ou Adicionar serviço.');
                return;
            }
            if (!grupos.some(grupo => grupo.itens.size)) {
                evento.preventDefault();
                informar('Adicione pelo menos uma peça ou um serviço para abrir a ordem de serviço.');
                return;
            }
            if (atualizarTotais() > 9999999999) {
                evento.preventDefault();
                informar('O total da ordem de serviço ultrapassa o limite permitido. Confira os itens e as quantidades.');
            }
        });
    })();
</script>
@endsection

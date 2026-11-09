@extends('layout')

@section('title', 'Consultar veículo - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/relatorios.css') }}?v=1">
@endpush

@section('content')
<div class="relatorio">
    <div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">{{ trim(($veiculo->marca ?? '') . ' ' . ($veiculo->modelo ?? '')) }} - {{ $veiculo->placa }}</h1>

        <div class="d-flex flex-wrap gap-2 nao-imprimir">
            <a href="{{ route('veiculos.index') }}" class="btn btn-outline-dark">Voltar</a>
            <a href="{{ route('pedidos.index', ['veiculo_id' => $veiculo->id]) }}" class="btn btn-outline-dark">Todas as OS</a>
            <a href="{{ route('pedidos.create', ['veiculo_id' => $veiculo->id]) }}" class="btn btn-primary">Abrir OS</a>
            <a href="{{ route('veiculos.edit', $veiculo->id) }}" class="btn btn-primary">Editar</a>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()">Imprimir histórico</button>

            <form method="POST" action="{{ route('veiculos.destroy', $veiculo->id) }}" class="m-0" onsubmit="return confirm('Excluir este veículo?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Excluir</button>
            </form>
        </div>
    </div>

    @if(session('mensagem'))
    <div class="alert alert-success nao-imprimir" role="status">{{ session('mensagem') }}</div>
    @endif

    @if(session('erro'))
    <div class="alert alert-danger nao-imprimir" role="alert">{{ session('erro') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger nao-imprimir" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $mensagem)
            <li>{{ $mensagem }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <dl class="row g-3 mb-0">
                <div class="col-12 col-md-6">
                    <dt class="d-inline">Placa:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->placa }}</dd>
                </div>

                <div class="col-12 col-md-6">
                    <dt class="d-inline">Marca:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->marca ?? '-' }}</dd>
                </div>

                <div class="col-12 col-md-6">
                    <dt class="d-inline">Modelo:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->modelo ?? '-' }}</dd>
                </div>

                <div class="col-12 col-md-6">
                    <dt class="d-inline">Ano:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->ano ?? '-' }}</dd>
                </div>

                <div class="col-12 col-md-6">
                    <dt class="d-inline">Proprietário:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>
                </div>

                <div class="col-12 col-md-6">
                    <dt class="d-inline">Telefone:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->cliente?->telefone ?? 'Não informado' }}</dd>
                </div>

                <div class="col-12 col-md-6">
                    <dt class="d-inline">Quilometragem informada no cadastro:</dt>
                    <dd class="d-inline mb-0">{{ $veiculo->quilometragem_informada === null ? 'Não informada' : number_format($veiculo->quilometragem_informada, 0, ',', '.') . ' km' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <section id="historico-os" class="mb-4">
        <h2 class="h5">Histórico de atendimentos na Mazotto</h2>
        <p class="text-muted">OS concluídas deste veículo. São exibidos somente os itens autorizados e executados, com os preços registrados no atendimento.</p>

        @forelse($historicoPedidos as $pedido)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="h6 mb-0">OS #{{ $pedido->id }}</h3>
                    <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-dark nao-imprimir">Consultar OS #{{ $pedido->id }}</a>
                </div>

                <p class="mb-1">Conclusão: {{ $pedido->concluido_em?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') ?? 'Não registrada' }} | Quilometragem: {{ $pedido->quilometragem_entrada === null ? 'Não informada' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</p>
                <p>Cliente da OS: {{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</p>

                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Item executado</th>
                                <th>Quantidade</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($pedido->servicos as $item)
                            <tr>
                                <td>Serviço</td>
                                <td>{{ $item->servico?->nome ?? 'Serviço não encontrado' }}</td>
                                <td>{{ $item->quantidade }}</td>
                                <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach

                            @foreach($pedido->itens as $item)
                            <tr>
                                <td>Peça</td>
                                <td>{{ $item->produto?->nome ?? 'Peça não encontrada' }}</td>
                                <td>{{ $item->quantidade }}</td>
                                <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach

                            @if($pedido->servicos->isEmpty() && $pedido->itens->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted">Nenhum item executado registrado.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <p class="text-end fw-bold mt-3 mb-0">Total da OS: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
            </div>
        </div>
        @empty
        <p class="text-muted">Nenhuma OS concluída encontrada para este veículo.</p>
        @endforelse

        <p class="small text-muted">O total da OS pode incluir cobranças acordadas por interrupção. Consulte a OS para os detalhes. Página {{ $historicoPedidos->currentPage() }} de {{ $historicoPedidos->lastPage() }}; a impressão inclui os atendimentos desta página.</p>

        @if($historicoPedidos->hasPages())
        <div class="nao-imprimir">
            {{ $historicoPedidos->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </section>

    <div class="card border-0 shadow-sm nao-imprimir">
        <div class="card-body p-4">
            <h2 class="h5 mb-2">Manutenções informadas pelo cliente</h2>
            <p class="text-muted">Informações do cadastro, que podem incluir serviços realizados em outra oficina.</p>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Manutenção</th>
                            <th>Data informada</th>
                            <th>Quilometragem informada</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Última troca de óleo</td>
                            <td>{{ $veiculo->oleo_data_informada?->format('d/m/Y') ?? 'Não informada' }}</td>
                            <td>{{ $veiculo->oleo_km_informado === null ? 'Não informada' : number_format($veiculo->oleo_km_informado, 0, ',', '.') . ' km' }}</td>
                        </tr>

                        <tr>
                            <td>Última revisão</td>
                            <td>{{ $veiculo->revisao_data_informada?->format('d/m/Y') ?? 'Não informada' }}</td>
                            <td>{{ $veiculo->revisao_km_informado === null ? 'Não informada' : number_format($veiculo->revisao_km_informado, 0, ',', '.') . ' km' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('relatorios.base')

@section('titulo-relatorio', 'OS concluídas / faturamento')

@section('conteudo-relatorio')
@include('relatorios.periodo')
<p>Valores registrados nas OS concluídas, pela data de conclusão. Este relatório não comprova recebimentos ou pagamentos.</p>
<div class="row g-3 mb-4">
    @foreach([['Total das OS concluídas', 'R$ ' . number_format($totalFaturado, 2, ',', '.')], ['Quantidade de OS', $quantidadeOs], ['Ticket médio', 'R$ ' . number_format($ticketMedio, 2, ',', '.')]] as [$titulo, $valor])
    <div class="col-12 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">{{ $titulo }}</p><p class="h4 mb-0">{{ $valor }}</p></div></div></div>
    @endforeach
</div>
<div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
        <thead><tr><th>OS</th><th>Conclusão</th><th>Cliente</th><th>Veículo</th><th class="text-end">Total</th></tr></thead>
        <tbody>
            @forelse($pedidos as $pedido)
            <tr>
                <td><a href="{{ route('pedidos.show', $pedido->id) }}">#{{ $pedido->id }}</a></td>
                <td>{{ $pedido->concluido_em?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                <td>{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</td>
                <td>{{ $pedido->veiculo?->placa ?? 'Veículo não encontrado' }}</td>
                <td class="text-end text-nowrap">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma OS concluída encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="nao-imprimir">{{ $pedidos->links('pagination::bootstrap-5') }}</div>
@endsection

@extends('relatorios.base')

@section('titulo-relatorio', 'Serviços executados')

@section('conteudo-relatorio')
@include('relatorios.periodo')

<p>Quantidades de serviços autorizados e executados em OS concluídas. Itens recusados, cancelados ou interrompidos não entram na contagem.</p>

<div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
        <thead>
            <tr>
                <th>Serviço</th>
                <th class="text-end">Quantidade executada</th>
            </tr>
        </thead>

        <tbody>
            @forelse($servicos as $servico)
            <tr>
                <td>{{ $servico->nome }}</td>
                <td class="text-end">{{ (int) $servico->quantidade_executada }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center text-muted py-4">Nenhum serviço cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="nao-imprimir">
    {{ $servicos->links('pagination::bootstrap-5') }}
</div>
@endsection

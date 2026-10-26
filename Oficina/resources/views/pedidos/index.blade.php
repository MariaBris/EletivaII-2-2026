@extends('layout')

@section('title', 'Ordens de serviço - Mazotto')

@section('content')
@php
$andamentos = [
'ABERTO' => 'Aberto',
'EM_EXECUCAO' => 'Em execução',
'PAGO' => 'Pago',
'ENVIADO' => 'Enviado',
'CONCLUIDO' => 'Concluído',
'CANCELADO' => 'Cancelado',
];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Ordens de serviço</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao painel</a>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nova ordem de serviço</a>
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

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <thead>
            <tr>
                <th>ordem de serviço</th>
                <th>Abertura</th>
                <th>Cliente</th>
                <th>Veículo</th>
                <th>Andamento</th>
                <th>Total</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pedidos as $pedido)
            <tr>
                <td>#{{ $pedido->id }}</td>
                <td>{{ $pedido->created_at?->format('d/m/Y H:i') ?? 'Não informada' }}</td>
                <td>{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</td>
                <td>{{ $pedido->veiculo?->placa ?? 'Não encontrado' }}</td>
                <td>{{ $andamentos[$pedido->status] ?? $pedido->status }}</td>
                <td class="text-nowrap">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>

                <td>
                    <div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-primary">Consultar</a>
                        <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-sm btn-primary">Atualizar</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">Nenhuma ordem de serviço cadastrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

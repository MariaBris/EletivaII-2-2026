@extends('layout')

@section('title', 'Ordem de serviço #' . $pedido->id . ' - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/impressao_ordemS.css') }}?v=3" media="print">
@endpush

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

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Mazotto — Ordem de serviço #{{ $pedido->id }}</h1>

    <div class="d-flex flex-wrap gap-2 nao-imprimir">
        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Voltar</a>
        <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-primary">Atualizar</a>
        <button type="button" class="btn btn-outline-dark" onclick="window.print()">Imprimir</button>

        @if(in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true))
        <form method="POST" action="{{ route('pedidos.destroy', $pedido->id) }}" class="m-0" onsubmit="return confirm('Cancelar esta ordem de serviço? As peças com baixa registrada serão devolvidas ao estoque.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Cancelar ordem de serviço</button>
        </form>
        @endif
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

@if($pedido->estoque_baixado === null && $pedido->itens->isNotEmpty())
<div class="alert alert-warning nao-imprimir">Esta ordem de serviço antiga possui peças cuja baixa de estoque ainda precisa ser conferida antes do cancelamento.</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Abertura</dt>
            <dd class="col-sm-9"><span @class(['nao-imprimir'=> blank($pedido->created_at)])>{{ $pedido->created_at?->format('d/m/Y H:i') ?? 'Não informada' }}</span></dd>

            <dt class="col-sm-3">Andamento</dt>
            <dd class="col-sm-9">{{ $andamentos[$pedido->status] ?? $pedido->status }}</dd>

            <dt class="col-sm-3">Cliente</dt>
            <dd class="col-sm-9"><span @class(['nao-imprimir'=> blank($pedido->cliente?->nome)])>{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</span></dd>

            <dt class="col-sm-3">Telefone</dt>
            <dd class="col-sm-9"><span @class(['nao-imprimir'=> blank($pedido->cliente?->telefone)])>{{ $pedido->cliente?->telefone ?? 'Não informado' }}</span></dd>

            <dt class="col-sm-3">Veículo</dt>
            <dd class="col-sm-9"><span @class(['nao-imprimir'=> !$pedido->veiculo])>{{ $pedido->veiculo?->placa ?? 'Não encontrado' }} — {{ $pedido->veiculo?->marca }} {{ $pedido->veiculo?->modelo }}</span></dd>

            <dt class="col-sm-3">Aberta por</dt>
            <dd class="col-sm-9"><span @class(['nao-imprimir'=> blank($pedido->operador?->name)])>{{ $pedido->operador?->name ?? 'Não informado' }}</span></dd>

            <dt class="col-sm-3">Responsável pelo serviço</dt>
            <dd class="col-sm-9"><span @class(['nao-imprimir'=> blank($pedido->responsavel)])>{{ $pedido->responsavel ?? 'Não informado' }}</span></dd>

            <dt class="col-sm-3">Observações</dt>
            <dd class="col-sm-9" style="white-space: pre-wrap;"><span @class(['nao-imprimir'=> blank($pedido->observacoes)])>{{ $pedido->observacoes ?? 'Não informadas' }}</span></dd>
        </dl>
    </div>
</div>

<h2 class="h5">Peças utilizadas</h2>

<div class="table-responsive mb-4">
    <table class="table table-striped align-middle bg-white">
        <thead>
            <tr>
                <th>Peça</th>
                <th>Quantidade</th>
                <th>Preço unitário</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pedido->itens as $item)
            <tr>
                <td>{{ $item->produto?->nome ?? 'Peça não encontrada' }}</td>
                <td>{{ $item->quantidade }}</td>
                <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Nenhuma peça incluída.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="h5">Serviços e mão de obra</h2>

<div class="table-responsive mb-4">
    <table class="table table-striped align-middle bg-white">
        <thead>
            <tr>
                <th>Serviço</th>
                <th>Quantidade</th>
                <th>Preço unitário</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pedido->servicos as $item)
            <tr>
                <td>{{ $item->servico?->nome ?? 'Serviço não encontrado' }}</td>
                <td>{{ $item->quantidade }}</td>
                <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Nenhum serviço incluído.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4 text-end">
        <p class="h4 mb-0">Total da ordem de serviço: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
    </div>
</div>
@endsection

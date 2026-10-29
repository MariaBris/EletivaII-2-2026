@extends('layout')

@section('title', 'Atualizar ordem de serviço - Mazotto')

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
@endphp

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Atualizar ordem de serviço #{{ $pedido->id }}</h1>

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

@if(in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true))
<div class="alert alert-info">O andamento desta ordem de serviço está encerrado. Você pode atualizar o responsável e as observações.</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Cliente</dt>
            <dd class="col-sm-9">{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>

            <dt class="col-sm-3">Veículo</dt>
            <dd class="col-sm-9">{{ $pedido->veiculo?->placa ?? 'Não encontrado' }} — {{ $pedido->veiculo?->modelo }}</dd>

            <dt class="col-sm-3">Total</dt>
            <dd class="col-sm-9">R$ {{ number_format($pedido->total, 2, ',', '.') }}</dd>
        </dl>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-editar-os" method="POST" action="{{ route('pedidos.update', $pedido->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="status" class="form-label">Andamento</label>
                <select id="status" name="status" class="form-select" required>
                    @foreach($opcoes as $status)
                    <option value="{{ $status }}" @selected(old('status', $pedido->status) === $status)>{{ $andamentos[$status] ?? $status }}</option>
                    @endforeach
                </select>
                <p class="form-text mb-0">Após concluir ou cancelar, a ordem de serviço não poderá voltar para um andamento anterior.</p>
            </div>

            <div class="mb-3">
                <label for="responsavel" class="form-label">Responsável pelo serviço (opcional)</label>
                <input id="responsavel" name="responsavel" type="text" value="{{ old('responsavel', $pedido->responsavel) }}" class="form-control" maxlength="255">
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Solicitação do cliente / observações (opcional)</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="4" maxlength="5000">{{ old('observacoes', $pedido->observacoes) }}</textarea>
            </div>

            <p class="text-muted mb-0">As peças, os serviços e os preços registrados na abertura são preservados.</p>
        </form>
    </div>
</div>
@endsection

@extends('layout')

@section('title', 'Consultar agendamento - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Agendamento #{{ $agendamento->id }}</h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('agendamentos.index', ['data' => $agendamento->data_hora->format('Y-m-d')]) }}" class="btn btn-outline-dark">Voltar</a>
        @if($agendamento->status === 'AGENDADO')
        <a href="{{ route('agendamentos.edit', $agendamento->id) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('pedidos.create', ['agendamento_id' => $agendamento->id]) }}" class="btn btn-success">Abrir OS</a>
        <form method="POST" action="{{ route('agendamentos.destroy', $agendamento->id) }}" class="m-0" onsubmit="return confirm('Cancelar este agendamento? O histórico será preservado.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Cancelar agendamento</button>
        </form>
        @elseif($agendamento->pedido_id !== null)
        <a href="{{ route('pedidos.show', $agendamento->pedido_id) }}" class="btn btn-primary">Consultar OS #{{ $agendamento->pedido_id }}</a>
        @endif
    </div>
</div>
@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif
@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger" role="alert"><ul class="mb-0">
    @foreach($errors->all() as $mensagem)
    <li>{{ $mensagem }}</li>
    @endforeach
</ul></div>
@endif

<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <dl class="row mb-0">
        <dt class="col-sm-3">Data e horário</dt>
        <dd class="col-sm-9">{{ $agendamento->data_hora->format('d/m/Y H:i') }}</dd>
        <dt class="col-sm-3">Situação</dt>
        <dd class="col-sm-9">{{ App\Models\Agendamento::SITUACOES[$agendamento->status] ?? $agendamento->status }}</dd>
        <dt class="col-sm-3">Veículo</dt>
        <dd class="col-sm-9">{{ trim(($agendamento->veiculo?->marca ?? '') . ' ' . ($agendamento->veiculo?->modelo ?? '')) }} — {{ $agendamento->veiculo?->placa }}</dd>
        <dt class="col-sm-3">Cliente</dt>
        <dd class="col-sm-9">{{ $agendamento->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>
        <dt class="col-sm-3">Telefone</dt>
        <dd class="col-sm-9">{{ $agendamento->cliente?->telefone ?? 'Não informado' }}</dd>
        <dt class="col-sm-3">Motivo / relato</dt>
        <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $agendamento->motivo }}</dd>
        <dt class="col-sm-3">Cadastrado por</dt>
        <dd class="col-sm-9">{{ $agendamento->operador?->name ?? 'Não informado' }}</dd>
    </dl>
    @if($agendamento->status === 'AGENDADO')
    <p class="form-text mb-0">Ao salvar a OS vinculada, o agendamento será marcado como atendido. Apenas abrir a tela da OS não altera esta situação.</p>
    @endif
</div></div>
@endsection

@extends('layout')

@section('title', 'Consultar veículo - Mazotto')

@section('content')
<h1 class="h3 mb-4">Veículo {{ $veiculo->placa }}</h1>

{{-- Mensagens de sucesso, bloqueio e validação. --}}
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

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Placa</dt>
            <dd class="col-sm-9">{{ $veiculo->placa }}</dd>

            <dt class="col-sm-3">Marca</dt>
            <dd class="col-sm-9">{{ $veiculo->marca ?? '—' }}</dd>

            <dt class="col-sm-3">Modelo</dt>
            <dd class="col-sm-9">{{ $veiculo->modelo ?? '—' }}</dd>

            <dt class="col-sm-3">Ano</dt>
            <dd class="col-sm-9">{{ $veiculo->ano ?? '—' }}</dd>

            <dt class="col-sm-3">Proprietário</dt>
            <dd class="col-sm-9">{{ $veiculo->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>

            <dt class="col-sm-3">Telefone</dt>
            <dd class="col-sm-9">{{ $veiculo->cliente?->telefone ?? 'Não informado' }}</dd>
        </dl>

        <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">
            <a href="{{ route('veiculos.index') }}" class="btn btn-outline-dark">Voltar</a>
            <a href="{{ route('veiculos.edit', $veiculo->id) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@endsection

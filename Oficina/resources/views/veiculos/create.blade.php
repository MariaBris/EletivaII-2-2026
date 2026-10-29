@extends('layout')

@section('title', 'Cadastrar veículo - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Cadastrar veículo</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-veiculo" class="btn btn-primary">Salvar</button>
        <a href="{{ route('veiculos.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-veiculo" method="POST" action="{{ route('veiculos.store') }}">
            @csrf

            @include('veiculos.form', ['veiculo' => null])
        </form>
    </div>
</div>
@endsection

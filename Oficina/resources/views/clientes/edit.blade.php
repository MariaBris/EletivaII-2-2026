@extends('layout')

@section('title', 'Editar cliente - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Editar cliente</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-cliente" class="btn btn-primary">Salvar</button>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-cliente" method="POST" action="{{ route('clientes.update', $cliente->id) }}">
            @csrf
            @method('PUT')

            @include('clientes.form')
        </form>
    </div>
</div>
@endsection

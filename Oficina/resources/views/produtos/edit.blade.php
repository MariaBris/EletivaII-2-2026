@extends('layout')

@section('title', 'Editar Produto - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Editar Produto</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-produto" class="btn btn-primary">Salvar</button>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success alert-dismissible fade show" role="status">
    {{ session('mensagem') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-produto" method="POST" action="{{ route('produtos.update', $produto->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('produtos.form')
        </form>
    </div>
</div>
@endsection

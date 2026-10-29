@extends('layout')

@section('title', 'Nova Categoria - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Nova Categoria</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-categoria" class="btn btn-primary">Salvar</button>
        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-categoria" method="POST" action="{{ route('categorias.store') }}">
            @csrf

            @include('categorias.form', ['categoria' => null])
        </form>
    </div>
</div>
@endsection

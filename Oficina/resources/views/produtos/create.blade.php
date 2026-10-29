@extends('layout')

@section('title', 'Novo Produto - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Novo Produto</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-produto" class="btn btn-primary">Salvar</button>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-produto" method="POST" action="{{ route('produtos.store') }}" enctype="multipart/form-data">
            @csrf

            @include('produtos.form', ['produto' => null])
        </form>
    </div>
</div>
@endsection

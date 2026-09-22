@extends('layout')

@section('title', 'Editar Categoria')

@section('content')
@if(session('mensagem'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('mensagem') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="mb-4">
    <h1>Editar Categoria</h1>
</div>

<form method="post" action="/categorias/{{ $categoria->id }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="nome" class="form-label">Nome da categoria</label>
        <input type="text" id="nome" name="nome" class="form-control" required value="{{ $categoria->nome }}">
    </div>
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição</label>
        <input type="text" id="descricao" name="descricao" class="form-control" required value="{{ $categoria->descricao }}">
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="/categorias" class="btn btn-outline-dark ms-2">Cancelar</a>
    </div>
</form>
@endsection

@extends('layout')

@section('title', 'Nova Categoria')

@section('content')
<div class="mb-4">
    <h1>Nova Categoria</h1>
</div>

<form method="post" action="/categorias">
    @csrf
    <div class="mb-3">
        <label for="nome" class="form-label">Nome da categoria</label>
        <input type="text" id="nome" name="nome" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição</label>
        <input type="text" id="descricao" name="descricao" class="form-control" required>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/categorias" class="btn btn-secondary ms-2">Cancelar</a>
    </div>
</form>
@endsection

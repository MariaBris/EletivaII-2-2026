@extends('layout')

@section('title', 'Consultar categoria - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Dados da categoria</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('categorias.index') }}" class="btn btn-outline-dark">Voltar</a>
        <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-primary">Editar</a>

        <form method="POST" action="{{ route('categorias.destroy', $categoria->id) }}" class="m-0" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da categoria</label>
            <input type="text" id="nome" class="form-control" disabled value="{{ $categoria->nome }}">
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea id="descricao" class="form-control" rows="4" disabled>{{ $categoria->descricao }}</textarea>
        </div>
    </div>
</div>
@endsection

@extends('layout')

@section('title', 'Consultar Categoria')

@section('content')
    <div class="mb-4">
        <h1>Dados da Categoria</h1>
    </div>

    <form method="post" action="/categorias/{{ $categoria->id }}">
        @csrf
        @method('DELETE')
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da categoria</label>
            <input type="text" id="nome" name="nome" class="form-control" disabled value="{{ $categoria->nome }}">
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <input type="text" id="descricao" name="descricao" class="form-control" disabled value="{{ $categoria->descricao }}">
        </div>

        <div class="mt-4">
            <!-- Botão com o pop-up de confirmação embutido -->
            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem a certeza que deseja excluir esta categoria?')">Excluir</button>
            <a href="/categorias" class="btn btn-secondary ms-2">Voltar</a>
        </div>
    </form>
@endsection

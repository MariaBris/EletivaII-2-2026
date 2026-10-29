@extends('layout')

@section('title', 'Categorias - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Categorias</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nova categoria</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th class="col-acoes text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($categorias as $c)
            <tr>
                <td>{{ $c->nome }}</td>
                <td>{{ $c->descricao }}</td>
                <td class="col-acoes">
                    <div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('categorias.show', $c->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar categoria"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('categorias.edit', $c->id) }}" class="btn btn-sm btn-primary" title="Editar" aria-label="Editar categoria"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>

                        <form method="POST" action="{{ route('categorias.destroy', $c->id) }}" class="m-0" onsubmit="return confirm('Deseja excluir esta categoria?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" aria-label="Excluir categoria"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

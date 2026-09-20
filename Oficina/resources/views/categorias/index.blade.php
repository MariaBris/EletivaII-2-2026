@extends('layout')

@section('title', 'Categorias - Mazotto')

@section('content')
<div class="container py-3">
    @if(session('mensagem'))
    <div class="alert alert-success">{{ session('mensagem') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Categorias</h2>
        <a href="/categorias/create" class="btn btn-primary">Novo Registo</a>
    </div>

    <table class="table table-hover table-striped shadow-sm bg-white rounded">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorias as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->nome }}</td>
                <td>{{ $c->descricao }}</td>
                <td class="d-flex gap-2">
                    <a href="/categorias/{{ $c->id }}/edit" class="btn btn-sm btn-warning">Editar</a>
                    <a href="/categorias/{{ $c->id }}" class="btn btn-sm btn-info">Consultar</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

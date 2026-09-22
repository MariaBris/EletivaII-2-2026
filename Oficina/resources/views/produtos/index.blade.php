@extends('layout')

@section('title', 'Lista de Produtos - Mazotto')

@section('content')
<div class="container py-3">
    @if(session('mensagem'))
    <div class="alert alert-success">{{ session('mensagem') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Produtos</h2>
        <a href="/produtos/create" class="btn btn-primary">Novo Registo</a>
    </div>

    <table class="table table-hover table-striped shadow-sm bg-white rounded">
        <thead>
            <tr>
                <th style="width: 70px;">Foto</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th class="col-acoes">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $p)
            <tr>
                <td>
                    @if($p->imagem)
                    <!-- Adicionada a classe img-tabela aqui para forçar o tamanho pequeno e quadrado -->
                    <img src="{{ Str::startsWith($p->imagem, 'http') ? $p->imagem : asset($p->imagem) }}" alt="Foto" class="img-tabela">
                    @else
                    <span class="text-muted small">Sem foto</span>
                    @endif
                </td>
                <td>{{ $p->nome }}</td>
                <td>{{ $p->categoria->nome ?? 'Sem Categoria' }}</td>
                <td>R$ {{ number_format($p->preco, 2, ',', '.') }}</td>
                <td>{{ $p->estoque }}</td>
                <td class="col-acoes">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/produtos/{{ $p->id }}/edit" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></a>
                        <a href="/produtos/{{ $p->id }}" class="btn btn-sm btn-outline-primary" title="Consultar"><i class="bi bi-eye"></i></a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

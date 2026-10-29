@extends('layout')

@section('title', 'Produtos - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Produtos</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('produtos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo produto</a>
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
                <th style="width: 70px;">Foto</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th class="col-acoes text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($produtos as $p)
            <tr>
                <td>
                    @if($p->imagem)
                    <!-- Adicionada a classe img-tabela aqui para forçar o tamanho pequeno e quadrado -->
                    <img src="{{ Str::startsWith($p->imagem, 'http') ? $p->imagem : asset($p->imagem) }}" alt="Foto de {{ $p->nome }}" class="img-tabela">
                    @else
                    <span class="text-muted small">Sem foto</span>
                    @endif
                </td>
                <td>{{ $p->nome }}</td>
                <td>{{ $p->categoria?->nome ?? 'Sem categoria' }}</td>
                <td class="text-nowrap">R$ {{ number_format($p->preco, 2, ',', '.') }}</td>
                <td>{{ $p->estoque }}</td>
                <td class="col-acoes">
                    <div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('produtos.show', $p->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar produto"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('produtos.edit', $p->id) }}" class="btn btn-sm btn-primary" title="Editar" aria-label="Editar produto"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>

                        <form method="POST" action="{{ route('produtos.destroy', $p->id) }}" class="m-0" onsubmit="return confirm('Deseja excluir este produto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" aria-label="Excluir produto"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">Nenhum produto cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

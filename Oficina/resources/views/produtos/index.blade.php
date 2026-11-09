@extends('layout')

@section('title', 'Produtos e estoque - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/relatorios.css') }}?v=1">
@endpush

@section('content')
@php
$administrador = auth()->user()->role === 'ADM';
@endphp

<div class="relatorio">
    <div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">Produtos e estoque</h1>

        <div class="d-flex flex-wrap gap-2 nao-imprimir">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()">Imprimir página</button>

            @if($administrador)
            <a href="{{ route('produtos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo produto</a>
            @endif
        </div>
    </div>

    @if(session('mensagem'))
    <div class="alert alert-success nao-imprimir" role="status">{{ session('mensagem') }}</div>
    @endif

    @if(session('erro'))
    <div class="alert alert-danger nao-imprimir" role="alert">{{ session('erro') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4 nao-imprimir">
        <div class="card-body p-4">
            <form id="formulario-pesquisa" method="GET" action="{{ route('produtos.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="busca" class="form-label">Pesquisar produto</label>
                        <input id="busca" name="busca" type="search" class="form-control" value="{{ old('busca', request('busca')) }}" maxlength="255" placeholder="Nome, descrição ou categoria">
                    </div>

                    <div class="col-12 col-md">
                        <label for="estoque_baixo" class="form-label">Estoque</label>
                        <select id="estoque_baixo" name="estoque_baixo" class="form-select">
                            <option value="" @selected(!old('estoque_baixo', request('estoque_baixo')))>Todos os produtos</option>
                            <option value="1" @selected((string) old('estoque_baixo', request('estoque_baixo'))==='1' )>No mínimo ou abaixo</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                        <a href="{{ route('produtos.index') }}" class="btn btn-secondary" data-limpar-pesquisa>Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="resultados-pesquisa" aria-busy="false">
        @if($errors->any())
        <div class="alert alert-danger nao-imprimir" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $mensagem)
                <li>{{ $mensagem }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <p class="mb-1" role="status">{{ $produtos->total() }} produto(s) encontrado(s).</p>
                <p class="mb-1">Valor físico a preço de venda: <strong>R$ {{ number_format($valorEstoque, 2, ',', '.') }}</strong></p>
                <p class="small text-muted mb-0">Total de todos os produtos do filtro, calculado pelo estoque físico e preço atual de venda. Não representa custo nem lucro.</p>
                <p class="small text-muted mb-0">Filtro: {{ request()->boolean('estoque_baixo') ? 'Estoque físico no mínimo ou abaixo' : 'Todos os produtos' }}.@if(request()->filled('busca')) Pesquisa: {{ request('busca') }}.@endif</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle bg-white">
                <thead>
                    <tr>
                        <th class="nao-imprimir" style="width: 70px;">Foto</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Físico</th>
                        <th>Reservado</th>
                        <th>Disponível</th>
                        <th>Mínimo</th>
                        <th>Situação</th>

                        @if($administrador)
                        <th class="col-acoes text-end nao-imprimir">Ações</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($produtos as $p)
                    <tr>
                        <td class="nao-imprimir">
                            @if($p->imagem)
                            <img src="{{ Str::startsWith($p->imagem, 'http') ? $p->imagem : asset($p->imagem) }}" alt="Foto de {{ $p->nome }}" class="img-tabela">
                            @else
                            <span class="text-muted small">Sem foto</span>
                            @endif
                        </td>

                        <td>{{ $p->nome }}</td>
                        <td>{{ $p->categoria?->nome ?? 'Sem categoria' }}</td>
                        <td class="text-nowrap">R$ {{ number_format($p->preco, 2, ',', '.') }}</td>
                        <td>{{ $p->estoque }}</td>
                        <td>{{ $p->estoque_reservado }}</td>
                        <td>{{ $p->estoque_disponivel }}</td>
                        <td>{{ $p->estoque_minimo }}</td>
                        <td>{{ $p->estoque <= 0 ? 'Sem estoque físico' : ($p->estoque <= $p->estoque_minimo ? 'Repor estoque' : ($p->estoque_disponivel <= 0 ? 'Todo reservado' : 'Disponível')) }}</td>

                        @if($administrador)
                        <td class="col-acoes nao-imprimir">
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
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $administrador ? 10 : 9 }}" class="text-center text-muted py-4">Nenhum produto encontrado para esta consulta.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="small text-muted">Página {{ $produtos->currentPage() }} de {{ $produtos->lastPage() }}. A impressão inclui somente os produtos desta página.</p>

        @if($produtos->hasPages())
        <div class="mt-4 nao-imprimir">
            {{ $produtos->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<div class="nao-imprimir">
    @include('partials.pesquisa-dinamica')
</div>
@endsection

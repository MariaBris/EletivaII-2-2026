@extends('layout')

@section('title', 'Consultar Produto - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Dados do Produto</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('produtos.index') }}" class="btn btn-outline-dark">Voltar</a>
        <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-primary">Editar</a>

        <form method="POST" action="{{ route('produtos.destroy', $produto->id) }}" class="m-0" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row">
            <!-- Coluna da Esquerda: Imagem em Destaque -->
            <div class="col-md-4 text-center mb-4">
                <p class="form-label fw-bold d-block mb-2">Foto em Destaque</p>

                <div class="card p-3 shadow-sm bg-light">
                    @if($produto->imagem)
                    <img src="{{ Str::startsWith($produto->imagem, 'http') ? $produto->imagem : asset($produto->imagem) }}" alt="Foto de {{ $produto->nome }}" class="img-fluid rounded object-fit-cover shadow-sm" style="max-height: 250px; width: 100%;">
                    @else
                    <div class="text-muted py-5 border rounded bg-white">Sem imagem cadastrada</div>
                    @endif
                </div>
            </div>

            <!-- Coluna da Direita: Informações Bloqueadas (Disabled) -->
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoria</label>
                    <input type="text" id="categoria" class="form-control" disabled value="{{ $produto->categoria?->nome ?? 'Sem categoria' }}">
                </div>

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome do produto</label>
                    <input type="text" id="nome" class="form-control" disabled value="{{ $produto->nome }}">
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea id="descricao" class="form-control" rows="3" disabled>{{ $produto->descricao }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="preco" class="form-label">Preço</label>
                        <input type="text" id="preco" class="form-control" disabled value="R$ {{ number_format($produto->preco, 2, ',', '.') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="estoque" class="form-label">Estoque</label>
                        <input type="text" id="estoque" class="form-control" disabled value="{{ $produto->estoque }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

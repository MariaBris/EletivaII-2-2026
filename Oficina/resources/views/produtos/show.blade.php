@extends('layout')

@section('title', 'Consultar Produto - Mazotto')

@section('content')
<div class="mb-4">
    <h1>Dados do Produto</h1>
</div>

<form method="post" action="/produtos/{{ $produto->id }}">
    @csrf
    @method('DELETE')

    <div class="row">
        <!-- Coluna da Esquerda: Imagem em Destaque -->
        <div class="col-md-4 text-center mb-4">
            <label class="form-label fw-bold d-block mb-2">Foto em Destaque</label>
            <div class="card p-3 shadow-sm bg-light">
                @if($produto->imagem)
                <img src="{{ Str::startsWith($produto->imagem, 'http') ? $produto->imagem : asset($produto->imagem) }}" alt="Produto" class="img-fluid rounded object-fit-cover shadow-sm" style="max-height: 250px; width: 100%;">
                @else
                <div class="text-muted py-5 border rounded bg-white">Sem imagem cadastrada</div>
                @endif
            </div>
        </div>

        <!-- Coluna da Direita: Informações Bloqueadas (Disabled) -->
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">Categoria</label>
                <input type="text" class="form-control" disabled value="{{ $produto->categoria->nome ?? 'N/A' }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Nome do produto</label>
                <input type="text" class="form-control" disabled value="{{ $produto->nome }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea class="form-control" rows="3" disabled>{{ $produto->descricao }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Preço</label>
                    <input type="text" class="form-control" disabled value="R$ {{ number_format($produto->preco, 2, ',', '.') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estoque</label>
                    <input type="text" class="form-control" disabled value="{{ $produto->estoque }}">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem a certeza que deseja excluir este produto?')">Excluir</button>
        <a href="/produtos" class="btn btn-outline-dark ms-2">Voltar</a>
    </div>
</form>
@endsection

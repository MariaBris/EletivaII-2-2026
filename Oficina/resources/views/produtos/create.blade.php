@extends('layout')

@section('title', 'Novo Produto - Mazotto')

@section('content')
<div class="mb-4">
    <h1>Novo Produto</h1>
</div>

<form method="post" action="/produtos" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-4 text-center mb-4">
            <label class="form-label fw-bold d-block mb-2">Foto do Produto</label>
            <div class="card p-3 shadow-sm bg-light">
                <div class="text-muted py-4 border rounded mb-3 bg-white small">Pré-visualização da imagem</div>

                <input type="file" name="imagem_arquivo" class="form-control form-control-sm mb-2" accept="image/*">
                <small class="text-muted d-block text-start">OU cole o link da web:</small>
                <input type="url" name="imagem_url" class="form-control form-control-sm" placeholder="https://...">
            </div>
        </div>

        <div class="col-md-8">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do produto</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoria</label>
                <select id="categoria_id" name="categoria_id" class="form-control" required>
                    <option value="">Selecione uma categoria...</option>
                    @foreach($categorias as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="preco" class="form-label">Preço (R$)</label>
                    <input type="number" step="0.01" id="preco" name="preco" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estoque" class="form-label">Estoque</label>
                    <input type="number" id="estoque" name="estoque" class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="/produtos" class="btn btn-outline-dark">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>
@endsection

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-md-4 text-center mb-4">
        <label for="imagem_arquivo" class="form-label fw-bold d-block mb-2">Foto do Produto</label>

        <div class="card p-3 shadow-sm bg-light">
            @if($produto?->imagem)
            <img src="{{ Str::startsWith($produto->imagem, 'http') ? $produto->imagem : asset($produto->imagem) }}" alt="Foto de {{ $produto->nome }}" class="img-fluid rounded mb-3 object-fit-cover shadow-sm" style="max-height: 220px; width: 100%;">
            @else
            <div class="text-muted py-4 border rounded mb-3 bg-white small">Sem imagem cadastrada</div>
            @endif

            <input type="file" id="imagem_arquivo" name="imagem_arquivo" class="form-control form-control-sm mb-2" accept="image/*">

            <label for="imagem_url" class="text-muted small d-block text-start">OU cole o link da web:</label>
            <input type="url" id="imagem_url" name="imagem_url" value="{{ old('imagem_url') }}" class="form-control form-control-sm" placeholder="https://...">
        </div>
    </div>

    <div class="col-md-8">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do produto</label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome', $produto->nome ?? '') }}" maxlength="255" required>
        </div>

        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoria</label>
            <select id="categoria_id" name="categoria_id" class="form-select" required>
                <option value="">Selecione uma categoria...</option>
                @foreach($categorias as $c)
                <option value="{{ $c->id }}" @selected(old('categoria_id', $produto->categoria_id ?? '') == $c->id)>{{ $c->nome }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3" required>{{ old('descricao', $produto->descricao ?? '') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="preco" class="form-label">Preço (R$)</label>
                <input type="number" step="0.01" min="0" max="99999999.99" id="preco" name="preco" class="form-control" value="{{ old('preco', $produto->preco ?? '') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="estoque" class="form-label">Estoque</label>
                <input type="number" step="1" min="0" id="estoque" name="estoque" class="form-control" value="{{ old('estoque', $produto->estoque ?? '') }}" required>
            </div>
        </div>
    </div>
</div>

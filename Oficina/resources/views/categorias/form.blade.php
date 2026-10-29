@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="mb-3">
    <label for="nome" class="form-label">Nome da categoria</label>
    <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome', $categoria->nome ?? '') }}" maxlength="255" required>
</div>

<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea id="descricao" name="descricao" class="form-control" rows="4">{{ old('descricao', $categoria->descricao ?? '') }}</textarea>
</div>

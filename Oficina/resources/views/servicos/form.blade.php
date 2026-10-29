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
    <label for="nome" class="form-label">Nome do serviço</label>
    <input id="nome" name="nome" type="text" class="form-control" value="{{ old('nome', $servico->nome ?? '') }}" maxlength="255" required>
</div>

<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea id="descricao" name="descricao" class="form-control" rows="4" maxlength="5000">{{ old('descricao', $servico->descricao ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="preco" class="form-label">Informe o valor da mão de obra (R$)</label>
    <input id="preco" name="preco" type="number" class="form-control" value="{{ old('preco', $servico->preco ?? '') }}" min="0" max="99999999.99" step="0.01" required>
    <div class="form-text">Informe somente a mão de obra. As peças são cadastradas em Produtos.</div>
</div>

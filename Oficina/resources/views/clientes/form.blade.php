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
    <label for="nome" class="form-label">Nome</label>
    <input id="nome" name="nome" type="text" value="{{ old('nome', $cliente->nome ?? '') }}" class="form-control" maxlength="255" required>
</div>

<div class="mb-3">
    <label for="telefone" class="form-label">Telefone para contato</label>
    <input id="telefone" name="telefone" type="tel" value="{{ old('telefone', $cliente->telefone ?? '') }}" class="form-control" maxlength="20" placeholder="DDD e telefone" required>
</div>

<div class="mb-3">
    <label for="email" class="form-label">E-mail (opcional)</label>
    <input id="email" name="email" type="email" value="{{ old('email', $cliente->email ?? '') }}" class="form-control" maxlength="255">
</div>

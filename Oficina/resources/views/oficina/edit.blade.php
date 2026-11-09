@extends('layout')

@section('title', 'Dados da oficina - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Dados da oficina</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-oficina" class="btn btn-primary">Salvar</button>
        <a href="{{ route('relatorios.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <p class="text-muted">Essas informações aparecem no cabeçalho das impressões. Depois de salvar, abra novamente a página que deseja imprimir para carregar os dados atualizados.</p>

        <form id="formulario-oficina" method="POST" action="{{ route('oficina.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nome" class="form-label">Nome da oficina</label>
                <input id="nome" name="nome" type="text" value="{{ old('nome', $oficina->nome) }}" class="form-control" maxlength="255" required>
            </div>

            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço completo</label>
                <textarea id="endereco" name="endereco" class="form-control" rows="3" maxlength="500" required>{{ old('endereco', $oficina->endereco) }}</textarea>
            </div>

            <div class="mb-0">
                <label for="telefone" class="form-label">Telefone (opcional)</label>
                <input id="telefone" name="telefone" type="tel" value="{{ old('telefone', $oficina->telefone) }}" class="form-control" maxlength="30">
            </div>
        </form>
    </div>
</div>
@endsection

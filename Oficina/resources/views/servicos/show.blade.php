@extends('layout')

@section('title', 'Consultar serviço - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Consultar serviço</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('servicos.index') }}" class="btn btn-outline-dark">Voltar</a>
        <a href="{{ route('servicos.edit', $servico->id) }}" class="btn btn-primary">Editar</a>

        <form method="POST" action="{{ route('servicos.destroy', $servico->id) }}" class="m-0" onsubmit="return confirm('Excluir este serviço?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nome</dt>
            <dd class="col-sm-9">{{ $servico->nome }}</dd>

            <dt class="col-sm-3">Descrição</dt>
            <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $servico->descricao ?? 'Não informada.' }}</dd>

            <dt class="col-sm-3">Mão de obra</dt>
            <dd class="col-sm-9">R$ {{ number_format($servico->preco, 2, ',', '.') }}</dd>
        </dl>
    </div>
</div>
@endsection

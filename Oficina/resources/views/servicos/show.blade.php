@extends('layout')

@section('title', 'Consultar serviço - Mazotto')

@section('content')
<h1 class="h3 mb-4">Consultar serviço</h1>

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

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('servicos.index') }}" class="btn btn-outline-dark">Voltar</a>
            <a href="{{ route('servicos.edit', $servico->id) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@endsection

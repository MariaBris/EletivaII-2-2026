@extends('layout')

@section('title', 'Cadastrar serviço - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Cadastrar serviço</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-servico" class="btn btn-primary">Salvar</button>
        <a href="{{ route('servicos.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-servico" method="POST" action="{{ route('servicos.store') }}">
            @csrf

            @include('servicos.form', ['servico' => null])
        </form>
    </div>
</div>
@endsection

@extends('layout')

@section('title', 'Consultar cliente - Mazotto')

@section('content')
@php
$enderecoCliente = $cliente->endereco;
@endphp

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Consultar cliente</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-dark">Voltar</a>
        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-primary">Editar</a>

        <form method="POST" action="{{ route('clientes.destroy', $cliente->id) }}" class="m-0" onsubmit="return confirm('Excluir este cliente?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nome / razão social</dt>
            <dd class="col-sm-9">{{ $cliente->nome }}</dd>

            <dt class="col-sm-3">CPF/CNPJ</dt>
            <dd class="col-sm-9">{{ $cliente->cpf_cnpj_formatado ?: 'Não informado' }}</dd>

            <dt class="col-sm-3">Telefone</dt>
            <dd class="col-sm-9">{{ $cliente->telefone ?? 'Não informado' }}</dd>

            <dt class="col-sm-3">E-mail</dt>
            <dd class="col-sm-9">{{ $cliente->email ?? 'Não informado' }}</dd>
        </dl>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Endereço</h2>

        <dl class="row mb-0">
            <dt class="col-sm-3">Rua / avenida</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->endereco ?? 'Não informada' }}</dd>

            <dt class="col-sm-3">Número</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->numero ?? 'Não informado' }}</dd>

            <dt class="col-sm-3">Complemento</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->complemento ?? 'Não informado' }}</dd>

            <dt class="col-sm-3">Bairro</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->bairro ?? 'Não informado' }}</dd>

            <dt class="col-sm-3">Cidade</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->cidade ?? 'Não informada' }}</dd>

            <dt class="col-sm-3">UF</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->estado ?? 'Não informada' }}</dd>

            <dt class="col-sm-3">CEP</dt>
            <dd class="col-sm-9">{{ $enderecoCliente?->cep ?? 'Não informado' }}</dd>
        </dl>
    </div>
</div>
@endsection

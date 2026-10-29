@extends('layout')

@section('title', 'Cadastrar veículo - Mazotto')

@section('content')
@php
$retornarParaOs = old('origem', request()->query('origem')) === 'os';
@endphp

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Cadastrar veículo</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-veiculo" class="btn btn-primary">Salvar</button>
        <a href="{{ $retornarParaOs ? route('pedidos.create', ['retomar' => 1]) : route('veiculos.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

@if($retornarParaOs)
<div class="alert alert-info">Após salvar, você retornará à ordem de serviço com este veículo selecionado.</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-veiculo" method="POST" action="{{ route('veiculos.store') }}">
            @csrf

            @if($retornarParaOs)
            <input type="hidden" name="origem" value="os">
            @endif

            @include('veiculos.form', ['veiculo' => null])
        </form>
    </div>
</div>
@endsection

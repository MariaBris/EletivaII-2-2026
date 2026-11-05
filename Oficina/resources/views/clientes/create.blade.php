@extends('layout')

@section('title', 'Cadastrar cliente - Mazotto')

@section('content')
@php
$retornarParaVeiculo = old('origem', request()->query('origem')) === 'veiculo';

$origemVeiculo = old('origem_veiculo', request()->query('origem_veiculo'));
$origemVeiculo = in_array($origemVeiculo, ['os', 'agendamento'], true)
? $origemVeiculo
: null;

$agendamentoRetornoId = filter_var(
old('agendamento_id', request()->query('agendamento_id')),
FILTER_VALIDATE_INT,
['options' => ['min_range' => 1]]
) ?: null;

$parametrosRetorno = ['retomar_cliente' => 1];

if ($origemVeiculo) {
$parametrosRetorno['origem'] = $origemVeiculo;
}

if ($origemVeiculo === 'agendamento' && $agendamentoRetornoId) {
$parametrosRetorno['agendamento_id'] = $agendamentoRetornoId;
}

$rotaCancelar = $retornarParaVeiculo
? route('veiculos.create', $parametrosRetorno)
: route('clientes.index');
@endphp

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Cadastrar cliente</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-cliente" class="btn btn-primary">Salvar</button>
        <a href="{{ $rotaCancelar }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

@if($retornarParaVeiculo)
<div class="alert alert-info">Após salvar, você retornará ao cadastro do veículo com este cliente selecionado.</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-cliente" method="POST" action="{{ route('clientes.store') }}">
            @csrf

            @if($retornarParaVeiculo)
            <input type="hidden" name="origem" value="veiculo">

            @if($origemVeiculo)
            <input type="hidden" name="origem_veiculo" value="{{ $origemVeiculo }}">
            @endif

            @if($origemVeiculo === 'agendamento' && $agendamentoRetornoId)
            <input type="hidden" name="agendamento_id" value="{{ $agendamentoRetornoId }}">
            @endif
            @endif

            @include('clientes.form', ['cliente' => null])
        </form>
    </div>
</div>
@endsection

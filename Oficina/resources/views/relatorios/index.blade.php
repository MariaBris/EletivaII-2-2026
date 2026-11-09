@extends('relatorios.base')

@section('titulo-relatorio', 'Relatórios')

@section('conteudo-relatorio')
<div class="row g-3">
    @foreach([
    ['faturamento', 'OS concluídas / faturamento', 'Consulte os valores das OS concluídas e o ticket médio.'],
    ['servicos', 'Serviços executados', 'Veja quais serviços foram realizados e suas quantidades.'],
    ] as [$rota, $titulo, $descricao])
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5">{{ $titulo }}</h2>
                <p class="text-muted">{{ $descricao }}</p>
                <a href="{{ route('relatorios.' . $rota) }}" class="btn btn-primary mt-auto nao-imprimir">Acessar</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<p class="text-muted mt-4 mb-2">O estoque e sua impressão ficam em Produtos. O histórico fica na consulta de cada veículo.</p>

<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('produtos.index') }}" class="btn btn-outline-dark">Produtos e estoque</a>
    <a href="{{ route('veiculos.index') }}" class="btn btn-outline-dark">Consultar veículos</a>
</div>

@if(auth()->user()->role === 'ADM')
<div class="mt-3">
    <a href="{{ route('oficina.edit') }}" class="btn btn-outline-dark">Dados da oficina</a>
</div>
@endif
@endsection

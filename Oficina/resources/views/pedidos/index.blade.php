@extends('layout')

@section('title', 'Ordens de serviço - Mazotto')

@section('content')
@php
$andamentos = [
'ABERTO' => 'Aberto',
'EM_EXECUCAO' => 'Em execução',
'CONCLUIDO' => 'Concluído',
'CANCELADO' => 'Cancelado',
];

$parametrosVeiculo = isset($veiculoFiltrado)
? ['veiculo_id' => $veiculoFiltrado->id]
: [];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Ordens de serviço</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('pedidos.create', $parametrosVeiculo) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nova ordem de serviço</a>
    </div>
</div>

@if($veiculoFiltrado ?? null)
<div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Histórico do veículo: {{ trim(($veiculoFiltrado->marca ?? '') . ' ' . ($veiculoFiltrado->modelo ?? '')) }} — {{ $veiculoFiltrado->placa }}</span>
    <a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-outline-dark">Mostrar todas as OS</a>
</div>
@endif

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form id="formulario-pesquisa" method="GET" action="{{ route('pedidos.index') }}">
            @if($veiculoFiltrado ?? null)
            <input type="hidden" name="veiculo_id" value="{{ $veiculoFiltrado->id }}">
            @endif

            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="busca" class="form-label">Pesquisar</label>
                    <input id="busca" name="busca" type="search" class="form-control" value="{{ old('busca', request('busca')) }}" maxlength="255" placeholder="Número da OS, placa ou nome do cliente">
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="status" class="form-label">Andamento</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach($andamentos as $valor => $titulo)
                        <option value="{{ $valor }}" @selected(old('status', request('status'))===$valor)>{{ $titulo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg">
                    <label for="data_inicio" class="form-label">Abertura a partir de</label>
                    <input id="data_inicio" name="data_inicio" type="date" class="form-control" value="{{ old('data_inicio', request('data_inicio')) }}">
                </div>

                <div class="col-12 col-md-4 col-lg">
                    <label for="data_fim" class="form-label">Abertura até</label>
                    <input id="data_fim" name="data_fim" type="date" class="form-control" value="{{ old('data_fim', request('data_fim')) }}">
                </div>

                <div class="col-12 d-flex justify-content-end flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Pesquisar</button>
                    <a href="{{ route('pedidos.index', $parametrosVeiculo) }}" class="btn btn-secondary" data-limpar-pesquisa>Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="resultados-pesquisa" aria-busy="false">
    @if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $mensagem)
            <li>{{ $mensagem }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <p class="text-muted mb-3" role="status">{{ $pedidos->total() }} ordem(ns) de serviço encontrada(s).</p>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle bg-white">
            <thead>
                <tr>
                    <th>Ordem de serviço</th>
                    <th>Abertura</th>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Andamento</th>
                    <th>Total</th>
                    <th class="col-acoes text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pedidos as $pedido)
                <tr>
                    <td>#{{ $pedido->id }}</td>
                    <td>{{ $pedido->created_at?->format('d/m/Y H:i') ?? 'Não informada' }}</td>
                    <td>{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</td>
                    <td>{{ $pedido->veiculo?->placa ?? 'Não encontrado' }}</td>
                    <td>{{ $andamentos[$pedido->status] ?? $pedido->status }}</td>
                    <td class="text-nowrap">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>

                    <td class="col-acoes">
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar ordem de serviço"><i class="bi bi-eye" aria-hidden="true"></i></a>
                            <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-sm btn-primary" title="Atualizar" aria-label="Atualizar ordem de serviço"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Nenhuma ordem de serviço encontrada para esta consulta.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pedidos->hasPages())
    <div class="mt-4">
        {{ $pedidos->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@include('partials.pesquisa-dinamica')
@endsection

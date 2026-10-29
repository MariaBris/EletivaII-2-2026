@extends('layout')

@section('title', 'Veículos - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Veículos</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('veiculos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo veículo</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <thead>
            <tr>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Ano</th>
                <th>Proprietário</th>
                <th class="col-acoes text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($veiculos as $veiculo)
            <tr>
                <td>{{ $veiculo->placa }}</td>
                <td>{{ $veiculo->marca ?? '—' }}</td>
                <td>{{ $veiculo->modelo ?? '—' }}</td>
                <td>{{ $veiculo->ano ?? '—' }}</td>
                <td>{{ $veiculo->cliente?->nome ?? 'Sem cliente cadastrado' }}</td>

                <td class="col-acoes">
                    <div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('veiculos.show', $veiculo->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar veículo"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('veiculos.edit', $veiculo->id) }}" class="btn btn-sm btn-primary" title="Editar" aria-label="Editar veículo"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>

                        <form method="POST" action="{{ route('veiculos.destroy', $veiculo->id) }}" class="m-0" onsubmit="return confirm('Excluir este veículo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" aria-label="Excluir veículo"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">Nenhum veículo cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

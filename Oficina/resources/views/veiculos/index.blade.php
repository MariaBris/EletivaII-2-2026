@extends('layout')

@section('title', 'Veículos - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">
        {{ auth()->user()->role === 'ADM' ? 'Veículos' : 'Meus veículos' }}
    </h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">
            Voltar ao painel
        </a>

        <a href="{{ route('veiculos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Novo veículo
        </a>
    </div>
</div>

@if(session('mensagem'))
    <div class="alert alert-success" role="status">
        {{ session('mensagem') }}
    </div>
@endif

@if(session('erro'))
    <div class="alert alert-danger" role="alert">
        {{ session('erro') }}
    </div>
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
                <th class="text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($veiculos as $veiculo)
                <tr>
                    <td>{{ $veiculo->placa }}</td>
                    <td>{{ $veiculo->marca ?? '—' }}</td>
                    <td>{{ $veiculo->modelo ?? '—' }}</td>
                    <td>{{ $veiculo->ano ?? '—' }}</td>
                    <td>{{ $veiculo->usuario?->name ?? 'Não encontrado' }}</td>

                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <a href="{{ route('veiculos.show', $veiculo->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                Consultar
                            </a>

                            @if(auth()->user()->role === 'ADM')
                                <a href="{{ route('veiculos.edit', $veiculo->id) }}"
                                   class="btn btn-sm btn-primary">
                                    Editar
                                </a>

                                <form method="POST"
                                      action="{{ route('veiculos.destroy', $veiculo->id) }}"
                                      onsubmit="return confirm('Excluir este veículo?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Excluir
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Nenhum veículo cadastrado para esta consulta.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

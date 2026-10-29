@extends('layout')

@section('title', 'Clientes - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Clientes</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo cliente</a>
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
                <th>Nome</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th class="col-acoes text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($clientes as $cliente)
            <tr>
                <td>{{ $cliente->nome }}</td>
                <td>
                    @if(blank($cliente->telefone))
                    <span class="badge bg-warning text-dark">Contato pendente</span>
                    @else
                    {{ $cliente->telefone }}
                    @endif
                </td>
                <td>{{ $cliente->email ?? '—' }}</td>

                <td class="col-acoes">
                    <div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar cliente"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-primary" title="Editar" aria-label="Editar cliente"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>

                        <form method="POST" action="{{ route('clientes.destroy', $cliente->id) }}" class="m-0" onsubmit="return confirm('Excluir este cliente?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" aria-label="Excluir cliente"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

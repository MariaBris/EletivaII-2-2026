@extends('layout')

@section('title', 'Serviços - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Serviços e mão de obra</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('servicos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo serviço</a>
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
                <th>Serviço</th>
                <th>Mão de obra</th>
                <th class="col-acoes text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($servicos as $servico)
            <tr>
                <td>{{ $servico->nome }}</td>
                <td class="text-nowrap">R$ {{ number_format($servico->preco, 2, ',', '.') }}</td>
                <td class="col-acoes">
                    <div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('servicos.show', $servico->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar serviço"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('servicos.edit', $servico->id) }}" class="btn btn-sm btn-primary" title="Editar" aria-label="Editar serviço"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>

                        <form method="POST" action="{{ route('servicos.destroy', $servico->id) }}" class="m-0" onsubmit="return confirm('Excluir este serviço?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" aria-label="Excluir serviço"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center text-muted py-4">Nenhum serviço cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

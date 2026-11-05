@extends('layout')

@section('title', 'Agendamentos - Mazotto')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Agendamentos</h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Ordens de serviço</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
        <a href="{{ route('agendamentos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Novo agendamento</a>
    </div>
</div>
@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif
@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger" role="alert"><ul class="mb-0">
    @foreach($errors->all() as $mensagem)
    <li>{{ $mensagem }}</li>
    @endforeach
</ul></div>
@endif

<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
    <form id="formulario-pesquisa" method="GET" action="{{ route('agendamentos.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label for="busca" class="form-label">Pesquisar</label>
                <input id="busca" name="busca" type="search" class="form-control" value="{{ request('busca') }}" maxlength="255" placeholder="Placa, modelo ou cliente">
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label for="data" class="form-label">Data</label>
                <input id="data" name="data" type="date" class="form-control" value="{{ $data }}">
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label for="status" class="form-label">Situação</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Todas</option>
                    @foreach(App\Models\Agendamento::SITUACOES as $valor => $titulo)
                    <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
                <a href="{{ route('agendamentos.index') }}" class="btn btn-secondary" data-limpar-pesquisa>Hoje</a>
                <a href="{{ route('agendamentos.index', ['data' => '']) }}" class="btn btn-outline-dark">Todos os dias</a>
            </div>
        </div>
    </form>
</div></div>

<div id="resultados-pesquisa" aria-busy="false">
    <p class="text-muted">{{ $data !== '' ? 'Agenda de ' . \Carbon\Carbon::parse($data)->format('d/m/Y') : 'Agendamentos de todos os dias' }} — {{ $agendamentos->total() }} registro(s).</p>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle bg-white">
            <thead><tr><th>Data / horário</th><th>Veículo</th><th>Cliente</th><th>Motivo</th><th>Situação</th><th class="col-acoes text-end">Ações</th></tr></thead>
            <tbody>
                @forelse($agendamentos as $agendamento)
                <tr>
                    <td class="text-nowrap">{{ $agendamento->data_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ trim(($agendamento->veiculo?->marca ?? '') . ' ' . ($agendamento->veiculo?->modelo ?? '')) }} — {{ $agendamento->veiculo?->placa }}</td>
                    <td>{{ $agendamento->cliente?->nome ?? 'Sem cliente cadastrado' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($agendamento->motivo, 90) }}</td>
                    <td>{{ App\Models\Agendamento::SITUACOES[$agendamento->status] ?? $agendamento->status }}</td>
                    <td class="col-acoes"><div class="d-flex justify-content-end flex-wrap gap-2">
                        <a href="{{ route('agendamentos.show', $agendamento->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar agendamento"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @if($agendamento->status === 'AGENDADO')
                        <a href="{{ route('agendamentos.edit', $agendamento->id) }}" class="btn btn-sm btn-primary" title="Editar" aria-label="Editar agendamento"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                        <form method="POST" action="{{ route('agendamentos.destroy', $agendamento->id) }}" class="m-0" onsubmit="return confirm('Cancelar este agendamento? O histórico será preservado.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Cancelar agendamento" aria-label="Cancelar agendamento"><i class="bi bi-x-circle" aria-hidden="true"></i></button>
                        </form>
                        @endif
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum agendamento encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($agendamentos->hasPages())
    {{ $agendamentos->onEachSide(1)->links('pagination::bootstrap-5') }}
    @endif
</div>
@include('partials.pesquisa-dinamica')
@endsection

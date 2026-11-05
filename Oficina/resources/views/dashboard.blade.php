@extends('layout')

@section('title', 'Painel - Mazotto')

@section('content')
@php
$usuario = auth()->user();
$equipe = in_array($usuario->role, ['ADM', 'FUNC'], true);
$administrador = $usuario->role === 'ADM';

if ($equipe && isset($produtosEstoqueBaixo)) {
    $produtosEstoqueBaixo->getCollection()->loadSum('itensReservados as estoque_reservado', 'quantidade_reservada');
}

$titulosAgenda = [
    'dia' => 'Agendamentos de hoje',
    'semana' => 'Agendamentos da semana',
    'mes' => 'Agendamentos do mês',
];

$coresAgenda = [
    'AGENDADO' => 'bg-primary',
    'ATENDIDO' => 'bg-success',
    'CANCELADO' => 'bg-secondary',
];
@endphp

<div class="mb-4">
    <h1 class="h3 mb-1">Olá, {{ $usuario->name }}!</h1>
    <p class="text-muted mb-0">Bem-vindo ao painel da Mazotto.</p>
</div>

@if($equipe)

@if($produtosEstoqueBaixo && $produtosEstoqueBaixo->total() > 0)
<div class="card border-warning shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>Estoque e reposição</h2>
                <p class="text-muted mb-0">{{ $produtosEstoqueBaixo->total() }} produto(s) no estoque mínimo ou abaixo dele.</p>
            </div>

            @if($administrador)
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-dark">Ver produtos</a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Estoque físico</th>
                        <th>Reservado</th>
                        <th>Disponível</th>
                        <th>Estoque mínimo</th>
                        <th>Situação</th>

                        @if($administrador)
                        <th class="text-end">Ação</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($produtosEstoqueBaixo as $produto)
                    <tr>
                        <td>{{ $produto->nome }}</td>
                        <td>{{ $produto->estoque }}</td>
                        <td>{{ $produto->estoque_reservado }}</td>
                        <td>{{ $produto->estoque_disponivel }}</td>
                        <td>{{ $produto->estoque_minimo }}</td>

                        <td>
                            @if($produto->estoque <= 0)
                            <span class="badge bg-danger">Sem estoque</span>
                            @elseif($produto->estoque < $produto->estoque_minimo)
                            <span class="badge bg-warning text-dark">Abaixo do mínimo</span>
                            @else
                            <span class="badge bg-warning text-dark">No mínimo</span>
                            @endif
                        </td>

                        @if($administrador)
                        <td class="text-end">
                            <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-outline-dark">Atualizar estoque</a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $administrador ? 7 : 6 }}" class="text-center text-muted py-3">Nenhum produto nesta página. Use a paginação para voltar aos registros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($produtosEstoqueBaixo->hasPages())
        <div class="mt-3">
            {{ $produtosEstoqueBaixo->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endif

<div id="agenda-painel" class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1"><i class="bi bi-calendar-week me-2" aria-hidden="true"></i>{{ $titulosAgenda[$periodoAgenda] }}</h2>

                <p class="text-muted mb-0">
                    {{ $inicioAgenda->format('d/m/Y') }}@if($periodoAgenda !== 'dia') até {{ $fimAgenda->format('d/m/Y') }}@endif
                    — {{ $agendamentosPainel->total() }} agendamento(s).
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('agendamentos.index', ['data' => '']) }}" class="btn btn-outline-dark">Agenda completa</a>
                <a href="{{ route('agendamentos.create') }}" class="btn btn-primary">Novo agendamento</a>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3" aria-label="Período da agenda">
            @foreach(['dia' => 'Hoje', 'semana' => 'Semana', 'mes' => 'Mês'] as $valor => $titulo)
            <a href="{{ route('dashboard', ['periodo_agenda' => $valor]) }}#agenda-painel" class="btn btn-sm {{ $periodoAgenda === $valor ? 'btn-primary' : 'btn-outline-primary' }}" @if($periodoAgenda === $valor) aria-current="true" @endif>{{ $titulo }}</a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ $periodoAgenda === 'dia' ? 'Horário' : 'Data / horário' }}</th>
                        <th>Veículo</th>
                        <th>Cliente</th>
                        <th>Situação</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($agendamentosPainel as $agendamento)
                    <tr>
                        <td class="text-nowrap">{{ $agendamento->data_hora->format($periodoAgenda === 'dia' ? 'H:i' : 'd/m H:i') }}</td>
                        <td>{{ trim(($agendamento->veiculo?->marca ?? '') . ' ' . ($agendamento->veiculo?->modelo ?? '')) }} — {{ $agendamento->veiculo?->placa }}</td>
                        <td>{{ $agendamento->cliente?->nome ?? 'Sem cliente cadastrado' }}</td>

                        <td>
                            <span class="badge {{ $coresAgenda[$agendamento->status] ?? 'bg-secondary' }}">{{ App\Models\Agendamento::SITUACOES[$agendamento->status] ?? $agendamento->status }}</span>
                        </td>

                        <td class="text-end">
                            <a href="{{ route('agendamentos.show', $agendamento->id) }}" class="btn btn-sm btn-outline-dark" title="Consultar" aria-label="Consultar agendamento"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Nenhum agendamento encontrado para este período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($agendamentosPainel->hasPages())
        <div class="mt-3">
            {{ $agendamentosPainel->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<h2 class="h5 mb-3">Acesso rápido</h2>

<div class="row g-3">
    @foreach([
        ['pedidos.index', 'OS', 'bi-clipboard-check', 'Abra e acompanhe as ordens de serviço.', false],
        ['agendamentos.index', 'Agendamentos', 'bi-calendar-week', 'Organize as datas e os horários dos atendimentos.', false],
        ['clientes.index', 'Clientes', 'bi-people', 'Consulte e atualize os dados dos clientes.', false],
        ['veiculos.index', 'Veículos', 'bi-car-front', 'Consulte os veículos e seus históricos.', false],
        ['produtos.index', 'Produtos', 'bi-box-seam', 'Consulte as peças e atualize o estoque.', true],
        ['servicos.index', 'Serviços', 'bi-wrench-adjustable', 'Consulte os serviços e os valores da mão de obra.', true],
        ['categorias.index', 'Categorias', 'bi-tags', 'Organize as categorias dos produtos.', true],
    ] as [$rota, $titulo, $icone, $descricao, $somenteAdm])

    @if(!$somenteAdm || $administrador)
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex flex-column p-4">
                <i class="bi {{ $icone }} fs-2 mb-2" style="color: var(--azul-mazotto);" aria-hidden="true"></i>
                <h3 class="h5">{{ $titulo }}</h3>
                <p class="text-muted">{{ $descricao }}</p>
                @if($rota === 'pedidos.index')
                <div class="d-grid gap-2 mt-auto">
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nova OS</a>
                    <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Consultar OS</a>
                </div>
                @else
                <a href="{{ route($rota) }}" class="btn btn-primary mt-auto">Acessar {{ $titulo }}</a>
                @endif
            </div>
        </div>
    </div>
    @endif

    @endforeach
</div>
@endif
@endsection

@extends('layout')

@section('title', 'Consultar veículo - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">{{ trim(($veiculo->marca ?? '') . ' ' . ($veiculo->modelo ?? '')) }} - {{ $veiculo->placa }}</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('veiculos.index') }}" class="btn btn-outline-dark">Voltar</a>
        <a href="{{ route('pedidos.index', ['veiculo_id' => $veiculo->id]) }}" class="btn btn-outline-dark">Histórico de OS</a>
        <a href="{{ route('pedidos.create', ['veiculo_id' => $veiculo->id]) }}" class="btn btn-primary">Abrir OS</a>
        <a href="{{ route('veiculos.edit', $veiculo->id) }}" class="btn btn-primary">Editar</a>

        <form method="POST" action="{{ route('veiculos.destroy', $veiculo->id) }}" class="m-0" onsubmit="return confirm('Excluir este veículo?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
    </div>
</div>

{{-- Mensagens de sucesso, bloqueio e validação. --}}
@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <dl class="row g-3 mb-0">
            <div class="col-12 col-md-6">
                <dt class="d-inline">Placa:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->placa }}</dd>
            </div>

            <div class="col-12 col-md-6">
                <dt class="d-inline">Marca:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->marca ?? '-' }}</dd>
            </div>

            <div class="col-12 col-md-6">
                <dt class="d-inline">Modelo:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->modelo ?? '-' }}</dd>
            </div>

            <div class="col-12 col-md-6">
                <dt class="d-inline">Ano:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->ano ?? '-' }}</dd>
            </div>

            <div class="col-12 col-md-6">
                <dt class="d-inline">Proprietário:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>
            </div>

            <div class="col-12 col-md-6">
                <dt class="d-inline">Telefone:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->cliente?->telefone ?? 'Não informado' }}</dd>
            </div>

            <div class="col-12 col-md-6">
                <dt class="d-inline">Quilometragem informada no cadastro:</dt>
                <dd class="d-inline mb-0">{{ $veiculo->quilometragem_informada === null ? 'Não informada' : number_format($veiculo->quilometragem_informada, 0, ',', '.') . ' km' }}</dd>
            </div>
        </dl>
    </div>
</div>

<div id="historico-servicos" class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h2 class="h5 mb-2">Histórico de serviços na Mazotto</h2>
        <p class="text-muted">Serviços autorizados e executados em OS concluídas. Cada realização permanece no histórico, mesmo quando o serviço se repete.</p>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Serviço realizado</th>
                        <th>Conclusão da OS</th>
                        <th>Quilometragem de entrada</th>
                        <th class="text-end">OS</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($historicoServicos as $item)
                    <tr>
                        <td>{{ $item->servico?->nome ?? 'Serviço não encontrado' }}</td>
                        <td>{{ $item->pedido?->concluido_em?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') ?? 'Não registrada' }}</td>
                        <td>{{ $item->pedido?->quilometragem_entrada === null ? 'Não informada' : number_format($item->pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</td>
                        <td class="text-end"><a href="{{ route('pedidos.show', $item->pedido_id) }}" class="btn btn-sm btn-outline-dark" aria-label="Consultar OS número {{ $item->pedido_id }}">#{{ $item->pedido_id }}</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Nenhum serviço autorizado e executado em OS concluída para este veículo.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($historicoServicos->hasPages())
        <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" aria-label="Paginação do histórico de serviços">
            <span class="text-muted small">{{ $historicoServicos->firstItem() }} a {{ $historicoServicos->lastItem() }} de {{ $historicoServicos->total() }} serviços</span>

            <div class="d-flex gap-2">
                @if($historicoServicos->onFirstPage())
                <span class="btn btn-sm btn-outline-dark disabled" aria-disabled="true">Anterior</span>
                @else
                <a href="{{ $historicoServicos->previousPageUrl() }}#historico-servicos" class="btn btn-sm btn-outline-dark" rel="prev">Anterior</a>
                @endif

                @if($historicoServicos->hasMorePages())
                <a href="{{ $historicoServicos->nextPageUrl() }}#historico-servicos" class="btn btn-sm btn-outline-dark" rel="next">Próxima</a>
                @else
                <span class="btn btn-sm btn-outline-dark disabled" aria-disabled="true">Próxima</span>
                @endif
            </div>
        </nav>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h2 class="h5 mb-2">Manutenções informadas pelo cliente</h2>
        <p class="text-muted">Informações do cadastro, que podem incluir serviços realizados em outra oficina.</p>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Manutenção</th>
                        <th>Data informada</th>
                        <th>Quilometragem informada</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Última troca de óleo</td>
                        <td>{{ $veiculo->oleo_data_informada?->format('d/m/Y') ?? 'Não informada' }}</td>
                        <td>{{ $veiculo->oleo_km_informado === null ? 'Não informada' : number_format($veiculo->oleo_km_informado, 0, ',', '.') . ' km' }}</td>
                    </tr>
                    <tr>
                        <td>Última revisão</td>
                        <td>{{ $veiculo->revisao_data_informada?->format('d/m/Y') ?? 'Não informada' }}</td>
                        <td>{{ $veiculo->revisao_km_informado === null ? 'Não informada' : number_format($veiculo->revisao_km_informado, 0, ',', '.') . ' km' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

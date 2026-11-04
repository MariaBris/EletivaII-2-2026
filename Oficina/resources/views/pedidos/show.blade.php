@extends('layout')

@section('title', 'OS #' . $pedido->id . ' - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/impressao_ordemS.css') }}?v=6" media="print">
@endpush

@section('content')
@php
$andamentos = [
'ABERTO' => 'Aberto',
'EM_EXECUCAO' => 'Em execução',
'CONCLUIDO' => 'Concluído',
'CANCELADO' => 'Cancelado',
];

$nomeVeiculo = trim(
($pedido->veiculo?->marca ?? '') . ' ' .
($pedido->veiculo?->modelo ?? '')
);

$combustivel = App\Models\Pedido::COMBUSTIVEIS[$pedido->combustivel_entrada] ?? '';
@endphp

<div class="os-tela">
    <div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">Mazotto - OS #{{ $pedido->id }}</h1>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Voltar</a>
            <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-primary">Atualizar</a>
            <a href="{{ route('checklist.edit', $pedido->id) }}" class="btn btn-primary">Checklist de inspeção</a>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()">Imprimir ordem de serviço</button>

            @if(in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true))
            <form method="POST" action="{{ route('pedidos.destroy', $pedido->id) }}" class="m-0" onsubmit="return confirm('Cancelar esta OS? As peças com baixa registrada serão devolvidas ao estoque.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Cancelar OS</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('mensagem'))
    <div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
    @endif

    @if(session('alertas_estoque'))
    <div class="alert alert-warning" role="alert">
        <p class="fw-bold mb-2">Atenção à reposição das peças utilizadas:</p>

        <ul class="mb-0">
            @foreach(session('alertas_estoque') as $alerta)
            <li>{{ $alerta['nome'] }} - Estoque atual: {{ $alerta['estoque'] }}; mínimo: {{ $alerta['estoque_minimo'] }}.@if($alerta['estoque'] <= 0) Sem estoque disponível.@endif</li>
                    @endforeach
        </ul>
    </div>
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

    @if($pedido->estoque_baixado === null && $pedido->itens->isNotEmpty())
    <div class="alert alert-warning">Esta OS antiga possui peças cuja baixa de estoque ainda precisa ser conferida antes do cancelamento.</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Dados do atendimento</h2>

            <dl class="row mb-0">
                <dt class="col-sm-3">Abertura</dt>
                <dd class="col-sm-9">{{ $pedido->created_at?->format('d/m/Y H:i') ?? 'Não informada' }}</dd>

                <dt class="col-sm-3">Andamento</dt>
                <dd class="col-sm-9">{{ $andamentos[$pedido->status] ?? $pedido->status }}</dd>

                <dt class="col-sm-3">Cliente</dt>
                <dd class="col-sm-9">{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>

                <dt class="col-sm-3">Telefone</dt>
                <dd class="col-sm-9">{{ $pedido->cliente?->telefone ?? 'Não informado' }}</dd>

                <dt class="col-sm-3">Veículo</dt>
                <dd class="col-sm-9">
                    @if($pedido->veiculo)
                    {{ $nomeVeiculo }} — {{ $pedido->veiculo->placa }}
                    @else
                    Não encontrado
                    @endif
                </dd>

                <dt class="col-sm-3">Aberta por</dt>
                <dd class="col-sm-9">{{ $pedido->operador?->name ?? 'Não informado' }}</dd>

                <dt class="col-sm-3">Responsável pelo serviço</dt>
                <dd class="col-sm-9">{{ $pedido->responsavel ?? 'Não informado' }}</dd>

                <dt class="col-sm-3">Solicitação / observações</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $pedido->observacoes ?? 'Não informadas' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Condições de entrada</h2>

            <dl class="row mb-0">
                <dt class="col-sm-3">Quilometragem de entrada</dt>
                <dd class="col-sm-9">{{ $pedido->quilometragem_entrada === null ? 'Não informada' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</dd>

                <dt class="col-sm-3">Combustível na entrada</dt>
                <dd class="col-sm-9">{{ $combustivel ?: 'Não informado' }}</dd>

                <dt class="col-sm-3">Avarias na entrada</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $pedido->avarias_entrada ?? 'Não informadas' }}</dd>
            </dl>
        </div>
    </div>

    @include('pedidos.fotos')

    <h2 class="h5">Peças utilizadas</h2>

    <div class="table-responsive mb-4">
        <table class="table table-striped align-middle bg-white">
            <thead>
                <tr>
                    <th>Peça</th>
                    <th>Quantidade</th>
                    <th>Preço unitário</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pedido->itens as $item)
                <tr>
                    <td>{{ $item->produto?->nome ?? 'Peça não encontrada' }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Nenhuma peça incluída.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="h5">Serviços e mão de obra</h2>

    <div class="table-responsive mb-4">
        <table class="table table-striped align-middle bg-white">
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th>Quantidade</th>
                    <th>Preço unitário</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pedido->servicos as $item)
                <tr>
                    <td>{{ $item->servico?->nome ?? 'Serviço não encontrado' }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td class="text-nowrap">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td class="text-end text-nowrap">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Nenhum serviço incluído.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 text-end">
            <p class="h4 mb-0">Total: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
        </div>
    </div>
</div>

<div class="os-impressao d-none">
    <div class="ficha-cabecalho">
        <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" class="ficha-logo">

        <div class="ficha-titulo">
            <h1>Mazotto Soluções Automotivas</h1>
            <p>Ordem de serviço</p>
        </div>

        <strong>OS: #{{ $pedido->id }}</strong>
    </div>

    <table>
        <tbody>
            <tr>
                <td><strong>Abertura:</strong> {{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
                <td><strong>Saída:</strong> {{ $pedido->saida_em?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Andamento:</strong> {{ $andamentos[$pedido->status] ?? $pedido->status }}</td>
                <td><strong>Aberta por:</strong> {{ $pedido->operador?->name }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="2">DADOS DO CLIENTE</th>
                <th colspan="2">DADOS DO VEÍCULO</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td colspan="2"><strong>Nome:</strong> {{ $pedido->cliente?->nome }}</td>
                <td colspan="2"><strong>Veículo:</strong> {{ $nomeVeiculo }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Telefone:</strong> {{ $pedido->cliente?->telefone }}</td>
                <td><strong>Placa:</strong> {{ $pedido->veiculo?->placa }}</td>
                <td><strong>Ano:</strong> {{ $pedido->veiculo?->ano }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>E-mail:</strong> {{ $pedido->cliente?->email }}</td>
                <td colspan="2"><strong>Quilometragem:</strong> {{ $pedido->quilometragem_entrada === null ? '' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Responsável pelo serviço:</strong> {{ $pedido->responsavel }}</td>
                <td colspan="2"><strong>Combustível na entrada:</strong> {{ $combustivel }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>SOLICITAÇÃO DO CLIENTE / OBSERVAÇÕES</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="ficha-texto">{{ $pedido->observacoes }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>AVARIAS DESCRITAS NA ENTRADA</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="ficha-texto">{{ $pedido->avarias_entrada }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="4">PEÇAS UTILIZADAS</th>
            </tr>
            <tr>
                <th>Peça</th>
                <th class="ficha-quantidade">Quantidade</th>
                <th class="ficha-valor">Preço unitário</th>
                <th class="ficha-valor">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pedido->itens as $item)
            <tr>
                <td>{{ $item->produto?->nome ?? 'Peça não encontrada' }}</td>
                <td class="ficha-quantidade">{{ $item->quantidade }}</td>
                <td class="ficha-valor">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                <td class="ficha-valor">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">Nenhuma peça incluída.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="4">SERVIÇOS E MÃO DE OBRA</th>
            </tr>
            <tr>
                <th>Serviço</th>
                <th class="ficha-quantidade">Quantidade</th>
                <th class="ficha-valor">Preço unitário</th>
                <th class="ficha-valor">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pedido->servicos as $item)
            <tr>
                <td>{{ $item->servico?->nome ?? 'Serviço não encontrado' }}</td>
                <td class="ficha-quantidade">{{ $item->quantidade }}</td>
                <td class="ficha-valor">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                <td class="ficha-valor">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">Nenhum serviço incluído.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ficha-total">
        <strong>Total da ordem de serviço: R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
    </div>
</div>
@endsection

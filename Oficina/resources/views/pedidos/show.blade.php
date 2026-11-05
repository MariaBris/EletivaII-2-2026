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

$temTrabalhoIniciado = $pedido->itens->contains(function ($item) {
return in_array($item->situacao, ['EM_EXECUCAO', 'EXECUTADO', 'INTERROMPIDO'], true);
}) || $pedido->servicos->contains(function ($item) {
return in_array($item->situacao, ['EM_EXECUCAO', 'EXECUTADO', 'INTERROMPIDO'], true);
});

$podeCancelar = in_array($pedido->status, ['ABERTO', 'EM_EXECUCAO'], true)
&& !$temTrabalhoIniciado;
@endphp

<div class="os-tela">
    <div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">Mazotto - OS #{{ $pedido->id }}</h1>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Voltar</a>
            <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-primary">Atualizar</a>
            <a href="{{ route('pedidos.imprimir', ['id' => $pedido->id, 'modo' => 'completo']) }}" class="btn btn-outline-dark" target="_blank" rel="noopener">Imprimir completo</a>
            <a href="{{ route('pedidos.imprimir', ['id' => $pedido->id, 'modo' => 'resumo']) }}" class="btn btn-outline-dark" target="_blank" rel="noopener">Imprimir resumo</a>

            @if($podeCancelar)
            <form method="POST" action="{{ route('pedidos.destroy', $pedido->id) }}" class="m-0" onsubmit="return confirm('Cancelar esta OS e os itens pendentes?');">
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

    @if(session('alertas_estoque'))
    <div class="alert alert-warning" role="alert">
        <p class="fw-bold mb-2">Atenção à reposição:</p>

        <ul class="mb-0">
            @foreach(session('alertas_estoque') as $alerta)
            <li>{{ $alerta['nome'] }} — Estoque: {{ $alerta['estoque'] }}; mínimo: {{ $alerta['estoque_minimo'] }}.</li>
            @endforeach
        </ul>
    </div>
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
                <dd class="col-sm-9">{{ $nomeVeiculo }} — {{ $pedido->veiculo?->placa }}</dd>

                <dt class="col-sm-3">Aberta por</dt>
                <dd class="col-sm-9">{{ $pedido->operador?->name ?? 'Não informado' }}</dd>

                <dt class="col-sm-3">Responsável</dt>
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
                <dt class="col-sm-3">Quilometragem</dt>
                <dd class="col-sm-9">{{ $pedido->quilometragem_entrada === null ? 'Não informada' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</dd>

                <dt class="col-sm-3">Combustível</dt>
                <dd class="col-sm-9">{{ $combustivel ?: 'Não informado' }}</dd>

                <dt class="col-sm-3">Avarias</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $pedido->avarias_entrada ?? 'Não informadas' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 nao-imprimir">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Fotos de entrada</h2>

        @if($pedido->fotosEntrada->isEmpty())
        <p class="text-muted mb-0">Esta OS não possui fotos de entrada registradas.</p>
        @else
        <div class="row g-3">
            @foreach(App\Models\FotoEntrada::POSICOES as $posicao => $titulo)
            @php
            $foto = $pedido->fotosEntrada->firstWhere('posicao', $posicao);
            @endphp

            <div class="col-12 col-md-6">
                <h3 class="h6">{{ $titulo }}</h3>

                @if($foto)
                <a href="{{ route('pedidos.fotos.show', ['id' => $pedido->id, 'foto' => $foto->id]) }}" target="_blank" rel="noopener"><img src="{{ route('pedidos.fotos.show', ['id' => $pedido->id, 'foto' => $foto->id]) }}" alt="{{ $titulo }} do veículo na entrada da OS {{ $pedido->id }}" class="img-fluid rounded border" style="width: 100%; max-height: 250px; object-fit: contain;" loading="lazy"></a>
                <p class="form-text mb-0">Registrada em {{ $foto->created_at?->format('d/m/Y H:i') }} por {{ $foto->operador?->name ?? 'Não informado' }}.</p>
                @else
                <p class="text-muted">Foto não registrada.</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

    @include('pedidos.form', ['consulta' => true])

    <h2 class="h5">Orçamento e trabalhos registrados</h2>
    @include('pedidos.itens-tabela', ['modo' => 'consultar'])

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4 text-end">
            <p class="h5 mb-2">Total do orçamento: R$ {{ number_format($pedido->total_orcamento, 2, ',', '.') }}</p>
            <p class="h4 mb-2">Total autorizado: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
            <p class="text-muted mb-0">O orçamento inclui os itens recusados para comparação. Cancelados ficam fora dos dois totais. Interrompidos utilizam o valor combinado no total autorizado.</p>
        </div>
    </div>
</div>

@endsection

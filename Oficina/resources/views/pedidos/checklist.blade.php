@extends('layout')

@section('title', 'Checklist da OS #' . $pedido->id . ' - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/checklist.css') }}?v=1">
@endpush

@section('content')
@php
$situacoesInspecao = [
'OK' => 'OK',
'ATENCAO' => 'NÃO OK',
];

$situacoesAcessorios = [
'PRESENTE' => 'Presente',
'AUSENTE' => 'Ausente',
];
@endphp

<div class="checklist-tela">
    <div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">Checklist da OS #{{ $pedido->id }}</h1>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-outline-dark">Voltar à OS</a>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()">Imprimir checklist salvo</button>

            @if($editavel)
            <button type="submit" form="formulario-checklist" class="btn btn-primary">Salvar</button>
            @endif
        </div>
    </div>

    @if(session('mensagem'))
    <div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
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

    @if(!$editavel)
    <div class="alert alert-info">Esta OS está encerrada. O checklist está disponível somente para consulta e impressão.</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <dl class="row mb-0">
                <dt class="col-sm-3">Cliente</dt>
                <dd class="col-sm-9">{{ $pedido->cliente?->nome ?? 'Sem cliente cadastrado' }}</dd>

                <dt class="col-sm-3">Veículo</dt>
                <dd class="col-sm-9">{{ trim(($pedido->veiculo?->marca ?? '') . ' ' . ($pedido->veiculo?->modelo ?? '')) }} — {{ $pedido->veiculo?->placa }}</dd>

                <dt class="col-sm-3">Quilometragem de entrada</dt>
                <dd class="col-sm-9">{{ $pedido->quilometragem_entrada === null ? 'Não informada' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</dd>
            </dl>
        </div>
    </div>

    <p class="text-muted">Marque somente os itens conferidos. Sem marcação significa “Não verificado”. Salve antes de imprimir: a ficha utiliza somente os dados salvos. Marcar NÃO OK não autoriza reparos nem adiciona cobranças.</p>

    <form id="formulario-checklist" method="POST" action="{{ route('checklist.update', $pedido->id) }}">
        @csrf
        @method('PUT')

        <fieldset @disabled(!$editavel)>
            @foreach($grupos as $grupo => $dadosGrupo)
            @php
            $camposGrupo = collect($campos)->where('grupo', $grupo);
            $opcoes = $grupo === 'ACESSORIOS' ? $situacoesAcessorios : $situacoesInspecao;
            @endphp

            @if($camposGrupo->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">{{ $dadosGrupo['titulo'] }}</h2>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Situação</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($camposGrupo as $codigo => $campo)
                                @php
                                $registro = $itens->get($codigo);
                                $situacao = old('itens.' . $codigo . '.situacao', $registro?->situacao ?? 'NAO_VERIFICADO');
                                @endphp

                                <tr @class(['table-warning'=> $situacao === 'ATENCAO']) data-checklist-item>
                                    <td style="min-width: 180px;">{{ $campo['nome'] }}</td>

                                    <td style="min-width: 230px;">
                                        <div class="checklist-opcoes" role="group" aria-label="Situação de {{ $campo['nome'] }}">
                                            <input type="radio" name="itens[{{ $codigo }}][situacao]" value="NAO_VERIFICADO" class="d-none" data-nao-verificado @checked($situacao==='NAO_VERIFICADO' ) aria-label="Não verificado">

                                            @foreach($opcoes as $valor => $titulo)
                                            <div class="form-check">
                                                <input id="situacao-{{ $codigo }}-{{ $valor }}" name="itens[{{ $codigo }}][situacao]" type="radio" value="{{ $valor }}" class="form-check-input" @checked($situacao===$valor)>
                                                <label for="situacao-{{ $codigo }}-{{ $valor }}" class="form-check-label">{{ $titulo }}</label>
                                            </div>
                                            @endforeach

                                            @if($situacao === 'NAO_APLICA')
                                            <div class="form-check">
                                                <input id="situacao-{{ $codigo }}-antiga" name="itens[{{ $codigo }}][situacao]" type="radio" value="NAO_APLICA" class="form-check-input" checked>
                                                <label for="situacao-{{ $codigo }}-antiga" class="form-check-label">Não se aplica (anterior)</label>
                                            </div>
                                            @endif
                                        </div>

                                        @if($editavel)
                                        <button type="button" class="btn btn-sm btn-link text-secondary p-0 mt-2" data-limpar-marcacao aria-label="Limpar marcação de {{ $campo['nome'] }}">Limpar marcação</button>
                                        @endif
                                    </td>

                                    <td style="min-width: 220px;">
                                        <label for="observacao-{{ $codigo }}" class="visually-hidden">Observações sobre {{ $campo['nome'] }}</label>
                                        <textarea id="observacao-{{ $codigo }}" name="itens[{{ $codigo }}][observacoes]" class="form-control" rows="2" maxlength="1000">{{ old('itens.' . $codigo . '.observacoes', $registro?->observacoes) }}</textarea>

                                        @if($registro?->conferido_em)
                                        <p class="form-text mb-0">Última conferência: {{ $registro->conferido_em->format('d/m/Y H:i') }} — {{ $registro->conferente?->name ?? 'Não informado' }}</p>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </fieldset>
    </form>
</div>

@include('pedidos.checklist-impressao')

<script>
    (() => {
        const formulario = document.getElementById('formulario-checklist');

        if (!formulario) return;

        formulario.querySelectorAll('[data-checklist-item]').forEach(linha => {
            const atualizarDestaque = () => {
                const marcado = linha.querySelector('input[type="radio"]:checked');

                linha.classList.toggle('table-warning', marcado?.value === 'ATENCAO');
            };

            linha.querySelectorAll('input[type="radio"]').forEach(campo => {
                campo.addEventListener('change', atualizarDestaque);
            });

            linha.querySelector('[data-limpar-marcacao]')?.addEventListener('click', () => {
                const naoVerificado = linha.querySelector('[data-nao-verificado]');

                naoVerificado.checked = true;
                naoVerificado.dispatchEvent(new Event('change'));
            });
        });
    })();
</script>
@endsection

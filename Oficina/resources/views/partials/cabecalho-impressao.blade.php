@php
$dadosOficina = App\Models\Oficina::dados();
@endphp

<div class="cabecalho-impressao">
    <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo da oficina" class="cabecalho-impressao-logo">

    <div class="cabecalho-impressao-dados">
        <h2>{{ $dadosOficina->nome }}</h2>
        <p class="cabecalho-impressao-endereco">{{ $dadosOficina->endereco }}</p>

        @if(filled($dadosOficina->telefone))
        <p>Telefone: {{ $dadosOficina->telefone }}</p>
        @endif

        @if(filled($subtituloImpressao ?? null))
        <p class="cabecalho-impressao-subtitulo">{{ $subtituloImpressao }}</p>
        @endif
    </div>

    <strong class="cabecalho-impressao-identificacao">{{ $identificacaoImpressao ?? 'Documento' }}</strong>
</div>

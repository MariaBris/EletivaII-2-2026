@php
$itensImpressao = $itens->filter(function ($item) {
    return $item->situacao !== 'NAO_VERIFICADO';
});

$combustiveisImpressao = [
    'VAZIO' => 'Vazio / reserva',
    'UM_QUARTO' => '1/4 do tanque',
    'METADE' => '1/2 do tanque',
    'TRES_QUARTOS' => '3/4 do tanque',
    'CHEIO' => 'Cheio',
];

$veiculoImpressao = trim(
    ($pedido->veiculo?->marca ?? '') . ' ' .
    ($pedido->veiculo?->modelo ?? '')
);
@endphp

<div class="checklist-impressao">
    <div class="ficha-cabecalho">
        <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" class="ficha-logo">

        <div class="ficha-titulo">
            <h1>Mazotto Soluções Automotivas</h1>
            <p>Ficha de entrada e inspeção do veículo</p>
        </div>

        <strong>OS: #{{ $pedido->id }}</strong>
    </div>

    <table>
        <tbody>
            <tr>
                <td colspan="2"><strong>Solicitação do cliente:</strong><div class="ficha-texto">{{ $pedido->observacoes }}</div></td>
            </tr>
            <tr>
                <td><strong>Entrada:</strong> {{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
                <td><strong>Saída:</strong> {{ $pedido->saida_em?->format('d/m/Y H:i') }}</td>
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
                <td colspan="2"><strong>Veículo:</strong> {{ $veiculoImpressao }}</td>
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
                <td colspan="2"><strong>Combustível:</strong> {{ $combustiveisImpressao[$pedido->combustivel_entrada] ?? '' }}</td>
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
                <td class="ficha-texto" style="min-height: 25px;">{{ $pedido->avarias_entrada }}</td>
            </tr>
        </tbody>
    </table>

    @foreach(['ACESSORIOS', 'INTERNO', 'ILUMINACAO', 'MECANICA'] as $grupo)
    @php
    $itensGrupo = $itensImpressao->where('grupo', $grupo);
    $acessorios = $grupo === 'ACESSORIOS';
    @endphp

    @if($itensGrupo->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th colspan="4">{{ mb_strtoupper($grupos[$grupo]['titulo'] ?? $grupo) }}</th>
            </tr>
            <tr>
                <th>Item</th>
                <th class="ficha-opcao">{{ $acessorios ? 'Presente' : 'OK' }}</th>
                <th class="ficha-opcao">{{ $acessorios ? 'Ausente' : 'NÃO OK' }}</th>
                <th class="ficha-observacao">Observações</th>
            </tr>
        </thead>

        <tbody>
            @foreach($itensGrupo as $item)
            <tr>
                <td>{{ $item->nome }}</td>
                <td class="ficha-opcao">{{ in_array($item->situacao, ['OK', 'PRESENTE'], true) ? 'X' : '' }}</td>
                <td class="ficha-opcao">{{ in_array($item->situacao, ['ATENCAO', 'AUSENTE'], true) ? 'X' : '' }}</td>
                <td>
                    @if($item->situacao === 'NAO_APLICA')
                    <span>Não se aplica — registro anterior.</span>
                    @endif
                    <span class="ficha-texto">{{ $item->observacoes }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach

    @if($itensImpressao->isEmpty())
    <p>Nenhum item de inspeção marcado e salvo nesta OS.</p>
    @endif

    <p class="ficha-nota">Esta ficha apresenta somente os itens conferidos e salvos. A indicação “NÃO OK” registra uma condição encontrada e não representa autorização para reparos.</p>
</div>

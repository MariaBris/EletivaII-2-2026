@extends('layout')

@section('title', $modelo ? 'Modelo de checklist - Mazotto' : 'Imprimir OS #' . $pedido->id . ' - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/impressao_ordemS.css') }}?v=7">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 nao-imprimir">
    <h1 class="h3 mb-0">{{ $modelo ? 'Modelo de checklist em branco' : ($modo === 'resumo' ? 'Resumo da OS #' : 'Documento completo da OS #') . $pedido->id }}</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ $modelo ? route('pedidos.index') : route('pedidos.show', $pedido->id) }}" class="btn btn-outline-dark">Voltar</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
    </div>
</div>

<p class="text-muted nao-imprimir">Use papel A4, escala 100% e desative cabeçalhos e rodapés do navegador. No documento completo, o resumo começa em uma nova página no final.</p>

@if($modelo)
<div class="ficha-checklist">
    <div class="ficha-cabecalho">
        <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" class="ficha-logo">

        <div class="text-center">
            <h2 class="h5 mb-1">Mazotto Soluções Automotivas</h2>
            <p class="mb-0">Ficha de entrada e inspeção do veículo</p>
        </div>

        <strong>OS: __________</strong>
    </div>

    <table class="ficha-tabela">
        <tbody>
            <tr>
                <td colspan="2" class="ficha-campo"><strong>Solicitação do cliente:</strong></td>
            </tr>
            <tr>
                <td><strong>Entrada:</strong> ____/____/________</td>
                <td><strong>Saída:</strong> ____/____/________</td>
            </tr>
        </tbody>
    </table>

    <table class="ficha-tabela">
        <thead>
            <tr>
                <th colspan="2" class="ficha-titulo">DADOS DO CLIENTE</th>
                <th colspan="2" class="ficha-titulo">DADOS DO VEÍCULO</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td colspan="2" class="ficha-campo"><strong>Nome:</strong></td>
                <td class="ficha-campo"><strong>Marca:</strong></td>
                <td class="ficha-campo"><strong>Modelo:</strong></td>
            </tr>
            <tr>
                <td colspan="2" class="ficha-campo"><strong>CPF/CNPJ:</strong></td>
                <td class="ficha-campo"><strong>Ano:</strong></td>
                <td class="ficha-campo"><strong>Placa:</strong></td>
            </tr>
            <tr>
                <td colspan="2" class="ficha-campo"><strong>Telefone:</strong></td>
                <td class="ficha-campo"><strong>Quilometragem:</strong></td>
                <td class="ficha-campo"><strong>Responsável:</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="ficha-campo"><strong>E-mail:</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="ficha-campo"><strong>Endereço / nº / complemento:</strong></td>
            </tr>
            <tr>
                <td class="ficha-campo"><strong>Bairro:</strong></td>
                <td class="ficha-campo"><strong>Cidade:</strong></td>
                <td class="ficha-campo"><strong>UF:</strong></td>
                <td class="ficha-campo"><strong>CEP:</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="ficha-tabela">
        <thead>
            <tr>
                <th>INSPEÇÃO VISUAL DO VEÍCULO</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <p class="small mb-3">Assinale com X os danos existentes no veículo e indique o nível de combustível na entrada.</p>

                    <div class="ficha-inspecao-visual">
                        <div class="ficha-carro">
                            <img src="{{ asset('img/carrocheck.jpg') }}" alt="Vistas das duas laterais, frente e traseira do veículo para marcar avarias" class="ficha-imagem-carro">
                        </div>

                        <div class="ficha-combustivel">
                            <p class="small fw-bold mb-2">Combustível na entrada</p>
                            <img src="{{ asset('img/fuelcheck.jpg') }}" alt="Marcador do nível de combustível" class="ficha-imagem-combustivel">
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="ficha-campo"><strong>Observações sobre avarias:</strong></td>
            </tr>
        </tbody>
    </table>

    <p class="small">Fotos da entrada: □ Frente &nbsp; □ Traseira &nbsp; □ Lateral esquerda &nbsp; □ Lateral direita</p>

    @foreach(['ACESSORIOS', 'INTERNO', 'ILUMINACAO', 'MECANICA'] as $grupo)
    @php
    $dadosGrupo = $grupos[$grupo];
    @endphp

    <div @class(['ficha-pagina-mecanica'=> $grupo === 'MECANICA'])>
        <table class="ficha-tabela">
            <thead>
                <tr>
                    <th colspan="4" class="ficha-titulo">{{ mb_strtoupper($dadosGrupo['titulo']) }}</th>
                </tr>
                <tr>
                    <th>Item</th>
                    <th class="ficha-opcao">{{ $grupo === 'ACESSORIOS' ? 'Presente' : 'OK' }}</th>
                    <th class="ficha-opcao">{{ $grupo === 'ACESSORIOS' ? 'Ausente' : 'NÃO OK' }}</th>
                    <th class="ficha-observacao">Observações</th>
                </tr>
            </thead>

            <tbody>
                @foreach($dadosGrupo['itens'] as $nome)
                <tr>
                    <td>{{ $nome }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <p class="small">Os campos em branco representam itens não verificados. A inspeção não representa autorização para executar reparos.</p>

    <p class="mb-1"><strong>Observações gerais:</strong></p>
    <div class="ficha-linha"></div>
    <div class="ficha-linha"></div>

    <p class="mb-1"><strong>Reparos autorizados pelo cliente:</strong></p>
    <div class="ficha-linha"></div>
    <div class="ficha-linha"></div>

    <p class="small mb-3">Autorização recebida por: __________________________ Data: ____/____/________ Horário: ____:____</p>
    <p class="small mb-0">Responsável pela inspeção: ____________________________________________________</p>
</div>
@else
@php
$andamentos = [
'ABERTO' => 'Aberto',
'EM_EXECUCAO' => 'Em execução',
'CONCLUIDO' => 'Concluído',
'CANCELADO' => 'Cancelado',
];

$clienteImpressao = $pedido->cliente;
$enderecoImpressao = $clienteImpressao?->endereco;

$logradouro = collect([
$enderecoImpressao?->endereco,
$enderecoImpressao?->numero,
])->filter(fn ($valor) => filled($valor))->implode(', ');

$cidadeUf = collect([
$enderecoImpressao?->cidade,
$enderecoImpressao?->estado,
])->filter(fn ($valor) => filled($valor))->implode(' / ');

$enderecoCompleto = collect([
$logradouro,
$enderecoImpressao?->complemento,
$enderecoImpressao?->bairro,
$cidadeUf,
filled($enderecoImpressao?->cep) ? 'CEP: ' . $enderecoImpressao->cep : null,
])->filter(fn ($valor) => filled($valor))->implode(' — ');

$nomeVeiculo = trim(
($pedido->veiculo?->marca ?? '') . ' ' . ($pedido->veiculo?->modelo ?? '')
);

$combustivel = App\Models\Pedido::COMBUSTIVEIS[$pedido->combustivel_entrada] ?? '';

$checklistImpresso = $pedido->checklistItens
->filter(fn ($item) => $item->situacao !== 'NAO_VERIFICADO' || filled($item->observacoes))
->sortBy('ordem');
@endphp

<div class="documento-os">
    @if($modo === 'completo')
    <section class="ficha-detalhes">
        <div class="ficha-cabecalho">
            <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" class="ficha-logo">

            <div class="ficha-titulo">
                <h1>Mazotto Soluções Automotivas</h1>
                <p>Orçamento e ordem de serviço</p>
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
                    <th>DADOS DO CLIENTE</th>
                    <th>DADOS DO VEÍCULO</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td><strong>Nome:</strong> {{ $clienteImpressao?->nome }}</td>
                    <td><strong>Veículo:</strong> {{ $nomeVeiculo }}</td>
                </tr>
                <tr>
                    <td><strong>Telefone:</strong> {{ $clienteImpressao?->telefone }}</td>
                    <td><strong>Placa:</strong> {{ $pedido->veiculo?->placa }}</td>
                </tr>
                <tr>
                    <td>
                        @if(filled($clienteImpressao?->email))
                        <strong>E-mail:</strong> {{ $clienteImpressao->email }}
                        @endif
                    </td>
                    <td><strong>Ano:</strong> {{ $pedido->veiculo?->ano }}</td>
                </tr>
                <tr>
                    <td><strong>Responsável:</strong> {{ $pedido->responsavel }}</td>
                    <td><strong>Quilometragem:</strong> {{ $pedido->quilometragem_entrada === null ? '' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</td>
                </tr>

                @if(filled($clienteImpressao?->cpf_cnpj))
                <tr>
                    <td colspan="2"><strong>CPF/CNPJ:</strong> {{ $clienteImpressao->cpf_cnpj_formatado }}</td>
                </tr>
                @endif

                @if(filled($enderecoCompleto))
                <tr>
                    <td colspan="2" style="overflow-wrap: anywhere;"><strong>Endereço:</strong> {{ $enderecoCompleto }}</td>
                </tr>
                @endif

                <tr>
                    <td colspan="2"><strong>Combustível na entrada:</strong> {{ $combustivel }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>SOLICITAÇÃO / OBSERVAÇÕES</th>
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
                    <th>AVARIAS NA ENTRADA</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="ficha-texto">{{ $pedido->avarias_entrada }}</td>
                </tr>
            </tbody>
        </table>

        <h2>Checklist de inspeção</h2>

        @foreach(['ACESSORIOS', 'INTERNO', 'ILUMINACAO', 'MECANICA'] as $grupo)
        @php
        $itensGrupo = $checklistImpresso->where('grupo', $grupo);
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
                        <span class="ficha-texto">{{ $item->observacoes }}</span>

                        @if($item->conferido_em)
                        <small class="ficha-auditoria">{{ $item->conferido_em->format('d/m/Y H:i') }} — {{ $item->conferente?->name }}</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endforeach

        @if($checklistImpresso->isEmpty())
        <p>Nenhum item de inspeção marcado e salvo nesta OS.</p>
        @endif

        <p class="ficha-nota">Esta ficha apresenta somente os itens conferidos e salvos. A indicação “NÃO OK” registra uma condição encontrada e não representa autorização para reparos.</p>

        <h2>Orçamento e trabalhos registrados</h2>

        @include('pedidos.itens-tabela', ['modo' => 'imprimir'])
    </section>
    @endif

    <section @class(['ficha-resumo', 'ficha-resumo-separado'=> $modo === 'completo'])>
        <div class="ficha-cabecalho">
            <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" class="ficha-logo">

            <div class="ficha-titulo">
                <h1>Mazotto Soluções Automotivas</h1>
                <p>Resumo do orçamento / OS</p>
            </div>

            <strong>OS: #{{ $pedido->id }}</strong>
        </div>

        <table>
            <tbody>
                <tr>
                    <td><strong>Cliente:</strong> {{ $clienteImpressao?->nome }}</td>
                    <td><strong>Telefone:</strong> {{ $clienteImpressao?->telefone }}</td>
                </tr>

                @if(filled($clienteImpressao?->cpf_cnpj))
                <tr>
                    <td colspan="2"><strong>CPF/CNPJ:</strong> {{ $clienteImpressao->cpf_cnpj_formatado }}</td>
                </tr>
                @endif

                @if(filled($enderecoCompleto))
                <tr>
                    <td colspan="2" style="overflow-wrap: anywhere;"><strong>Endereço:</strong> {{ $enderecoCompleto }}</td>
                </tr>
                @endif

                <tr>
                    <td colspan="2"><strong>Veículo:</strong> {{ $nomeVeiculo }} — {{ $pedido->veiculo?->placa }}</td>
                </tr>
                <tr>
                    <td><strong>Abertura:</strong> {{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
                    <td><strong>Saída:</strong> {{ $pedido->saida_em?->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Andamento:</strong> {{ $andamentos[$pedido->status] ?? $pedido->status }}</td>
                    <td><strong>Responsável:</strong> {{ $pedido->responsavel }}</td>
                </tr>
                <tr>
                    <td><strong>Quilometragem:</strong> {{ $pedido->quilometragem_entrada === null ? '' : number_format($pedido->quilometragem_entrada, 0, ',', '.') . ' km' }}</td>
                    <td><strong>Combustível:</strong> {{ $combustivel }}</td>
                </tr>
            </tbody>
        </table>

        <h2>Valores do atendimento</h2>

        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Total do orçamento</td>
                    <td class="text-end">R$ {{ number_format($pedido->total_orcamento, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total autorizado pelo cliente</strong></td>
                    <td class="text-end"><strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <p>O orçamento inclui itens aguardando decisão e recusados para comparação. Itens cancelados ficam fora dos totais. Em trabalhos interrompidos, o total autorizado considera o valor combinado registrado.</p>

        <p>Este resumo apresenta os valores da OS. Não representa comprovante de pagamento nem substitui o detalhamento dos itens e das autorizações.</p>

        <div class="ficha-assinaturas">
            <p>Cliente: _______________________________________________________</p>
            <p>Responsável: __________________________________________________</p>
        </div>
    </section>
</div>
@endif
@endsection

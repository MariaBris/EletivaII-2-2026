@extends('layout')

@section('title', 'Modelo de checklist - Mazotto')

@push('estilos')
<style>
    .ficha-checklist {
        max-width: 1000px;
        margin: 0 auto;
        padding: 24px;
        background: #fff;
        color: #000;
    }

    .ficha-cabecalho {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 16px;
    }

    .ficha-logo {
        width: 100px;
        height: auto;
    }

    .ficha-tabela {
        width: 100%;
        margin-bottom: 16px;
        border-collapse: collapse;
        font-size: 13px;
    }

    .ficha-tabela th,
    .ficha-tabela td {
        padding: 6px;
        border: 1px solid #000;
        vertical-align: middle;
    }

    .ficha-tabela th {
        background: #eee;
    }

    .ficha-titulo {
        text-align: center;
        font-weight: bold;
    }

    .ficha-campo {
        height: 42px;
        vertical-align: top !important;
    }

    .ficha-opcao {
        width: 55px;
        text-align: center;
    }

    .ficha-observacao {
        width: 35%;
    }

    .ficha-linha {
        min-height: 32px;
        margin-bottom: 12px;
        border-bottom: 1px solid #000;
    }

    .ficha-inspecao-visual {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .ficha-imagem-carro {
        display: block;
        width: 100%;
        height: auto;
    }

    .ficha-combustivel {
        flex: 0 0 25%;
        text-align: center;
    }

    .ficha-carro {
        flex: 1;
        min-width: 0;
    }

    .ficha-imagem-combustivel {
        display: block;
        width: 100%;
        height: auto;
    }

    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    @media print {

        nav,
        header,
        footer,
        .navbar,
        .nao-imprimir {
            display: none !important;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #000 !important;
        }

        main,
        main.container,
        main.container-fluid {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .ficha-checklist {
            max-width: none;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.15;
        }

        .ficha-cabecalho {
            gap: 10px;
            margin-bottom: 6px;
        }

        .ficha-cabecalho h2 {
            font-size: 14px;
        }

        .ficha-cabecalho p {
            font-size: 10px;
        }

        .ficha-logo {
            width: 65px;
        }

        .ficha-tabela {
            margin-bottom: 6px;
            font-size: 9px;
        }

        .ficha-tabela th,
        .ficha-tabela td {
            padding: 2px 4px;
            line-height: 1.15;
        }

        .ficha-tabela tr {
            break-inside: avoid;
        }

        .ficha-tabela thead {
            display: table-header-group;
        }

        .ficha-campo {
            height: 24px;
        }

        .ficha-opcao {
            width: 45px;
        }

        .ficha-observacao {
            width: 38%;
        }

        .ficha-inspecao-visual {
            gap: 12px;
        }

        .ficha-imagem-carro {
            width: 100%;
            max-height: 42mm;
            object-fit: contain;
        }

        .ficha-imagem-combustivel {
            width: 100%;
            max-height: 25mm;
            object-fit: contain;
        }

        .ficha-checklist .small {
            font-size: 9px;
        }

        .ficha-checklist p {
            margin-bottom: 4px !important;
        }

        .ficha-linha {
            min-height: 18px;
            margin-bottom: 5px;
        }

        .ficha-pagina-mecanica {
            break-before: page;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 nao-imprimir">
    <h1 class="h3 mb-0">Modelo de checklist em branco</h1>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-dark">Voltar</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir modelo</button>
    </div>
</div>

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
                <td colspan="2" class="ficha-campo"><strong>Telefone:</strong></td>
                <td class="ficha-campo"><strong>Ano:</strong></td>
                <td class="ficha-campo"><strong>Placa:</strong></td>
            </tr>
            <tr>
                <td colspan="2" class="ficha-campo"><strong>E-mail:</strong></td>
                <td class="ficha-campo"><strong>Quilometragem:</strong></td>
                <td class="ficha-campo"><strong>Responsável:</strong></td>
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
                    <th colspan="5" class="ficha-titulo">{{ mb_strtoupper($dadosGrupo['titulo']) }}</th>
                </tr>
                <tr>
                    <th>Item</th>
                    <th class="ficha-opcao">{{ $grupo === 'ACESSORIOS' ? 'Presente' : 'OK' }}</th>
                    <th class="ficha-opcao">{{ $grupo === 'ACESSORIOS' ? 'Ausente' : 'Atenção' }}</th>
                    <th class="ficha-opcao">Não se aplica</th>
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
@endsection

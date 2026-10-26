<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OS {{ $pedido->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/ficha-os.css') }}">
</head>

<body>
    <main class="folha">
        <div class="cabecalho">
            @if(is_file(public_path('img/logo_mazotto.jpg')))
            <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo da empresa" class="logo">
            @endif
        </div>

        <h1>Ordem de serviço nº {{ $pedido->id }}</h1>

        <div class="dados">
            <div>
                <div class="rotulo">Data de abertura</div>
                <div class="valor">{{ $pedido->created_at?->format('d/m/Y H:i') ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">Responsável pelo serviço</div>
                <div class="valor">{{ $pedido->responsavel ?? '' }}</div>
            </div>

            <div class="campo-inteiro">
                <div class="rotulo">Cliente</div>
                <div class="valor">{{ $pedido->cliente?->nome ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">Telefone</div>
                <div class="valor">{{ $pedido->cliente?->telefone ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">E-mail</div>
                <div class="valor">{{ $pedido->cliente?->email ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">Placa</div>
                <div class="valor">{{ $pedido->veiculo?->placa ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">Marca</div>
                <div class="valor">{{ $pedido->veiculo?->marca ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">Modelo</div>
                <div class="valor">{{ $pedido->veiculo?->modelo ?? '' }}</div>
            </div>

            <div>
                <div class="rotulo">Ano</div>
                <div class="valor">{{ $pedido->veiculo?->ano ?? '' }}</div>
            </div>
        </div>

        <h2>Solicitação / observações</h2>
        <div class="observacoes">{{ $pedido->observacoes ?? '' }}</div>

        <h2>Peças e serviços</h2>
        <table class="tabela">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="quantidade">Qtd.</th>
                    <th class="dinheiro">Valor unitário</th>
                    <th class="dinheiro">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $item)
                <tr>
                    <td><span class="tipo-item">Peça</span>{{ $item->produto?->nome ?? '' }}</td>
                    <td class="quantidade">{{ $item->quantidade }}</td>
                    <td class="dinheiro">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td class="dinheiro">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                @foreach($pedido->servicos as $item)
                <tr>
                    <td><span class="tipo-item">Serviço</span>{{ $item->servico?->nome ?? '' }}</td>
                    <td class="quantidade">{{ $item->quantidade }}</td>
                    <td class="dinheiro">R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td class="dinheiro">R$ {{ number_format($item->quantidade * $item->preco, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                @if($pedido->itens->isEmpty() && $pedido->servicos->isEmpty())
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>

        <p class="total">Total registrado: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>

        <div class="assinaturas">
            <div class="assinatura">Cliente</div>
            <div class="assinatura">Responsável da oficina</div>
        </div>
    </main>
</body>

</html>

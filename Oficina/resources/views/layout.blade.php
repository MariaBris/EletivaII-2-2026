<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mazotto - Soluções Automotivas')</title>

    <!-- Ícone da aba com v=2 para forçar a atualização do cache -->
    <link rel="icon" href="{{ asset('img/logo_mazotto.jpg') }}?v=2" type="image/jpeg">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-mazotto: #1a2530;
            --vermelho-mazotto: #d0202d;
        }

        body {
            background-color: #f4f6f9;
        }

        .navbar-custom,
        .footer-custom {
            background-color: var(--azul-mazotto);
            color: white;
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #ffffff;
        }

        .navbar-custom .nav-link:hover {
            color: var(--vermelho-mazotto);
        }

        /* Botão Azul Marinho (Editar / Novo) */
        .btn-primary {
            background-color: var(--azul-mazotto);
            border-color: var(--azul-mazotto);
        }

        .btn-primary:hover {
            background-color: #0f161d;
            border-color: #0f161d;
        }

        /* Botão Vermelho (Excluir) */
        .btn-danger {
            background-color: var(--vermelho-mazotto);
            border-color: var(--vermelho-mazotto);
        }

        /* Botão Vazado Azul (Consultar / Voltar / Cancelar) */
        .btn-outline-dark,
        .btn-outline-primary {
            color: var(--azul-mazotto);
            border-color: var(--azul-mazotto);
        }

        .btn-outline-dark:hover,
        .btn-outline-primary:hover {
            background-color: var(--azul-mazotto);
            color: white;
        }

        .footer-custom {
            margin-top: auto;
        }

        .col-acoes {
            width: 140px;
            text-align: right;
        }

        .img-tabela {
            width: 45px;
            height: 45px;
            object-fit: cover; /* Garante que a imagem não distorce, preenchendo o quadrado perfeitamente */
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Chama o cabeçalho -->
    @include('cabecalho')

    <!-- Conteúdo dinâmico das telas -->
    <div class="container py-4 flex-grow-1">
        @yield('content')
    </div>

    <!-- Chama o rodapé -->
    @include('rodape')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

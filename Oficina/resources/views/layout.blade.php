<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Mazotto - Soluções Automotivas')</title>

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

        .btn-primary {
            background-color: var(--azul-mazotto);
            border-color: var(--azul-mazotto);
        }

        .btn-primary:hover {
            background-color: #0f161d;
            border-color: #0f161d;
        }

        .btn-danger {
            background-color: var(--vermelho-mazotto);
            border-color: var(--vermelho-mazotto);
        }

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
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .acoes-os {
            position: sticky;
            bottom: 0;
            background-color: white;
            padding: 15px;
            border-top: 1px solid #dee2e6;
            z-index: 10;
        }

        .barra-acoes {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: #fff;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 6px;
        }
    </style>

    @stack('estilos')

    <link rel="stylesheet" href="{{ asset('css/cabecalho-impressao.css') }}?v=1">
</head>

<body class="d-flex flex-column min-vh-100">
    @include('cabecalho')

    <div class="container py-4 flex-grow-1">
        @unless($__env->hasSection('cabecalho-impressao-proprio'))
        <div class="cabecalho-impressao-global">
            @include('partials.cabecalho-impressao', ['identificacaoImpressao' => str_replace(' - Mazotto', '', trim($__env->yieldContent('title', 'Documento')))])
        </div>
        @endunless

        @yield('content')
    </div>

    @include('rodape')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

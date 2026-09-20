<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mazotto - Soluções Automotivas')</title>
    <link rel="icon" href="{{ asset('img/logo_mazotto.jpg') }}?v=2" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-mazotto: #1a2530;
            /* Cor do fundo escuro */
            --vermelho-mazotto: #d0202d;
            /* Cor vermelha da Mazotto */
        }

        body {
            background-color: #f4f6f9;
        }

        .navbar-custom {
            background-color: var(--azul-mazotto);
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #ffffff;
        }

        .navbar-custom .nav-link:hover {
            color: var(--vermelho-mazotto);
        }

        .btn-primary {
            background-color: var(--vermelho-mazotto);
            border-color: var(--vermelho-mazotto);
        }

        .btn-primary:hover {
            background-color: #a81722;
            border-color: #a81722;
        }

        .footer-custom {
            background-color: var(--azul-mazotto);
            color: white;
            margin-top: auto;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Chama o ficheiro cabecalho.blade.php -->
    @include('cabecalho')

    <!-- O conteúdo de cada ecrã (tabelas, formulários) vai entrar aqui -->
    <div class="container py-4 flex-grow-1">
        @yield('content')
    </div>

    <!-- Chama o ficheiro rodape.blade.php -->
    @include('rodape')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

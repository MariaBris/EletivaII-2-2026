<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Exercícios Laravel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 260px;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            min-height: 100vh;
        }
    </style>
</head>

<body class="bg-light">
    <div class="d-flex">
        <aside class="sidebar bg-dark text-white p-3 d-flex flex-column flex-shrink-0">
            <a href="/" class="d-flex align-items-center mb-3 me-md-auto text-white text-decoration-none fs-5 fw-bold">
                Lista de Exercícios
            </a>
            <hr class="text-secondary my-2">

            <ul class="nav nav-pills flex-column mb-auto">
                @for ($i = 1; $i <= 20; $i++)
                    <li class="nav-item mb-1">
                    <a href="/exer{{ $i }}"
                        class="nav-link text-white {{ request()->is('exer'.$i.'*') ? 'active' : '' }}">
                        Exercício {{ $i }}
                    </a>
                    </li>
                    @endfor
            </ul>
        </aside>

        <main class="main-content p-4">
            <div class="container-fluid">
                @yield('conteudo', '')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
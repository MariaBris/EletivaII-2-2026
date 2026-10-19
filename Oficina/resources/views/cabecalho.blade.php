<nav class="navbar navbar-expand-lg navbar-custom shadow-sm"
    data-bs-theme="dark">

    <div class="container-fluid">

        {{-- Logo e nome da oficina. --}}
        <a class="navbar-brand d-flex align-items-center gap-2"
            href="{{ url('/') }}">
            <img
                src="{{ asset('img/logo_mazotto.jpg') }}"
                alt="Logo Mazotto"
                height="45"
                style="border-radius: 4px;">
            <strong>Mazotto</strong>
        </a>

        {{-- Botão do menu para telas pequenas. --}}
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                {{-- Menu de quem está conectado. --}}
                @auth
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('dashboard') }}">
                        Painel
                    </a>
                </li>

                {{-- Mostra somente os módulos com rotas cadastradas. --}}
                @foreach ([
                ['categorias.index', 'Categorias'],
                ['produtos.index', 'Produtos'],
                ['servicos.index', 'Serviços'],
                ['clientes.index', 'Clientes'],
                ['veiculos.index', 'Veículos'],
                ['os.index', 'Ordens de Serviço']
                ] as [$rota, $titulo])
                @if (Route::has($rota))
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route($rota) }}">
                        {{ $titulo }}
                    </a>
                </li>
                @endif
                @endforeach

                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('profile.edit') }}">
                        <i class="bi bi-person-circle me-1"></i>
                        Meu perfil
                    </a>
                </li>

                {{-- O logout do Breeze precisa usar POST e CSRF. --}}
                <li class="nav-item">
                    <form method="POST"
                        action="{{ route('logout') }}"
                        class="m-0">
                        @csrf

                        <button type="submit"
                            class="nav-link border-0 bg-transparent">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Sair
                        </button>
                    </form>
                </li>
                @endauth

                {{-- Menu de quem ainda não fez login. --}}
                @guest
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('login') }}">
                        Entrar
                    </a>
                </li>

                @if (Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('register') }}">
                        Cadastre-se
                    </a>
                </li>
                @endif
                @endguest

            </ul>
        </div>
    </div>
</nav>

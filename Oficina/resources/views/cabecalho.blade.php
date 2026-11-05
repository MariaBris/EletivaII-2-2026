<nav class="navbar navbar-expand-lg navbar-custom shadow-sm" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('dashboard') : url('/') }}">
            <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" height="45" style="border-radius: 4px;">
            <strong>Mazotto</strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                @auth
                @php
                $perfil = auth()->user()->role;
                $equipe = in_array($perfil, ['ADM', 'FUNC'], true);
                @endphp

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Início</a>
                </li>

                {{-- Esconder os links melhora a navegação; o middleware protege as rotas. --}}
                @if($equipe)
                @foreach([
                    ['pedidos.index', 'OS', 'pedidos.*'],
                    ['agendamentos.index', 'Agendamentos', 'agendamentos.*'],
                    ['clientes.index', 'Clientes', 'clientes.*'],
                    ['veiculos.index', 'Veículos', 'veiculos.*'],
                ] as [$rota, $titulo, $padrao])
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs($padrao) ? 'active' : '' }}" href="{{ route($rota) }}" @if(request()->routeIs($padrao)) aria-current="page" @endif>{{ $titulo }}</a>
                </li>
                @endforeach
                @endif

                @if($perfil === 'ADM')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('categorias.*', 'produtos.*', 'servicos.*') ? 'active' : '' }}" href="#" id="menuCadastros" role="button" data-bs-toggle="dropdown" aria-expanded="false">Cadastros</a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuCadastros">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}">Categorias</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('produtos.*') ? 'active' : '' }}" href="{{ route('produtos.index') }}">Produtos</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}" href="{{ route('servicos.index') }}">Serviços</a>
                        </li>
                    </ul>
                </li>
                @endif

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="#" id="menuConta" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle me-1" aria-hidden="true"></i>Conta</a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuConta">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">Meu perfil</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Sair</button>
                            </form>
                        </li>
                    </ul>
                </li>
                @endauth

                @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Entrar</a>
                </li>

                @if(Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">Cadastre-se</a>
                </li>
                @endif
                @endguest
            </ul>
        </div>
    </div>
</nav>

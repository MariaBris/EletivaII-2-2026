<nav class="navbar navbar-expand-lg navbar-custom shadow-sm" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img src="{{ asset('img/logo_mazotto.jpg') }}" alt="Logo Mazotto" height="45" style="border-radius: 4px;">
            <strong>Mazotto</strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                @auth
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Painel</a></li>

                {{-- Esconder os links melhora a navegação; o middleware protege as rotas. --}}
                @if(auth()->user()->role === 'ADM')
                @foreach([
                ['categorias.index', 'Categorias'],
                ['produtos.index', 'Produtos'],
                ['servicos.index', 'Serviços'],
                ['clientes.index', 'Clientes'],
                ['pedidos.index', 'Ordens de serviço']
                ] as [$rota, $titulo])
                @if(Route::has($rota))
                <li class="nav-item"><a class="nav-link" href="{{ route($rota) }}">{{ $titulo }}</a></li>
                @endif
                @endforeach
                @endif

                <li class="nav-item"><a class="nav-link" href="{{ route('veiculos.index') }}">{{ auth()->user()->role === 'ADM' ? 'Veículos' : 'Meus veículos' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">Meu perfil</a></li>

                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent"><i class="bi bi-box-arrow-right me-1"></i>Sair</button>
                    </form>
                </li>
                @endauth

                @guest
                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Entrar</a></li>

                @if(Route::has('register'))
                <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Cadastre-se</a></li>
                @endif
                @endguest
            </ul>
        </div>
    </div>
</nav>

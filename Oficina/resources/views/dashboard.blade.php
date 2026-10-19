@extends('layout')
@section('title', 'Painel - Mazotto')
@section('content')
{{-- Informações da conta conectada e atalhos da oficina. --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Olá, {{ auth()->user()->name }}!</h1>
        <p class="text-muted mb-0">Bem-vindo ao painel da Mazotto.</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i>Sair</button>
    </form>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <p class="mb-1"><strong>E-mail:</strong> {{ auth()->user()->email }}</p>
    <p class="mb-0"><strong>Perfil:</strong> {{ auth()->user()->role === 'ADM' ? 'Administrador' : 'Cliente' }}</p>
</div></div>
<div class="row g-3">
    {{-- O cliente vê veículos e perfil; o ADM também vê os cadastros administrativos. --}}
    @foreach([
        ['categorias.index', 'Categorias', 'bi-tags', 'Organize as categorias dos produtos.'],
        ['produtos.index', 'Produtos', 'bi-box-seam', 'Consulte os produtos da oficina.'],
        ['veiculos.index', 'Veículos', 'bi-car-front', 'Consulte os veículos cadastrados.'],
        ['profile.edit', 'Meu perfil', 'bi-person-circle', 'Atualize seus dados e sua senha.']
    ] as [$rota, $titulo, $icone, $descricao])
        @if(auth()->user()->role === 'ADM' || in_array($rota, ['veiculos.index', 'profile.edit']))
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm"><div class="card-body d-flex flex-column">
                <i class="bi {{ $icone }} fs-2 mb-2" style="color: var(--azul-mazotto);"></i>
                <h2 class="h5">{{ $titulo }}</h2>
                <p class="text-muted">{{ $descricao }}</p>
                <a href="{{ route($rota) }}" class="btn btn-primary mt-auto">Acessar {{ $titulo }}</a>
            </div></div>
        </div>
        @endif
    @endforeach
</div>
@endsection

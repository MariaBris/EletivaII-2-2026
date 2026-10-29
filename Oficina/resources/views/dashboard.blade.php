@extends('layout')

@section('title', 'Painel - Mazotto')

@section('content')
@php
$usuario = auth()->user();

$perfis = [
'ADM' => 'Administrador',
'FUNC' => 'Funcionário',
'CLI' => 'Cliente',
];

$atalhos = [
['categorias.index', 'Categorias', 'bi-tags', 'Organize as categorias dos produtos.', ['ADM']],
['produtos.index', 'Produtos', 'bi-box-seam', 'Consulte os produtos da oficina.', ['ADM']],
['clientes.index', 'Clientes', 'bi-people', 'Consulte os clientes cadastrados.', ['ADM', 'FUNC']],
['veiculos.index', 'Veículos', 'bi-car-front', 'Consulte os veículos cadastrados.', ['ADM', 'FUNC']],
['servicos.index', 'Serviços', 'bi-wrench-adjustable', 'Consulte os serviços cadastrados.', ['ADM']],
['pedidos.index', 'Ordens de serviço', 'bi-clipboard-check', 'Acompanhe os atendimentos da oficina.', ['ADM', 'FUNC']],
['profile.edit', 'Meu perfil', 'bi-person-circle', 'Atualize seus dados e sua senha.', ['ADM', 'FUNC', 'CLI']],
];
@endphp

{{-- Informações da conta conectada e atalhos da oficina. --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Olá, {{ $usuario->name }}!</h1>
        <p class="text-muted mb-0">Bem-vindo ao painel da Mazotto.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <p class="mb-1"><strong>E-mail:</strong> {{ $usuario->email }}</p>
        <p class="mb-0"><strong>Perfil:</strong> {{ $perfis[$usuario->role] ?? $usuario->role }}</p>
    </div>
</div>

@if(in_array($usuario->role, ['ADM', 'FUNC'], true))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Estoque e reposição</h2>

        @if($produtosEstoqueBaixo->total() > 0)
        <div class="alert alert-warning" role="alert">{{ $produtosEstoqueBaixo->total() }} produto(s) com estoque igual ou abaixo do mínimo. Confira a necessidade de reposição.</div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Estoque atual</th>
                        <th>Estoque mínimo</th>
                        <th>Situação</th>
                        @if($usuario->role === 'ADM')
                        <th class="text-end">Ação</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @foreach($produtosEstoqueBaixo as $produto)
                    <tr>
                        <td>{{ $produto->nome }}</td>
                        <td>{{ $produto->estoque }}</td>
                        <td>{{ $produto->estoque_minimo }}</td>
                        <td>
                            @if($produto->estoque <= 0)
                                <span class="badge bg-danger">Sem estoque</span>
                                @else
                                <span class="badge bg-warning text-dark">Reposição necessária</span>
                                @endif
                        </td>

                        @if($usuario->role === 'ADM')
                        <td class="text-end"><a href="{{ route('produtos.show', $produto->id) }}" class="btn btn-sm btn-outline-dark">Consultar produto</a></td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($produtosEstoqueBaixo->hasPages())
        <div class="mt-3">
            {{ $produtosEstoqueBaixo->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        @endif

        @else
        <div class="alert alert-success mb-0" role="status">Nenhum produto com estoque igual ou abaixo do mínimo.</div>
        @endif
    </div>
</div>
@endif

<div class="row g-3">
    {{-- Os atalhos respeitam o perfil da conta; as rotas mantêm suas próprias proteções. --}}
    @foreach($atalhos as [$rota, $titulo, $icone, $descricao, $perfisPermitidos])
    @if(in_array($usuario->role, $perfisPermitidos, true))
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex flex-column">
                <i class="bi {{ $icone }} fs-2 mb-2" style="color: var(--azul-mazotto);"></i>
                <h2 class="h5">{{ $titulo }}</h2>
                <p class="text-muted">{{ $descricao }}</p>
                <a href="{{ route($rota) }}" class="btn btn-primary mt-auto">Acessar {{ $titulo }}</a>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection

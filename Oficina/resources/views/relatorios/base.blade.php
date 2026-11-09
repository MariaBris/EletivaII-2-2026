@extends('layout')

@section('title', trim($__env->yieldContent('titulo-relatorio')) . ' - Mazotto')

@push('estilos')
<link rel="stylesheet" href="{{ asset('css/relatorios.css') }}?v=1">
@endpush

@section('content')
<div class="relatorio">
    <div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">@yield('titulo-relatorio')</h1>

        <div class="d-flex flex-wrap gap-2 nao-imprimir">
            @if(request()->routeIs('relatorios.index'))
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Voltar ao início</a>
            @else
            <a href="{{ route('relatorios.index') }}" class="btn btn-outline-dark">Voltar aos relatórios</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir página</button>
            @endif
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger nao-imprimir" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $mensagem)
            <li>{{ $mensagem }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @yield('conteudo-relatorio')

    @unless(request()->routeIs('relatorios.index'))
    <p class="small text-muted mt-3 nao-imprimir">A impressão inclui os registros da página atual. Os totais consideram todos os resultados do filtro. Datas exibidas no horário de Brasília.</p>
    @endunless
</div>
@endsection

@extends('layout')

@section('title', 'Cadastrar serviço - Mazotto')

@section('content')
<h1 class="h3 mb-4">Cadastrar serviço</h1>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('servicos.store') }}">
            @csrf
            @include('servicos.form', ['servico' => null])
        </form>
    </div>
</div>
@endsection

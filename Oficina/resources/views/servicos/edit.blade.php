@extends('layout')

@section('title', 'Editar serviço - Mazotto')

@section('content')
<h1 class="h3 mb-4">Editar serviço</h1>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('servicos.update', $servico->id) }}">
            @csrf
            @method('PUT')
            @include('servicos.form')
        </form>
    </div>
</div>
@endsection

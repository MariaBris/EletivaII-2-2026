@extends('layout')
@section('title', 'Editar veículo - Mazotto')
@section('content')
<h1 class="h3 mb-4">Editar veículo</h1>
{{-- Mensagens de sucesso, bloqueio e validação. --}}
@if(session('mensagem'))<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>@endif
@if(session('erro'))<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)<li>{{ $mensagem }}</li>@endforeach
    </ul>
</div>@endif
@if($usuarios->isEmpty())<div class="alert alert-warning">Cadastre uma conta com perfil CLI antes de cadastrar um veículo.</div>@endif
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('veiculos.update', $veiculo->id) }}">
            @csrf @method('PUT')
            {{-- Os campos correspondem às colunas do banco oficina. --}}
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="placa" class="form-label">Placa</label>
                    <input id="placa" name="placa" type="text" value="{{ old('placa', $veiculo->placa) }}" class="form-control" maxlength="10" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="marca" class="form-label">Marca</label>
                    <input id="marca" name="marca" type="text" value="{{ old('marca', $veiculo->marca) }}" class="form-control" maxlength="100">
                </div>
                <div class="col-12 col-md-6">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input id="modelo" name="modelo" type="text" value="{{ old('modelo', $veiculo->modelo) }}" class="form-control" maxlength="100">
                </div>
                <div class="col-12 col-md-6">
                    <label for="ano" class="form-label">Ano</label>
                    <input id="ano" name="ano" type="number" value="{{ old('ano', $veiculo->ano) }}" class="form-control" min="1886" max="2100" step="1">
                </div>
                <div class="col-12">
                    <label for="user_id" class="form-label">Cliente proprietário</label>
                    <select id="user_id" name="user_id" class="form-select" required>
                        <option value="">Selecione um cliente...</option>
                        @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected(old('user_id', $veiculo->user_id) == $usuario->id)>{{ $usuario->name }} — {{ $usuario->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('veiculos.index') }}" class="btn btn-outline-dark">Voltar</a>
                <button type="submit" class="btn btn-primary" @disabled($usuarios->isEmpty())>Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

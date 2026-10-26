@extends('layout')

@section('title', 'Cadastrar veículo - Mazotto')

@section('content')
<h1 class="h3 mb-4">Cadastrar veículo</h1>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('veiculos.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="placa" class="form-label">Placa</label>
                    <input id="placa" name="placa" type="text" value="{{ old('placa') }}" class="form-control" maxlength="10" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="marca" class="form-label">Marca</label>
                    <input id="marca" name="marca" type="text" value="{{ old('marca') }}" class="form-control" maxlength="100">
                </div>

                <div class="col-12 col-md-6">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input id="modelo" name="modelo" type="text" value="{{ old('modelo') }}" class="form-control" maxlength="100">
                </div>

                <div class="col-12 col-md-6">
                    <label for="ano" class="form-label">Ano</label>
                    <input id="ano" name="ano" type="number" value="{{ old('ano') }}" class="form-control" min="1886" max="2100" step="1">
                </div>

                <div class="col-12">
                    <label for="cliente_id" class="form-label">Cliente proprietário (opcional)</label>
                    <select id="cliente_id" name="cliente_id" class="form-select" aria-describedby="cliente-ajuda">
                        <option value="">Sem cliente cadastrado</option>
                        @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected(old('cliente_id')==$cliente->id)>{{ $cliente->nome }} — {{ $cliente->telefone ?? 'Telefone não informado' }}</option>
                        @endforeach
                    </select>
                    <p id="cliente-ajuda" class="form-text mb-0">Você pode cadastrar o veículo sem vincular um cliente e completar essa informação depois.</p>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('veiculos.index') }}" class="btn btn-outline-dark">Voltar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

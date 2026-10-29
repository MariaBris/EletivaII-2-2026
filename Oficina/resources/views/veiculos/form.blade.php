@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Os campos correspondem às colunas do banco oficina. --}}
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="placa" class="form-label">Placa</label>
        <input id="placa" name="placa" type="text" value="{{ old('placa', $veiculo->placa ?? '') }}" class="form-control" maxlength="10" required>
    </div>

    <div class="col-12 col-md-6">
        <label for="marca" class="form-label">Marca</label>
        <input id="marca" name="marca" type="text" value="{{ old('marca', $veiculo->marca ?? '') }}" class="form-control" maxlength="100">
    </div>

    <div class="col-12 col-md-6">
        <label for="modelo" class="form-label">Modelo</label>
        <input id="modelo" name="modelo" type="text" value="{{ old('modelo', $veiculo->modelo ?? '') }}" class="form-control" maxlength="100">
    </div>

    <div class="col-12 col-md-6">
        <label for="ano" class="form-label">Ano</label>
        <input id="ano" name="ano" type="number" value="{{ old('ano', $veiculo->ano ?? '') }}" class="form-control" min="1886" max="2100" step="1">
    </div>

    <div class="col-12">
        <label for="cliente_id" class="form-label">Cliente proprietário (opcional)</label>
        <select id="cliente_id" name="cliente_id" class="form-select" aria-describedby="cliente-ajuda">
            <option value="" @selected(blank(old('cliente_id', $veiculo->cliente_id ?? null)))>Sem cliente cadastrado</option>
            @foreach($clientes as $cliente)
            <option value="{{ $cliente->id }}" @selected(old('cliente_id', $veiculo->cliente_id ?? null) == $cliente->id)>{{ $cliente->nome }} — {{ $cliente->telefone ?? 'Telefone não informado' }}</option>
            @endforeach
        </select>

        @if(isset($veiculo))
        <p id="cliente-ajuda" class="form-text mb-0">A alteração do proprietário não modifica o cliente registrado nas ordens de serviço anteriores.</p>
        @else
        <p id="cliente-ajuda" class="form-text mb-0">Você pode cadastrar o veículo sem vincular um cliente e completar essa informação depois.</p>
        @endif
    </div>
</div>

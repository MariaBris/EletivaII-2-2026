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
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <label for="cliente_id" class="form-label mb-0">Cliente proprietário (opcional)</label>

            @if(!isset($veiculo))
            <a id="novo-cliente-veiculo" href="{{ route('clientes.create', ['origem' => 'veiculo', 'origem_veiculo' => $origem ?? null, 'agendamento_id' => $agendamentoRetornoId ?? null]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Novo cliente</a>
            @endif
        </div>

        <select id="cliente_id" name="cliente_id" class="form-select" aria-describedby="cliente-ajuda">
            <option value="" @selected(blank(old('cliente_id', $veiculo->cliente_id ?? request('cliente_id'))))>Sem cliente cadastrado</option>

            @foreach($clientes as $cliente)
            <option value="{{ $cliente->id }}" @selected(old('cliente_id', $veiculo->cliente_id ?? request('cliente_id')) == $cliente->id)>{{ $cliente->nome }} - {{ $cliente->telefone ?? 'Telefone não informado' }}</option>
            @endforeach
        </select>

        @if(isset($veiculo))
        <p id="cliente-ajuda" class="form-text mb-0">A alteração do proprietário não modifica o cliente registrado nas ordens de serviço anteriores.</p>
        @else
        <p id="cliente-ajuda" class="form-text mb-0">Você pode cadastrar o veículo sem vincular um cliente e completar essa informação depois.</p>
        @endif
    </div>
</div>

<hr class="my-4">

<h2 class="h5 mb-2">Informações de manutenção (opcional)</h2>
<p class="text-muted mb-3">Registre o que o cliente souber informar, inclusive manutenções realizadas em outra oficina. Data e quilometragem podem ser preenchidas separadamente.</p>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="quilometragem_informada" class="form-label">Quilometragem atual conhecida</label>
        <input id="quilometragem_informada" name="quilometragem_informada" type="number" value="{{ old('quilometragem_informada', $veiculo->quilometragem_informada ?? '') }}" class="form-control" min="0" max="4294967295" step="1">
    </div>

    <div class="col-12">
        <h3 class="h6 mb-0">Última troca de óleo informada</h3>
    </div>

    <div class="col-12 col-md-6">
        <label for="oleo_data_informada" class="form-label">Data da troca de óleo</label>
        <input id="oleo_data_informada" name="oleo_data_informada" type="date" value="{{ old('oleo_data_informada', isset($veiculo) ? $veiculo->oleo_data_informada?->format('Y-m-d') : '') }}" class="form-control" max="{{ now('America/Sao_Paulo')->toDateString() }}">
    </div>

    <div class="col-12 col-md-6">
        <label for="oleo_km_informado" class="form-label">Quilometragem na troca de óleo</label>
        <input id="oleo_km_informado" name="oleo_km_informado" type="number" value="{{ old('oleo_km_informado', $veiculo->oleo_km_informado ?? '') }}" class="form-control" min="0" max="4294967295" step="1">
    </div>

    <div class="col-12">
        <h3 class="h6 mb-0">Última revisão informada</h3>
    </div>

    <div class="col-12 col-md-6">
        <label for="revisao_data_informada" class="form-label">Data da revisão</label>
        <input id="revisao_data_informada" name="revisao_data_informada" type="date" value="{{ old('revisao_data_informada', isset($veiculo) ? $veiculo->revisao_data_informada?->format('Y-m-d') : '') }}" class="form-control" max="{{ now('America/Sao_Paulo')->toDateString() }}">
    </div>

    <div class="col-12 col-md-6">
        <label for="revisao_km_informado" class="form-label">Quilometragem na revisão</label>
        <input id="revisao_km_informado" name="revisao_km_informado" type="number" value="{{ old('revisao_km_informado', $veiculo->revisao_km_informado ?? '') }}" class="form-control" min="0" max="4294967295" step="1">
    </div>
</div>

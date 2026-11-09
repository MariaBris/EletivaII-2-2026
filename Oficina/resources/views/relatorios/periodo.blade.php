<form method="GET" action="{{ url()->current() }}" class="card border-0 shadow-sm mb-4 nao-imprimir">
    <div class="card-body row g-3 align-items-end">
        <div class="col-12 col-md-4">
            <label for="data_inicio" class="form-label">Conclusão a partir de</label>
            <input id="data_inicio" name="data_inicio" type="date" value="{{ old('data_inicio', request('data_inicio')) }}" class="form-control">
        </div>
        <div class="col-12 col-md-4">
            <label for="data_fim" class="form-label">Conclusão até</label>
            <input id="data_fim" name="data_fim" type="date" value="{{ old('data_fim', request('data_fim')) }}" class="form-control">
        </div>
        <div class="col-12 col-md-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary">Limpar</a>
        </div>
    </div>
</form>
<p class="small text-muted">Período de conclusão: {{ request('data_inicio') ?: 'Sem início definido' }} até {{ request('data_fim') ?: 'Sem fim definido' }}.</p>

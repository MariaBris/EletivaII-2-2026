<h2 class="h5 mb-3">Condições de entrada</h2>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <label for="quilometragem_entrada" class="form-label">Quilometragem de entrada (km)</label>
        <input id="quilometragem_entrada" name="quilometragem_entrada" type="number" value="{{ old('quilometragem_entrada') }}" class="form-control" min="0" max="4294967295" step="1" aria-describedby="quilometragem-ajuda">
        <div id="quilometragem-ajuda" class="form-text">Se não for possível conferir, deixe vazio. Zero significa 0 km.</div>
    </div>

    <div class="col-12 col-md-6">
        <label for="combustivel_entrada" class="form-label">Nível de combustível</label>
        <select id="combustivel_entrada" name="combustivel_entrada" class="form-select">
            <option value="">Não informado</option>
            @foreach(App\Models\Pedido::COMBUSTIVEIS as $valor => $titulo)
            <option value="{{ $valor }}" @selected(old('combustivel_entrada')===$valor)>{{ $titulo }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label for="avarias_entrada" class="form-label">Avarias observadas na entrada</label>
        <textarea id="avarias_entrada" name="avarias_entrada" class="form-control" rows="3" maxlength="5000" placeholder="Descreva riscos, amassados e a localização dos danos observados.">{{ old('avarias_entrada') }}</textarea>
    </div>
</div>

<h2 class="h5 mb-3">Fotos de entrada</h2>
<p class="form-text">Envie as quatro posições. Formatos: JPG, JPEG, PNG ou WEBP, até 15 MB por foto. Após um erro de validação ou ao sair desta página, selecione os arquivos novamente.</p>

<div class="row g-3 mb-4">
    @foreach(App\Models\FotoEntrada::POSICOES as $posicao => $titulo)
    <div class="col-12 col-md-6">
        <label for="foto-{{ $posicao }}" class="form-label">{{ $titulo }}</label>
        <input id="foto-{{ $posicao }}" name="fotos_entrada[{{ $posicao }}]" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" data-foto-entrada required>
        <img id="previa-{{ $posicao }}" alt="Prévia da foto: {{ $titulo }}" class="img-fluid rounded border mt-2" style="width: 100%; max-height: 230px; object-fit: contain;" hidden>
    </div>
    @endforeach
</div>

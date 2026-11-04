<div class="card border-0 shadow-sm mb-4 nao-imprimir">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Fotos de entrada</h2>

        @if($pedido->fotosEntrada->isEmpty())
        <p class="text-muted mb-0">Esta OS não possui fotos de entrada registradas.</p>
        @else
        <div class="row g-3">
            @foreach(App\Models\FotoEntrada::POSICOES as $posicao => $titulo)
            @php
            $foto = $pedido->fotosEntrada->firstWhere('posicao', $posicao);
            @endphp

            <div class="col-12 col-md-6">
                <h3 class="h6">{{ $titulo }}</h3>

                @if($foto)
                <a href="{{ route('pedidos.fotos.show', ['id' => $pedido->id, 'foto' => $foto->id]) }}" target="_blank" rel="noopener"><img src="{{ route('pedidos.fotos.show', ['id' => $pedido->id, 'foto' => $foto->id]) }}" alt="{{ $titulo }} do veículo na entrada da OS {{ $pedido->id }}" class="img-fluid rounded border" style="width: 100%; max-height: 250px; object-fit: contain;" loading="lazy"></a>
                <p class="form-text mb-0">Registrada em {{ $foto->created_at?->format('d/m/Y H:i') }} por {{ $foto->operador?->name ?? 'Não informado' }}.</p>
                @else
                <p class="text-muted">Foto não registrada.</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

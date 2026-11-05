@extends('layout')

@section('title', ($agendamento ? 'Editar agendamento' : 'Novo agendamento') . ' - Mazotto')

@section('content')
<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">{{ $agendamento ? 'Editar agendamento' : 'Novo agendamento' }}</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-agendamento" class="btn btn-primary" @disabled($veiculos->isEmpty())>Salvar</button>
        <a href="{{ $agendamento ? route('agendamentos.show', $agendamento->id) : route('agendamentos.index') }}" class="btn btn-secondary" data-cancelar-agendamento>Cancelar</a>
    </div>
</div>

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

@if($veiculos->isEmpty())
<div class="alert alert-warning">Cadastre um veículo pelo botão Novo veículo abaixo. Após salvar, você retornará com ele selecionado.</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-agendamento" data-recuperados="{{ session()->hasOldInput() ? '1' : '0' }}" data-rascunho="mazotto.agendamento.veiculo.{{ auth()->id() }}.{{ $agendamento?->id ?? 'novo' }}" method="POST" action="{{ $agendamento ? route('agendamentos.update', $agendamento->id) : route('agendamentos.store') }}">
            @csrf

            @if($agendamento)
            @method('PUT')
            @endif

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <label for="veiculo_id" class="form-label mb-0">Veículo</label>
                    <a id="novo-veiculo-agendamento" href="{{ route('veiculos.create', ['origem' => 'agendamento', 'agendamento_id' => $agendamento?->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Novo veículo</a>
                </div>

                <select id="veiculo_id" name="veiculo_id" class="form-select" required>
                    <option value="">Selecione o veículo...</option>

                    @foreach($veiculos as $veiculo)
                    <option value="{{ $veiculo->id }}" @selected((string) old('veiculo_id', request('veiculo_id', $agendamento?->veiculo_id)) === (string) $veiculo->id)>{{ $veiculo->placa }} — {{ trim(($veiculo->marca ?? '') . ' ' . ($veiculo->modelo ?? '')) }} — {{ $veiculo->cliente?->nome ?? 'Sem cliente cadastrado' }}</option>
                    @endforeach
                </select>

                <p class="form-text mb-0">O cliente vem do cadastro do veículo. Após cadastrar um novo veículo, você retorna a este formulário com ele selecionado e os dados preenchidos preservados.</p>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="data" class="form-label">Data</label>
                    <input id="data" name="data" type="date" class="form-control" value="{{ old('data', $agendamento?->data_hora?->format('Y-m-d') ?? request('data', now('America/Sao_Paulo')->toDateString())) }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="hora" class="form-label">Horário</label>
                    <input id="hora" name="hora" type="time" class="form-control" value="{{ old('hora', $agendamento?->data_hora?->format('H:i')) }}" step="60" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="motivo" class="form-label">Motivo do atendimento / relato do cliente</label>
                <textarea id="motivo" name="motivo" class="form-control" rows="4" maxlength="5000" required>{{ old('motivo', $agendamento?->motivo) }}</textarea>
            </div>

            <p class="text-muted mb-0">O agendamento reserva a data e o horário. Quando o cliente chegar, use Abrir OS na consulta do agendamento.</p>
        </form>
    </div>
</div>

<script>
    (() => {
        const formulario = document.getElementById('formulario-agendamento');
        const botao = document.getElementById('novo-veiculo-agendamento');
        const chave = formulario.dataset.rascunho;
        const nomes = ['veiculo_id', 'data', 'hora', 'motivo'];
        const parametros = new URLSearchParams(window.location.search);
        const novoVeiculo = parametros.get('veiculo_id');
        const recuperados = formulario.dataset.recuperados === '1';

        if (parametros.get('retomar') === '1') {
            try {
                const salvo = sessionStorage.getItem(chave);

                if (salvo && !recuperados) {
                    const dados = JSON.parse(salvo);

                    for (const nome of nomes) {
                        if (nome === 'veiculo_id' && novoVeiculo) continue;

                        const campo = formulario.elements.namedItem(nome);

                        if (campo && typeof dados[nome] === 'string') {
                            campo.value = dados[nome];
                        }
                    }
                }

                sessionStorage.removeItem(chave);
            } catch (erro) {
                // O formulário continua disponível se o navegador bloquear o armazenamento.
            }
        }

        if (novoVeiculo && !recuperados) {
            formulario.elements.namedItem('veiculo_id').value = novoVeiculo;
        }

        botao.addEventListener('click', evento => {
            const dados = {};

            for (const nome of nomes) {
                dados[nome] = formulario.elements.namedItem(nome).value;
            }

            try {
                sessionStorage.setItem(chave, JSON.stringify(dados));
            } catch (erro) {
                evento.preventDefault();
                alert('Não foi possível guardar o preenchimento. Confira se o navegador permite o armazenamento desta página.');
            }
        });

        function limparRascunho() {
            try {
                sessionStorage.removeItem(chave);
            } catch (erro) {
                // A navegação continua mesmo se o armazenamento estiver indisponível.
            }
        }

        formulario.addEventListener('submit', limparRascunho);

        document.querySelector('[data-cancelar-agendamento]')
            ?.addEventListener('click', limparRascunho);
    })();
</script>
@endsection

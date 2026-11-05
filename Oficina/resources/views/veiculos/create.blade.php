@extends('layout')

@section('title', 'Cadastrar veículo - Mazotto')

@section('content')
@php
$origem = old('origem', request()->query('origem'));
$retornarParaOs = $origem === 'os';
$retornarParaAgendamento = $origem === 'agendamento';

$agendamentoRetornoId = filter_var(
old('agendamento_id', request()->query('agendamento_id')),
FILTER_VALIDATE_INT,
['options' => ['min_range' => 1]]
) ?: null;

$rotaCancelar = route('veiculos.index');

if ($retornarParaOs) {
$rotaCancelar = route('pedidos.create', ['retomar' => 1]);
} elseif ($retornarParaAgendamento) {
$rotaCancelar = $agendamentoRetornoId
? route('agendamentos.edit', ['agendamento' => $agendamentoRetornoId, 'retomar' => 1])
: route('agendamentos.create', ['retomar' => 1]);
}
@endphp

<div class="barra-acoes d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Cadastrar veículo</h1>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" form="formulario-veiculo" class="btn btn-primary">Salvar</button>
        <a href="{{ $rotaCancelar }}" class="btn btn-secondary" data-cancelar-veiculo>Cancelar</a>
    </div>
</div>

@if(session('mensagem'))
<div class="alert alert-success" role="status">{{ session('mensagem') }}</div>
@endif

@if(session('erro'))
<div class="alert alert-danger" role="alert">{{ session('erro') }}</div>
@endif

@if($retornarParaOs)
<div class="alert alert-info">Após salvar, você retornará à ordem de serviço com este veículo selecionado.</div>
@elseif($retornarParaAgendamento)
<div class="alert alert-info">Após salvar, você retornará ao agendamento com este veículo selecionado.</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="formulario-veiculo" data-recuperados="{{ session()->hasOldInput() ? '1' : '0' }}" data-rascunho="mazotto.veiculo.cliente.{{ auth()->id() }}.{{ $origem ?? 'direto' }}.{{ $agendamentoRetornoId ?? 'novo' }}" method="POST" action="{{ route('veiculos.store') }}">
            @csrf

            @if($retornarParaOs || $retornarParaAgendamento)
            <input type="hidden" name="origem" value="{{ $origem }}">
            @endif

            @if($retornarParaAgendamento && $agendamentoRetornoId)
            <input type="hidden" name="agendamento_id" value="{{ $agendamentoRetornoId }}">
            @endif

            @include('veiculos.form', ['veiculo' => null])
        </form>
    </div>
</div>

<script>
    (() => {
        const formulario = document.getElementById('formulario-veiculo');
        const botao = document.getElementById('novo-cliente-veiculo');
        const chave = formulario.dataset.rascunho;

        const nomes = [
            'placa',
            'marca',
            'modelo',
            'ano',
            'cliente_id',
            'quilometragem_informada',
            'oleo_data_informada',
            'oleo_km_informado',
            'revisao_data_informada',
            'revisao_km_informado'
        ];

        const parametros = new URLSearchParams(window.location.search);
        const novoCliente = parametros.get('cliente_id');
        const recuperados = formulario.dataset.recuperados === '1';

        if (parametros.get('retomar_cliente') === '1') {
            try {
                const salvo = sessionStorage.getItem(chave);

                if (salvo && !recuperados) {
                    const dados = JSON.parse(salvo);

                    for (const nome of nomes) {
                        if (nome === 'cliente_id' && novoCliente) continue;

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

        if (novoCliente && !recuperados) {
            formulario.elements.namedItem('cliente_id').value = novoCliente;
        }

        botao?.addEventListener('click', evento => {
            const dados = {};

            for (const nome of nomes) {
                const campo = formulario.elements.namedItem(nome);

                if (campo) {
                    dados[nome] = campo.value;
                }
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
        document.querySelector('[data-cancelar-veiculo]')?.addEventListener('click', limparRascunho);
    })();
</script>
@endsection

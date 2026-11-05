@php
$enderecoCliente = isset($cliente) ? $cliente->endereco : null;
@endphp

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $mensagem)
        <li>{{ $mensagem }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="mb-3">
    <label for="nome" class="form-label">Nome / razão social</label>
    <input id="nome" name="nome" type="text" value="{{ old('nome', $cliente->nome ?? '') }}" class="form-control" maxlength="255" required>
</div>

<div class="mb-3">
    <label for="telefone" class="form-label">Telefone para contato</label>
    <input id="telefone" name="telefone" type="tel" value="{{ old('telefone', $cliente->telefone ?? '') }}" class="form-control" maxlength="20" placeholder="DDD e telefone" required>
</div>

<div class="mb-3">
    <label for="email" class="form-label">E-mail (opcional)</label>
    <input id="email" name="email" type="email" value="{{ old('email', $cliente->email ?? '') }}" class="form-control" maxlength="255">
</div>

<div class="mb-3">
    <label for="cpf_cnpj" class="form-label">CPF/CNPJ (opcional)</label>
    <input id="cpf_cnpj" name="cpf_cnpj" type="text" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj_formatado ?? '') }}" class="form-control" maxlength="18" placeholder="CPF ou CNPJ, com ou sem pontuação">
</div>

<details class="border rounded p-3 mt-4" @if($enderecoCliente || $errors->any() || old('endereco') || old('cep')) open @endif>
    <summary class="fw-bold">Endereço (opcional)</summary>

    <p class="form-text mt-2">Digite o CEP para buscar o endereço ou preencha manualmente. O endereço não é obrigatório.</p>

    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6">
            <label for="cep" class="form-label">CEP</label>

            <div class="input-group">
                <input id="cep" name="cep" type="text" value="{{ old('cep', $enderecoCliente?->cep) }}" class="form-control" maxlength="9" placeholder="00000-000" autocomplete="postal-code" inputmode="numeric" aria-describedby="aviso-cep">
                <button id="buscar-cep" type="button" class="btn btn-outline-primary">Buscar CEP</button>
            </div>

            <p id="aviso-cep" class="form-text mb-0" role="status" aria-live="polite">A busca será feita ao completar os 8 dígitos.</p>
        </div>

        <div class="col-12 col-md-8">
            <label for="endereco" class="form-label">Rua / avenida</label>
            <input id="endereco" name="endereco" type="text" value="{{ old('endereco', $enderecoCliente?->endereco) }}" class="form-control" maxlength="255" autocomplete="address-line1">
        </div>

        <div class="col-12 col-md-4">
            <label for="numero" class="form-label">Número</label>
            <input id="numero" name="numero" type="text" value="{{ old('numero', $enderecoCliente?->numero) }}" class="form-control" maxlength="20" placeholder="Número ou s/n">
        </div>

        <div class="col-12 col-md-6">
            <label for="complemento" class="form-label">Complemento</label>
            <input id="complemento" name="complemento" type="text" value="{{ old('complemento', $enderecoCliente?->complemento) }}" class="form-control" maxlength="100" autocomplete="address-line2">
        </div>

        <div class="col-12 col-md-6">
            <label for="bairro" class="form-label">Bairro</label>
            <input id="bairro" name="bairro" type="text" value="{{ old('bairro', $enderecoCliente?->bairro) }}" class="form-control" maxlength="100">
        </div>

        <div class="col-12 col-md-6">
            <label for="cidade" class="form-label">Cidade</label>
            <input id="cidade" name="cidade" type="text" value="{{ old('cidade', $enderecoCliente?->cidade) }}" class="form-control" maxlength="255" autocomplete="address-level2">
        </div>

        <div class="col-12 col-md-3">
            <label for="estado" class="form-label">UF</label>
            <select id="estado" name="estado" class="form-select" autocomplete="address-level1">
                <option value="">Não informada</option>

                @foreach(App\Models\Endereco::UFS as $uf)
                <option value="{{ $uf }}" @selected(old('estado', $enderecoCliente?->estado) === $uf)>{{ $uf }}</option>
                @endforeach
            </select>
        </div>
    </div>
</details>

<script>
    (() => {
        const cep = document.getElementById('cep');
        const botao = document.getElementById('buscar-cep');
        const aviso = document.getElementById('aviso-cep');

        const campos = {
            endereco: document.getElementById('endereco'),
            bairro: document.getElementById('bairro'),
            cidade: document.getElementById('cidade'),
            estado: document.getElementById('estado')
        };

        let controle = null;
        let numeroConsulta = 0;

        // Identifica alterações manuais feitas enquanto a consulta estiver carregando.
        const alteracoes = {
            endereco: 0,
            bairro: 0,
            cidade: 0,
            estado: 0
        };

        for (const nome in campos) {
            campos[nome].addEventListener('input', () => {
                alteracoes[nome]++;
            });

            campos[nome].addEventListener('change', () => {
                alteracoes[nome]++;
            });
        }

        function somenteNumeros() {
            return cep.value.replace(/\D/g, '');
        }

        function cancelarConsulta() {
            numeroConsulta++;

            if (controle) {
                controle.abort();
                controle = null;
            }

            botao.disabled = false;
            botao.textContent = 'Buscar CEP';
        }

        async function buscarCep() {
            const numeroCep = somenteNumeros();

            if (numeroCep.length !== 8) {
                aviso.textContent = 'Informe um CEP com 8 dígitos ou preencha o endereço manualmente.';
                return;
            }

            cancelarConsulta();

            const consultaAtual = numeroConsulta;
            const controleAtual = new AbortController();
            controle = controleAtual;

            const alteracoesIniciais = {
                ...alteracoes
            };

            botao.disabled = true;
            botao.textContent = 'Buscando...';
            aviso.textContent = 'Consultando o CEP...';

            const limiteTempo = setTimeout(() => {
                controleAtual.abort();
            }, 10000);

            try {
                const resposta = await fetch(
                    `https://viacep.com.br/ws/${numeroCep}/json/`, {
                        signal: controleAtual.signal
                    }
                );

                if (!resposta.ok) {
                    throw new Error('Falha na consulta.');
                }

                const dados = await resposta.json();

                // Ignora respostas antigas caso o CEP tenha sido alterado.
                if (consultaAtual !== numeroConsulta || somenteNumeros() !== numeroCep) {
                    return;
                }

                if (dados.erro) {
                    aviso.textContent = 'CEP não encontrado. Confira o número ou preencha o endereço manualmente. Os dados existentes foram mantidos.';
                    return;
                }

                const valores = {
                    endereco: dados.logradouro,
                    bairro: dados.bairro,
                    cidade: dados.localidade,
                    estado: dados.uf
                };

                let houveEdicaoManual = false;

                for (const nome in campos) {
                    // Preserva o que foi alterado manualmente durante a busca.
                    if (alteracoes[nome] !== alteracoesIniciais[nome]) {
                        houveEdicaoManual = true;
                        continue;
                    }

                    const campo = campos[nome];
                    const valor = typeof valores[nome] === 'string' ? valores[nome] : '';

                    campo.value = valor.slice(0, campo.maxLength > 0 ? campo.maxLength : valor.length);
                    campo.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    campo.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                aviso.textContent = houveEdicaoManual ?
                    'Consulta concluída. Suas alterações manuais foram mantidas. Confira o endereço e complete número e complemento.' :
                    'Consulta concluída. Confira o endereço e complete os campos que faltarem, incluindo número e complemento.';
            } catch (erro) {
                if (consultaAtual !== numeroConsulta || somenteNumeros() !== numeroCep) {
                    return;
                }

                aviso.textContent = 'Não foi possível consultar o CEP. Tente novamente ou preencha manualmente. Os dados existentes foram mantidos.';
            } finally {
                clearTimeout(limiteTempo);

                if (consultaAtual === numeroConsulta) {
                    controle = null;
                    botao.disabled = false;
                    botao.textContent = 'Buscar CEP';
                }
            }
        }

        cep.addEventListener('input', () => {
            cancelarConsulta();

            const numeros = somenteNumeros().slice(0, 8);

            cep.value = numeros.length > 5 ?
                numeros.slice(0, 5) + '-' + numeros.slice(5) :
                numeros;

            if (numeros.length === 8) {
                buscarCep();
            } else {
                aviso.textContent = 'A busca será feita ao completar os 8 dígitos. Se mudar o CEP, confira também o endereço.';
            }
        });

        botao.addEventListener('click', buscarCep);
    })();
</script>

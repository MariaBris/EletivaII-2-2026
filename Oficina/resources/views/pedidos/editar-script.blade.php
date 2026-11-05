<script>
    (() => {
        const formulario = document.getElementById('formulario-editar-os') || document.getElementById('formulario-os');

        if (!formulario) return;

        document.querySelectorAll('[data-limpar-checklist]').forEach(botao => {
            botao.addEventListener('click', () => {
                botao.closest('[data-checklist-linha]').querySelectorAll('input[type="radio"]').forEach(campo => {
                    campo.checked = false;
                    campo.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });

        const criando = formulario.id === 'formulario-os';
        const modelo = document.getElementById('modelo-nova-inclusao');
        const aviso = document.getElementById('aviso-itens');
        const anteriores = JSON.parse(document.getElementById('novas-inclusoes-anteriores').textContent);
        const grupos = [];
        const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
        const limiteTotal = 9999999999;
        const quantidadesSalvas = [...document.querySelectorAll('[data-quantidade-orcamento]')];
        const chavePosicao = 'mazotto.os.edicao.posicao:' + window.location.pathname;

        function centavos(texto) {
            const [inteiro, decimal = ''] = String(texto).split('.');

            return Number(inteiro) * 100 + Number(decimal.padEnd(2, '0'));
        }

        function informar(texto) {
            if (!aviso) return;

            aviso.textContent = texto;
            aviso.classList.remove('d-none');
            aviso.focus();
        }

        function atualizarTotais() {
            let orcamento = centavos(formulario.dataset.orcamento);
            let autorizado = centavos(formulario.dataset.autorizado);

            for (const grupo of grupos) {
                grupo.vazio.hidden = grupo.itens.size > 0;

                for (const item of grupo.itens.values()) {
                    const quantidade = Number(item.entrada.value);

                    const subtotal = Number.isInteger(quantidade) && quantidade > 0
                        ? quantidade * item.preco
                        : 0;

                    item.linha.querySelector('[data-subtotal]').textContent = moeda.format(subtotal / 100);
                    orcamento += subtotal;
                }
            }

            for (const campo of quantidadesSalvas) {
                const original = Number(campo.dataset.original);
                const quantidade = Number(campo.value);
                const preco = centavos(campo.dataset.preco);

                const valida = Number.isInteger(quantidade)
                    && quantidade > 0
                    && quantidade <= 1000000;

                const subtotal = valida
                    ? quantidade * preco
                    : original * preco;

                const previa = document.getElementById(campo.dataset.previa);

                if (previa) {
                    previa.textContent = moeda.format(subtotal / 100);
                }

                orcamento += subtotal - original * preco;

                if (valida && quantidade !== original && campo.dataset.autorizado === '1') {
                    autorizado -= original * preco;
                }
            }

            document.getElementById('total-orcamento-previsto').textContent = moeda.format(orcamento / 100);
            document.getElementById('total-autorizado-previsto').textContent = moeda.format(autorizado / 100);

            return orcamento;
        }

        let conferirVeiculo = () => {};
        let rascunho = null;

        const parametros = new URLSearchParams(window.location.search);
        const chaveRascunho = formulario.dataset.rascunho;

        if (criando) {
            const busca = document.getElementById('veiculo-busca');
            const veiculoId = document.getElementById('veiculo_id');
            const veiculos = [...document.querySelectorAll('#lista-veiculos option')];

            const normalizar = texto => texto.normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim()
                .toLowerCase();

            if (parametros.get('retomar') === '1') {
                try {
                    const salvo = sessionStorage.getItem(chaveRascunho);

                    if (salvo) {
                        rascunho = JSON.parse(salvo);
                    }
                } catch (erro) {
                    informar('Não foi possível recuperar o preenchimento anterior. Confira os campos.');
                }
            }

            if (rascunho && Array.isArray(rascunho.campos)) {
                anteriores.produtos = [];
                anteriores.servicos = [];

                for (const [nome, valor] of rascunho.campos) {
                    if (['responsavel', 'observacoes', 'quilometragem_entrada', 'combustivel_entrada', 'avarias_entrada'].includes(nome)) {
                        const campo = formulario.elements.namedItem(nome);

                        if (campo) {
                            campo.value = valor;
                        }
                    }

                    if (!parametros.get('veiculo_id')) {
                        if (nome === 'veiculo_busca') busca.value = valor;
                        if (nome === 'veiculo_id') veiculoId.value = valor;
                    }

                    if (nome.startsWith('checklist[')) {
                        for (const campo of formulario.elements) {
                            if (campo.name !== nome) continue;
                            if (campo.type === 'radio') campo.checked = campo.value === valor;
                            else campo.value = valor;
                        }
                    }

                    const item = nome.match(/^(produtos|servicos)\[(\d+)\]\[(produto_id|servico_id|quantidade)\]$/);

                    if (item) {
                        const [, tipo, indice, campo] = item;

                        if (!anteriores[tipo][indice]) {
                            anteriores[tipo][indice] = {};
                        }

                        anteriores[tipo][indice][campo] = valor;
                    }
                }
            }

            conferirVeiculo = () => {
                const texto = normalizar(busca.value);

                const encontrado = veiculos.find(opcao =>
                    normalizar(opcao.value) === texto
                    || normalizar(opcao.dataset.placa) === texto
                );

                veiculoId.value = encontrado ? encontrado.dataset.id : '';

                busca.setCustomValidity(
                    encontrado ? '' : 'Escolha um veículo cadastrado entre as sugestões.'
                );
            };

            busca.addEventListener('input', conferirVeiculo);
            busca.addEventListener('change', conferirVeiculo);
            conferirVeiculo();

            document.getElementById('cadastrar-veiculo-os')?.addEventListener('click', evento => {
                const temFotos = [...formulario.querySelectorAll('[data-foto-entrada]')]
                    .some(campo => campo.files.length > 0);

                if (
                    temFotos
                    && !confirm('Os textos e itens serão preservados, mas as fotos precisarão ser selecionadas novamente ao retornar. Continuar?')
                ) {
                    evento.preventDefault();
                    return;
                }

                const selecoes = {};

                for (const grupo of grupos) {
                    selecoes[grupo.tipo] = {
                        id: grupo.selecao.value,
                        quantidade: grupo.quantidade.value,
                    };
                }

                try {
                    sessionStorage.setItem(chaveRascunho, JSON.stringify({
                        campos: [...new FormData(formulario).entries()]
                            .filter(([nome, valor]) => nome !== '_token' && typeof valor === 'string'),
                        temFotos,
                        selecoes,
                    }));
                } catch (erro) {
                    evento.preventDefault();
                    informar('Não foi possível guardar o preenchimento. Permaneça nesta tela para não perder os dados.');
                }
            });
        }

        for (const bloco of document.querySelectorAll('[data-grupo]')) {
            const grupo = {
                tipo: bloco.dataset.grupo,
                campo: bloco.dataset.campo,
                selecao: bloco.querySelector('[data-selecao]'),
                quantidade: bloco.querySelector('[data-quantidade-nova]'),
                corpo: bloco.querySelector('[data-itens]'),
                vazio: bloco.querySelector('[data-vazio]'),
                itens: new Map(),
                indice: 0,
            };

            grupos.push(grupo);

            function adicionar(id, quantidade, restaurando = false) {
                const opcao = [...grupo.selecao.options].find(item =>
                    item.value && item.value === String(id)
                );

                if (!opcao) {
                    informar('Um item anterior não está mais disponível. Confira as novas inclusões.');
                    return false;
                }

                const numero = Number(quantidade);

                if (!restaurando && (!Number.isInteger(numero) || numero < 1 || numero > 1000000)) {
                    informar('Informe uma quantidade inteira entre 1 e 1.000.000.');
                    return false;
                }

                const existente = grupo.itens.get(opcao.value);

                if (existente) {
                    const soma = Number(existente.entrada.value) + numero;

                    if (!Number.isInteger(soma) || soma < 1 || soma > 1000000) {
                        informar('Confira a quantidade total desse item.');
                        return false;
                    }

                    existente.entrada.value = soma;
                    atualizarTotais();

                    return true;
                }

                if (grupo.itens.size >= 100) {
                    informar('Inclua no máximo 100 tipos de peças e 100 tipos de serviços por atualização.');
                    return false;
                }

                const linha = modelo.content.firstElementChild.cloneNode(true);
                const indice = grupo.indice++;
                const identificador = linha.querySelector('[data-identificador]');
                const entrada = linha.querySelector('[data-quantidade]');
                const preco = centavos(opcao.dataset.preco);

                linha.querySelector('[data-nome]').textContent = opcao.dataset.nome;
                linha.querySelector('[data-preco]').textContent = moeda.format(preco / 100);

                identificador.name = `${grupo.tipo}[${indice}][${grupo.campo}]`;
                identificador.value = opcao.value;
                identificador.setAttribute('form', formulario.id);
                entrada.setAttribute('form', formulario.id);

                entrada.id = `${grupo.tipo}-item-${indice}`;
                entrada.name = `${grupo.tipo}[${indice}][quantidade]`;
                entrada.value = quantidade;
                entrada.max = Math.min(
                    1000000,
                    preco > 0 ? Math.floor(limiteTotal / preco) : 1000000
                );

                entrada.addEventListener('input', atualizarTotais);

                linha.querySelector('[data-rotulo]').htmlFor = entrada.id;
                linha.querySelector('[data-rotulo]').textContent = `Quantidade de ${opcao.dataset.nome}`;

                linha.querySelector('[data-remover]').addEventListener('click', () => {
                    grupo.itens.delete(opcao.value);
                    linha.remove();
                    grupo.vazio.hidden = grupo.itens.size > 0;
                    atualizarTotais();
                });

                grupo.itens.set(opcao.value, { entrada, linha, preco });
                grupo.corpo.append(linha);
                grupo.vazio.hidden = true;

                atualizarTotais();

                return true;
            }

            bloco.querySelector('[data-adicionar]').addEventListener('click', () => {
                if (!grupo.selecao.value) {
                    informar('Selecione um item antes de adicionar.');
                    return;
                }

                if (!grupo.quantidade.reportValidity()) return;
                if (!adicionar(grupo.selecao.value, grupo.quantidade.value)) return;

                grupo.selecao.value = '';
                grupo.quantidade.value = 1;
            });

            for (const item of Object.values(anteriores[grupo.tipo] ?? {})) {
                adicionar(item?.[grupo.campo], item?.quantidade ?? '', true);
            }
        }

        if (rascunho) {
            for (const grupo of grupos) {
                const pendente = rascunho.selecoes?.[grupo.tipo];

                if (pendente) {
                    grupo.selecao.value = pendente.id;
                    grupo.quantidade.value = pendente.quantidade;
                }
            }

            if (rascunho.temFotos) {
                informar('Preenchimento recuperado. Se quiser incluir as fotos que havia selecionado, selecione esses arquivos novamente.');
            }

            try {
                sessionStorage.removeItem(chaveRascunho);
            } catch (erro) {
                // O formulário recuperado continua disponível na tela.
            }
        }

        quantidadesSalvas.forEach(campo => campo.addEventListener('input', atualizarTotais));
        atualizarTotais();

        formulario.addEventListener('submit', evento => {
            conferirVeiculo();

            if (!formulario.reportValidity()) {
                evento.preventDefault();
                return;
            }

            if (quantidadesSalvas.some(campo => Number(campo.value) !== Number(campo.dataset.original))) {
                evento.preventDefault();
                informar('Salve primeiro a quantidade alterada no item registrado, ou retorne ao valor original.');
                return;
            }

            if (atualizarTotais() > limiteTotal) {
                evento.preventDefault();
                informar('O orçamento ultrapassa o limite permitido. Confira as quantidades.');
                return;
            }

            if (grupos.some(grupo => grupo.selecao.value)) {
                evento.preventDefault();
                informar('Há um item selecionado que ainda não foi adicionado.');
                return;
            }

        });

        formulario.querySelectorAll('[data-foto-entrada]').forEach(campo => {
            const previa = document.getElementById('previa-' + campo.id.replace('foto-', ''));
            let endereco = null;

            campo.addEventListener('change', () => {
                if (endereco) {
                    URL.revokeObjectURL(endereco);
                    endereco = null;
                }

                previa.hidden = true;
                previa.removeAttribute('src');
                campo.setCustomValidity('');

                const arquivo = campo.files[0];

                if (!arquivo) return;

                if (arquivo.size > 15 * 1024 * 1024) {
                    campo.setCustomValidity('Cada foto deve ter no máximo 15 MB.');
                    campo.reportValidity();
                    return;
                }

                endereco = URL.createObjectURL(arquivo);
                previa.src = endereco;
                previa.hidden = false;
            });

            window.addEventListener('beforeunload', () => {
                if (endereco) URL.revokeObjectURL(endereco);
            });
        });

        const selecionarTodos = document.getElementById('selecionar-todos-itens');
        const selecoes = [...document.querySelectorAll('[data-selecao-item]')];
        const botaoLote = document.getElementById('botao-iniciar-lote');
        const botaoFinalizar = document.getElementById('botao-finalizar-lote');

        function atualizarSelecao() {
            const marcados = selecoes.filter(campo => campo.checked);
            const quantidade = marcados.length;
            const iniciaveis = marcados.filter(campo => campo.dataset.situacao === 'PENDENTE').length;
            const executaveis = marcados.filter(campo => campo.dataset.situacao === 'EM_EXECUCAO').length;

            if (botaoLote) {
                botaoLote.disabled = iniciaveis === 0;
                botaoLote.textContent = `Autorizar e iniciar selecionados (${iniciaveis})`;
            }

            if (botaoFinalizar) {
                botaoFinalizar.disabled = executaveis === 0;
                botaoFinalizar.textContent = `Marcar selecionados como executados (${executaveis})`;
            }

            if (selecionarTodos) {
                selecionarTodos.disabled = selecoes.length === 0;
                selecionarTodos.checked = selecoes.length > 0 && quantidade === selecoes.length;
                selecionarTodos.indeterminate = quantidade > 0 && quantidade < selecoes.length;
            }
        }

        selecionarTodos?.addEventListener('change', () => {
            selecoes.forEach(campo => campo.checked = selecionarTodos.checked);
            atualizarSelecao();
        });

        selecoes.forEach(campo => campo.addEventListener('change', atualizarSelecao));
        atualizarSelecao();

        function assinaturaFormulario() {
            return JSON.stringify(
                [...new FormData(formulario).entries()].map(([nome, valor]) => [
                    nome,
                    typeof valor === 'string' ? valor : [valor.name, valor.size, valor.type],
                ])
            );
        }

        const assinaturaInicial = assinaturaFormulario();
        const recuperados = formulario.dataset.recuperados === '1';

        function guardarPosicao(alvo) {
            try {
                sessionStorage.setItem(chavePosicao, JSON.stringify({
                    alvo: alvo?.id,
                    deslocamento: alvo ? alvo.getBoundingClientRect().top : 0,
                    rolagem: window.scrollY,
                }));
            } catch (erro) {
                // A ação continua mesmo se não for possível guardar a posição.
            }
        }

        document.querySelectorAll('[data-acao-item]').forEach(formularioAcao => {
            formularioAcao.addEventListener('submit', evento => {
                let alvo = document.getElementById(formularioAcao.dataset.alvo);
                let mensagem = formularioAcao.dataset.confirmacao || '';
                let selecaoEnvio = null;

                if (formularioAcao.hasAttribute('data-lote')) {
                    const finalizando = evento.submitter?.dataset.acaoLote === 'finalizar';
                    const situacao = finalizando ? 'EM_EXECUCAO' : 'PENDENTE';
                    const marcados = selecoes.filter(campo => campo.checked && campo.dataset.situacao === situacao);
                    selecaoEnvio = marcados;

                    if (!marcados.length) {
                        evento.preventDefault();
                        return;
                    }

                    alvo = marcados[0].closest('tr');
                    mensagem = finalizando
                        ? `Confirma que os ${marcados.length} trabalhos selecionados foram executados? A OS será concluída automaticamente se não houver outros itens a resolver.`
                        : `Confirma que o cliente liberou os ${marcados.length} itens selecionados para execução? Eles serão autorizados e iniciados, com baixa no estoque das peças.`;
                }

                for (const nome of ['motivo_cancelamento', 'motivo_interrupcao']) {
                    const campo = formularioAcao.elements.namedItem(nome);

                    if (campo && !campo.value.trim()) {
                        evento.preventDefault();
                        campo.setCustomValidity('Informe o motivo.');
                        campo.reportValidity();
                        return;
                    }
                }

                const devolucao = formularioAcao.elements.namedItem('quantidade_devolvida');
                const confirmacao = formularioAcao.elements.namedItem('confirmar_devolucao');

                if (devolucao && Number(devolucao.value) > 0 && !confirmacao?.checked) {
                    evento.preventDefault();
                    alert('Confirme que as peças devolvidas podem retornar ao estoque.');
                    return;
                }

                const temNovosItens = grupos.some(grupo =>
                    grupo.itens.size > 0 || grupo.selecao.value
                );

                const temFotos = [...formulario.querySelectorAll('[data-foto-entrada]')]
                    .some(campo => campo.files.length > 0);

                const outrasQuantidades = quantidadesSalvas.some(campo =>
                    campo.form !== formularioAcao
                    && Number(campo.value) !== Number(campo.dataset.original)
                );

                if (recuperados || assinaturaFormulario() !== assinaturaInicial || temNovosItens || temFotos || outrasQuantidades) {
                    evento.preventDefault();
                    informar('Salve primeiro as alterações da OS e das quantidades antes de usar os botões dos itens. Seu preenchimento foi mantido.');
                    return;
                }

                if (mensagem && !confirm(mensagem)) {
                    evento.preventDefault();
                    return;
                }

                if (selecaoEnvio) {
                    formularioAcao.querySelectorAll('[data-lote-envio]').forEach(campo => campo.remove());
                    selecaoEnvio.forEach(campo => {
                        const entrada = document.createElement('input');
                        entrada.type = 'hidden';
                        entrada.name = 'selecionados[]';
                        entrada.value = campo.value;
                        entrada.dataset.loteEnvio = '1';
                        formularioAcao.append(entrada);
                    });
                }

                guardarPosicao(alvo);
            });
        });

        document.querySelectorAll('textarea[name="motivo_cancelamento"], textarea[name="motivo_interrupcao"]').forEach(campo => {
            campo.addEventListener('input', () => campo.setCustomValidity(''));
        });

        function restaurarPosicao() {
            let posicao;

            try {
                const salvo = sessionStorage.getItem(chavePosicao);

                if (!salvo) return;

                sessionStorage.removeItem(chavePosicao);
                posicao = JSON.parse(salvo);
            } catch (erro) {
                return;
            }

            requestAnimationFrame(() => {
                const alvo = document.getElementById(posicao.alvo);

                const destino = alvo
                    ? window.scrollY + alvo.getBoundingClientRect().top - posicao.deslocamento
                    : posicao.rolagem;

                if (Number.isFinite(destino)) {
                    window.scrollTo(0, Math.max(0, destino));
                }
            });
        }

        if (document.readyState === 'complete') {
            restaurarPosicao();
        } else {
            window.addEventListener('load', restaurarPosicao, { once: true });
        }
    })();
</script>

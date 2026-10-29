<script>
    (() => {
        const formulario = document.getElementById('formulario-pesquisa');
        const resultados = document.getElementById('resultados-pesquisa');

        if (!formulario || !resultados) return;

        const aviso = document.createElement('div');
        aviso.className = 'form-text mb-3';
        aviso.setAttribute('role', 'status');
        aviso.setAttribute('aria-live', 'polite');
        resultados.before(aviso);

        let temporizador;
        let requisicao;
        let ultimaBusca = 0;

        function enderecoPesquisa() {
            const endereco = new URL(formulario.action);
            endereco.search = '';

            for (const [nome, valor] of new FormData(formulario)) {
                if (String(valor).trim() !== '') {
                    endereco.searchParams.append(nome, valor);
                }
            }

            return endereco;
        }

        function preencherFiltros(endereco) {
            for (const campo of formulario.elements) {
                if (campo.name) {
                    campo.value = endereco.searchParams.get(campo.name) ?? '';
                }
            }
        }

        async function pesquisar(endereco, atualizarEndereco = true) {
            clearTimeout(temporizador);
            requisicao?.abort();

            const numeroBusca = ++ultimaBusca;
            requisicao = new AbortController();

            resultados.setAttribute('aria-busy', 'true');
            aviso.className = 'form-text mb-3';
            aviso.textContent = 'Pesquisando...';

            try {
                const resposta = await fetch(endereco.href, {
                    signal: requisicao.signal,
                    headers: {
                        Accept: 'text/html'
                    },
                    credentials: 'same-origin',
                });

                if (!resposta.ok) {
                    throw new Error('Falha na pesquisa.');
                }

                const html = await resposta.text();

                if (numeroBusca !== ultimaBusca) return;

                const pagina = new DOMParser().parseFromString(html, 'text/html');
                const novosResultados = pagina.getElementById('resultados-pesquisa');

                if (!novosResultados) {
                    throw new Error('Resultados indisponíveis.');
                }

                resultados.innerHTML = novosResultados.innerHTML;

                if (atualizarEndereco) {
                    window.history.replaceState(null, '', resposta.url || endereco.href);
                }

                aviso.textContent = '';
            } catch (erro) {
                if (erro.name === 'AbortError' || numeroBusca !== ultimaBusca) return;

                aviso.className = 'alert alert-warning';
                aviso.textContent = 'Não foi possível atualizar a pesquisa. ';

                const link = document.createElement('a');
                link.href = endereco.href;
                link.className = 'alert-link';
                link.textContent = 'Clique aqui para carregar a página.';
                aviso.append(link);
            } finally {
                if (numeroBusca === ultimaBusca) {
                    resultados.setAttribute('aria-busy', 'false');
                }
            }
        }

        function agendarPesquisa() {
            clearTimeout(temporizador);
            ++ultimaBusca;
            requisicao?.abort();

            resultados.setAttribute('aria-busy', 'false');
            aviso.textContent = '';

            temporizador = setTimeout(() => {
                if (formulario.checkValidity()) {
                    pesquisar(enderecoPesquisa());
                }
            }, 450);
        }

        formulario.addEventListener('input', evento => {
            if (
                evento.target.matches('input[type="search"]') &&
                !evento.isComposing
            ) {
                agendarPesquisa();
            }
        });

        formulario.addEventListener('change', evento => {
            if (!evento.target.matches('input[type="search"]')) {
                agendarPesquisa();
            }
        });

        formulario.addEventListener('submit', evento => {
            evento.preventDefault();

            if (formulario.reportValidity()) {
                pesquisar(enderecoPesquisa());
            }
        });

        formulario.querySelector('[data-limpar-pesquisa]')?.addEventListener('click', evento => {
            if (
                evento.button !== 0 ||
                evento.ctrlKey ||
                evento.metaKey ||
                evento.shiftKey ||
                evento.altKey
            ) return;

            evento.preventDefault();

            const endereco = new URL(evento.currentTarget.href);

            preencherFiltros(endereco);
            pesquisar(endereco);
        });

        resultados.addEventListener('click', evento => {
            const link = evento.target.closest('.pagination a');

            if (
                !link ||
                evento.button !== 0 ||
                evento.ctrlKey ||
                evento.metaKey ||
                evento.shiftKey ||
                evento.altKey
            ) return;

            const endereco = new URL(link.href);

            if (endereco.origin !== window.location.origin) return;

            evento.preventDefault();
            pesquisar(endereco);
        });

        window.addEventListener('popstate', () => {
            const endereco = new URL(window.location.href);

            preencherFiltros(endereco);
            pesquisar(endereco, false);
        });
    })();
</script>

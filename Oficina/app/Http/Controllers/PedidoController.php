<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Veiculos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->autorizarAcesso($request);

        $pedidos = Pedido::with([
            'cliente',
            'veiculo',
            'operador'
        ])->orderByDesc('id')->get();

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);

        $veiculos = Veiculos::with('cliente')
            ->orderBy('placa')
            ->get();

        $produtos = Produto::where('estoque', '>', 0)
            ->orderBy('nome')
            ->get();

        $servicos = Servico::orderBy('nome')->get();

        return view(
            'pedidos.create',
            compact('veiculos', 'produtos', 'servicos')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarCadastro($request);

        $pedido = DB::transaction(function () use ($dados, $request) {
            $veiculo = Veiculos::lockForUpdate()
                ->findOrFail($dados['veiculo_id']);

            $produtos = Produto::whereIn(
                'id',
                array_column($dados['produtos'], 'produto_id')
            )->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $servicos = Servico::whereIn(
                'id',
                array_column($dados['servicos'], 'servico_id')
            )->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $total = 0;

            foreach ($dados['produtos'] as $item) {
                $produto = $produtos->get($item['produto_id']);

                if (!$produto || $produto->estoque < $item['quantidade']) {
                    throw ValidationException::withMessages([
                        'produtos' => 'Estoque insuficiente para uma das peças selecionadas.',
                    ]);
                }

                $total += $this->centavos($produto->preco)
                    * $item['quantidade'];
            }

            foreach ($dados['servicos'] as $item) {
                $servico = $servicos->get($item['servico_id']);

                if (!$servico) {
                    throw ValidationException::withMessages([
                        'servicos' => 'Um dos serviços selecionados não está mais disponível.',
                    ]);
                }

                $total += $this->centavos($servico->preco)
                    * $item['quantidade'];
            }

            if ($total > 9999999999) {
                throw ValidationException::withMessages([
                    'total' => 'O total da ordem de serviço ultrapassa o limite permitido.',
                ]);
            }

            $pedido = Pedido::create([
                'status' => 'ABERTO',
                'total' => $this->decimal($total),
                'user_id' => null,
                'cliente_id' => $veiculo->cliente_id,
                'usuario_id' => $request->user()->id,
                'veiculo_id' => $veiculo->id,
                'responsavel' => $dados['responsavel'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null,
                'estoque_baixado' => count($dados['produtos']) > 0,
            ]);

            foreach ($dados['produtos'] as $item) {
                $produto = $produtos->get($item['produto_id']);

                $pedido->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $item['quantidade'],
                    'preco' => $produto->preco,
                ]);

                $produto->decrement('estoque', $item['quantidade']);
            }

            foreach ($dados['servicos'] as $item) {
                $servico = $servicos->get($item['servico_id']);

                $pedido->servicos()->create([
                    'servico_id' => $servico->id,
                    'quantidade' => $item['quantidade'],
                    'preco' => $servico->preco,
                ]);
            }

            return $pedido;
        }, 3);

        return redirect()->route('pedidos.show', $pedido->id)
            ->with('mensagem', 'Ordem de serviço cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $pedido = Pedido::with([
            'cliente',
            'veiculo',
            'operador',
            'itens.produto',
            'servicos.servico',
        ])->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $pedido = Pedido::with([
            'cliente',
            'veiculo',
        ])->findOrFail($id);

        return view('pedidos.edit', compact('pedido'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $dados = $request->validate([
            'status' => [
                'required',
                'in:ABERTO,EM_EXECUCAO,PAGO,ENVIADO,CONCLUIDO,CANCELADO',
            ],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ], [
            'status.required' => 'Selecione o andamento da OS.',
            'status.in' => 'Selecione um andamento válido.',
            'responsavel.max' => 'O responsável deve ter até 255 caracteres.',
            'observacoes.max' => 'As observações devem ter até 5000 caracteres.',
        ]);

        $this->alterarAndamento($id, $dados);

        return redirect()->route('pedidos.show', $id)
            ->with('mensagem', 'Ordem de serviço atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $this->alterarAndamento($id, [
            'status' => 'CANCELADO',
        ]);

        return redirect()->route('pedidos.show', $id)
            ->with('mensagem', 'OS cancelada. O histórico foi preservado.');
    }

    public function imprimir(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $pedido = Pedido::with([
            'cliente',
            'veiculo',
            'itens.produto',
            'servicos.servico',
        ])->findOrFail($id);

        return view('pedidos.imprimir', compact('pedido'));
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
    }

    private function validarCadastro(Request $request)
    {
        foreach (['produtos', 'servicos'] as $lista) {
            $itens = $request->input($lista, []);

            if (is_array($itens)) {
                $request->merge([
                    $lista => array_values(array_filter(
                        $itens,
                        function ($item) {
                            return !is_array($item)
                                || !in_array(
                                    $item['quantidade'] ?? null,
                                    [null, '', 0, '0'],
                                    true
                                );
                        }
                    )),
                ]);
            }
        }

        $dados = $request->validate([
            'veiculo_id' => [
                'required',
                'integer',
                'exists:veiculos,id',
            ],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:5000'],

            'produtos' => ['present', 'array', 'max:100'],
            'produtos.*' => ['array'],
            'produtos.*.produto_id' => [
                'required',
                'integer',
                'distinct',
                'exists:produtos,id',
            ],
            'produtos.*.quantidade' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],

            'servicos' => ['present', 'array', 'max:100'],
            'servicos.*' => ['array'],
            'servicos.*.servico_id' => [
                'required',
                'integer',
                'distinct',
                'exists:servicos,id',
            ],
            'servicos.*.quantidade' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],
        ], [
            'veiculo_id.required' => 'Selecione um veículo.',
            'veiculo_id.exists' => 'Selecione um veículo cadastrado.',
            'produtos.*.produto_id.exists' => 'Selecione uma peça cadastrada.',
            'produtos.*.produto_id.distinct' => 'Não repita a mesma peça na ordem de serviço.',
            'produtos.*.quantidade.integer' => 'A quantidade de peças deve ser inteira.',
            'produtos.*.quantidade.min' => 'A quantidade de peças deve ser maior que zero.',
            'servicos.*.servico_id.exists' => 'Selecione um serviço cadastrado.',
            'servicos.*.servico_id.distinct' => 'Não repita o mesmo serviço na ordem de serviço.',
            'servicos.*.quantidade.integer' => 'A quantidade de serviços deve ser inteira.',
            'servicos.*.quantidade.min' => 'A quantidade de serviços deve ser maior que zero.',
        ]);

        if (!$dados['produtos'] && !$dados['servicos']) {
            throw ValidationException::withMessages([
                'itens' => 'Inclua pelo menos uma peça ou um serviço na ordem de serviço.',
            ]);
        }

        return $dados;
    }

    private function alterarAndamento($id, array $dados)
    {
        DB::transaction(function () use ($id, $dados) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            $statusAtual = $pedido->status;
            $novoStatus = $dados['status'];

            if (
                in_array($novoStatus, ['PAGO', 'ENVIADO'], true)
                && $novoStatus !== $statusAtual
            ) {
                throw ValidationException::withMessages([
                    'status' => 'PAGO e ENVIADO são situações antigas. Use os novos andamentos da ordem de serviço.',
                ]);
            }

            if (
                $statusAtual === 'PAGO'
                && $novoStatus !== $statusAtual
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Esta ordem de serviço possui o status antigo PAGO. Precisamos adaptar seu recebimento antes de mudar o andamento.',
                ]);
            }

            if (
                in_array($statusAtual, ['CONCLUIDO', 'CANCELADO'], true)
                && $novoStatus !== $statusAtual
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Uma ordem de serviço concluída ou cancelada não pode mudar de andamento.',
                ]);
            }

            if (
                in_array($statusAtual, ['EM_EXECUCAO', 'ENVIADO'], true)
                && $novoStatus === 'ABERTO'
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Não é possível voltar a ordem de serviço para ABERTO.',
                ]);
            }

            if (
                $novoStatus === 'CANCELADO'
                && $statusAtual !== 'CANCELADO'
            ) {
                $itens = $pedido->itens()
                    ->orderBy('produto_id')
                    ->get();

                if (
                    $itens->isNotEmpty()
                    && $pedido->estoque_baixado === null
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'Esta ordem de serviço antiga possui peças cuja baixa não foi conferida. Confira o estoque antes de cancelá-la.',
                    ]);
                }

                if ($pedido->estoque_baixado) {
                    foreach ($itens as $item) {
                        $produto = Produto::lockForUpdate()
                            ->findOrFail($item->produto_id);

                        $novoSaldo = $produto->estoque + $item->quantidade;

                        if (
                            $item->quantidade <= 0
                            || $novoSaldo > 2147483647
                        ) {
                            throw ValidationException::withMessages([
                                'estoque' => 'Não foi possível repor uma das peças. Confira a quantidade e o saldo.',
                            ]);
                        }

                        $produto->increment(
                            'estoque',
                            $item->quantidade
                        );
                    }
                }

                $pedido->estoque_baixado = false;
            }

            $pedido->fill($dados)->save();
        }, 3);
    }

    private function centavos($valor): int
    {
        $valor = (string) $valor;

        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $valor)) {
            throw ValidationException::withMessages([
                'preco' => 'Há uma peça ou serviço com preço inválido. Confira o cadastro.',
            ]);
        }

        $partes = explode('.', $valor);

        return ((int) $partes[0] * 100)
            + (int) str_pad($partes[1] ?? '', 2, '0');
    }

    private function decimal(int $centavos): string
    {
        return intdiv($centavos, 100)
            . '.'
            . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}

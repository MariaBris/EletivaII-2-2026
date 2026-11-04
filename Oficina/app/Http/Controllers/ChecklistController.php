<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChecklistController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $pedido = Pedido::with(['cliente', 'veiculo'])->findOrFail($id);

        $itens = $pedido->checklistItens()
            ->with('conferente')
            ->orderBy('ordem')
            ->get()
            ->keyBy('codigo');

        $campos = $this->campos($itens);
        $grupos = config('checklist_os');

        $editavel = !in_array(
            $pedido->status,
            ['CONCLUIDO', 'CANCELADO'],
            true
        );

        return view('pedidos.checklist', compact(
            'pedido',
            'itens',
            'campos',
            'grupos',
            'editavel'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        DB::transaction(function () use ($request, $id) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            if (in_array($pedido->status, ['CONCLUIDO', 'CANCELADO'], true)) {
                throw ValidationException::withMessages([
                    'checklist' => 'O checklist de uma OS concluída ou cancelada não pode ser alterado.',
                ]);
            }

            $itens = $pedido->checklistItens()
                ->orderBy('ordem')
                ->get()
                ->keyBy('codigo');

            $campos = $this->campos($itens);

            $regras = [
                'itens' => [
                    'required',
                    'array:' . implode(',', array_keys($campos)),
                ],
            ];

            $atributos = [];

            foreach ($campos as $codigo => $campo) {
                $situacoes = $campo['grupo'] === 'ACESSORIOS'
                    ? 'NAO_VERIFICADO,PRESENTE,AUSENTE'
                    : 'NAO_VERIFICADO,OK,ATENCAO';

                $regras['itens.' . $codigo . '.situacao'] = [
                    'required',
                    'string',
                    'in:' . $situacoes,
                ];

                $regras['itens.' . $codigo . '.observacoes'] = [
                    'nullable',
                    'string',
                    'max:1000',
                ];

                if ($codigo === 'acessorios_outros') {
                    $regras['itens.' . $codigo . '.observacoes'][] =
                        'required_if:itens.acessorios_outros.situacao,PRESENTE';
                }

                $atributos['itens.' . $codigo . '.situacao'] =
                    'situação de ' . $campo['nome'];

                $atributos['itens.' . $codigo . '.observacoes'] =
                    'observação de ' . $campo['nome'];
            }

            $dados = $request->validate($regras, [
                'itens.required' => 'Preencha o checklist.',
                'itens.array' => 'Os itens enviados não correspondem ao checklist desta OS.',
                'required' => 'Informe o campo :attribute.',
                'in' => 'Selecione uma situação válida para :attribute.',
                'string' => 'O campo :attribute deve ser um texto.',
                'max' => 'O campo :attribute deve ter até :max caracteres.',
                'required_if' => 'Descreva quais outros acessórios estão presentes.',
            ], $atributos);

            foreach ($campos as $codigo => $campo) {
                $situacao = $dados['itens'][$codigo]['situacao'];
                $observacoes = trim(
                    $dados['itens'][$codigo]['observacoes'] ?? ''
                );

                $observacoes = $observacoes === '' ? null : $observacoes;
                $existente = $itens->get($codigo);

                if (
                    $existente
                    && $existente->situacao === $situacao
                    && $existente->observacoes === $observacoes
                ) {
                    continue;
                }

                $verificado = $situacao !== 'NAO_VERIFICADO';

                $pedido->checklistItens()->updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'grupo' => $campo['grupo'],
                        'nome' => $campo['nome'],
                        'ordem' => $campo['ordem'],
                        'situacao' => $situacao,
                        'observacoes' => $observacoes,
                        'conferido_por' => $verificado
                            ? $request->user()->id
                            : null,
                        'conferido_em' => $verificado ? now() : null,
                    ]
                );
            }
        }, 3);

        return redirect()->route('checklist.edit', $id)
            ->with('mensagem', 'Checklist salvo com sucesso!');
    }

    public function modelo(Request $request)
    {
        $this->autorizarAcesso($request);

        $grupos = config('checklist_os');

        return view('pedidos.checklist-modelo', compact('grupos'));
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
    }

    private function campos($itens)
    {
        $campos = [];

        if ($itens->isNotEmpty()) {
            foreach ($itens as $item) {
                $campos[$item->codigo] = [
                    'grupo' => $item->grupo,
                    'nome' => $item->nome,
                    'ordem' => $item->ordem,
                ];
            }

            return $campos;
        }

        $ordem = 0;

        foreach (config('checklist_os') as $grupo => $dados) {
            foreach ($dados['itens'] as $codigo => $nome) {
                $campos[$codigo] = [
                    'grupo' => $grupo,
                    'nome' => $nome,
                    'ordem' => ++$ordem,
                ];
            }
        }

        return $campos;
    }
}

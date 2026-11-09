<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OficinaController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request): View
    {
        $this->autorizarAcesso($request);

        $oficina = Oficina::dados();

        return view('oficina.edit', compact('oficina'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->autorizarAcesso($request);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'endereco' => ['required', 'string', 'max:500'],
            'telefone' => ['nullable', 'string', 'max:30'],
        ], [
            'nome.required' => 'Informe o nome da oficina.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome deve ter até 255 caracteres.',

            'endereco.required' => 'Informe o endereço da oficina.',
            'endereco.string' => 'O endereço deve ser um texto.',
            'endereco.max' => 'O endereço deve ter até 500 caracteres.',

            'telefone.string' => 'O telefone deve ser um texto.',
            'telefone.max' => 'O telefone deve ter até 30 caracteres.',
        ]);

        $dados['telefone'] = $dados['telefone'] ?? null;

        Oficina::updateOrCreate(
            ['id' => 1],
            $dados
        );

        return redirect()->route('oficina.edit')
            ->with('mensagem', 'Dados da oficina atualizados! As próximas impressões utilizarão essas informações.');
    }

    private function autorizarAcesso(Request $request): void
    {
        abort_unless(
            $request->user()?->role === 'ADM',
            403
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::all();

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $this->validar($request);

        Categoria::create($dados);

        return redirect()->route('categorias.index')
            ->with('mensagem', 'Categoria inserida com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        return view('categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $dados = $this->validar($request);

        if ($categoria->update($dados)) {
            return redirect()->route('categorias.index')
                ->with('mensagem', 'Categoria alterada com sucesso!');
        }

        return back()->withInput()
            ->withErrors(['categoria' => 'Não foi possível alterar a categoria.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        try {
            if (!$categoria->delete()) {
                return redirect()->route('categorias.index')
                    ->with('erro', 'Não foi possível excluir a categoria.');
            }
        } catch (QueryException $erro) {
            if (($erro->errorInfo[1] ?? null) == 1451) {
                return redirect()->route('categorias.index')
                    ->with('erro', 'Esta categoria possui registros vinculados, como produtos, e não pode ser excluída.');
            }

            throw $erro;
        }

        return redirect()->route('categorias.index')
            ->with('mensagem', 'Categoria excluída com sucesso!');
    }

    private function validar(Request $request)
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
        ], [
            'nome.required' => 'Informe o nome da categoria.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome deve ter até 255 caracteres.',
            'descricao.string' => 'A descrição deve ser um texto.',
        ]);
    }
}

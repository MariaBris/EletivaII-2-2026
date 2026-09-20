<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::all();
        return view('categoria.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categoria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Categoria::create($request->all()))
            return redirect()->route('categorias.index')->with('mensagem', 'Categoria inserida com sucesso!');
        else
            return redirect()->route('categorias.index')->with('mensagem', 'Erro ao inserir a categoria!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categoria.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categoria.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categoria = Categoria::finOrFail($id);
        if($categoria->update($request->all()))
            return redirect()->route('categorias.index')->with('mensagem', 'Categoria alterada com sucesso!');
        else
            return redirect()->route('categorias.index')->with('mensagem', 'Erro ao alterar a categoria!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categoria = Categoria::FindOrFail($id);
        if($categoria->delete())
            return redirect()->route('categorias.index')->with('mensagem', 'Categoria excluída!');
        else
            return redirect()->route('categorias.index')->with('mesagem', "Erro ao excluir a categoria!");
    }
}

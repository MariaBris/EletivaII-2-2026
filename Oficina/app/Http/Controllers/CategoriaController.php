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
        if (Categoria::create($request->all())) {
            return redirect()->route('categorias.index')->with('mensagem', 'Categoria inserida com sucesso!!');
        } else {
            return redirect()->route('categorias.index')->with('mensagem', 'Erro ao inserir categoria!!"');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $categoria)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        if ($categoria->update($request->all)) {
            return redirect()->route('categorias.index')->with('mensagem', 'Categoria alterada com sucesso!!');
        } else {
            return redirect()->route('categorias.index')->with('mensagem', 'Erro ao alterar categoria!!"');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        $categoria = Categoria::findOrFail($id);
        if($categoria->deete()){
            return redirect()->route('categorias.index')->with('mensagem', 'Categoria excluída com sucesso!!');
        } else {
            return redirect()->route('categorias.index')->with('mensagem', 'Erro ao excluir categoria!!"');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Categoria;//precisa declarar quando for usar tipo o categoria::all()
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$produtos = Produto::all();//puxa todos os registros e joga na variavel
        $produtos = Produto::with('categoria')->get();//with funciona como inner join com o negocio da model
        return view('produto.index', compact('produtos'));//boa pratica view como mesmo nome da
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        return view('produto.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Produto::create($request->all());//ele cria no bd
        return redirect()->route('produto.index');//implementar mensagens
    }

    /**
     * Display the specified resource.
     */
    public function show(Produto $produto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $produto = Produto::findOrFail($id);
        $categorias = Categoria::all();
        return view('produto.edit', compact('produto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $produto = Produto::findOrFail($id);
        $produto->update($request->all());
        return redirect()->route('produto.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        //
    }
}

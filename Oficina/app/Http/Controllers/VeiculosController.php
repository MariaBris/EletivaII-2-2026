<?php

namespace App\Http\Controllers;

use App\Models\Veiculos;
use Illuminate\Http\Request;

class VeiculosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $veiculos = Veiculos::all();
        return view('veiculos.index', compact('veiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('veiculos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Veiculos::create($requets->all()))
            return redirect()->route('veiculos.index')->with('mensagem', 'Veículo inserido com sucesso!');
        else
            return redirect()->route('categorias.index')->with('mensagem', 'Erro ao inserir o veículo!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Veiculos $id)
    {
        $veiculo = Veiculos::findOrFail($id);
        return view('veiculos.show', compact('veiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Veiculos $veiculos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Veiculos $veiculos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Veiculos $veiculos)
    {
        //
    }
}

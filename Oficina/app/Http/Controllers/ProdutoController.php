<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::all(); // Busca todos os produtos registados no banco de dados
        return view('produtos.index', compact('produtos')); // Retorna a view de listagem enviando a variável com os produtos
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all(); // Busca todas as categorias para preencher o select do formulário
        return view('produtos.create', compact('categorias')); // Abre a tela de cadastro enviando as categorias
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $request->all(); // Recolhe todos os dados enviados pelo formulário

        // Verifica se o utilizador enviou um ficheiro de imagem do dispositivo (computador/telemóvel)
        if ($request->hasFile('imagem_arquivo')) {
            $imagem = $request->file('imagem_arquivo');
            $nomeImagem = time() . '_' . $imagem->getClientOriginalName(); // Cria um nome único usando o timestamp atual
            $imagem->move(public_path('img/produtos'), $nomeImagem); // Move o ficheiro para a pasta public/img/produtos
            $dados['imagem'] = 'img/produtos/' . $nomeImagem; // Grava o caminho relativo que será salvo no banco
        }
        // Se o utilizador preferiu colar um link direto da internet
        elseif ($request->filled('imagem_url')) {
            $dados['imagem'] = $request->imagem_url;
        }

        $produto = Produto::create($dados); // Grava o novo produto no banco de dados

        if($produto)
            return redirect()->route('produtos.index')->with('mensagem', "O produto '{$produto->nome}' foi inserido com sucesso!");
        else
            return redirect()->route('produtos.index')->with('mensagem', 'Erro ao inserir o produto!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produto = Produto::findOrFail($id); // Procura o produto pelo ID ou mostra erro 404 se não encontrar
        return view('produtos.show', compact('produto')); // Retorna a tela de consulta/exclusão
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produto = Produto::findOrFail($id); // Encontra o produto que vai ser editado
        $categorias = Categoria::all(); // Busca as categorias para o select continuar funcional na edição
        return view('produtos.edit', compact('produto', 'categorias')); // Abre o formulário de edição com os dados atuais
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $produto = Produto::findOrFail($id); // Localiza o registo que será alterado
        $dados = $request->all(); // Pega os novos dados do formulário

        // Verifica se foi enviada uma nova imagem por ficheiro durante a edição
        if ($request->hasFile('imagem_arquivo')) {
            $imagem = $request->file('imagem_arquivo');
            $nomeImagem = time() . '_' . $imagem->getClientOriginalName();
            $imagem->move(public_path('img/produtos'), $nomeImagem);
            $dados['imagem'] = 'img/produtos/' . $nomeImagem;
        }
        // Se foi preenchido um novo link da internet
        elseif ($request->filled('imagem_url')) {
            $dados['imagem'] = $request->imagem_url;
        }

        // Executa a atualização no banco de dados com os dados tratados
        if($produto->update($dados))
            return redirect()->back()->with('mensagem', "O produto '{$produto->nome}' foi alterado com sucesso!");
        else
            return redirect()->back()->with('mensagem', 'Erro ao alterar o produto!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produto = Produto::findOrFail($id); // Encontra o produto antes de apagar
        $nomeProduto = $produto->nome; // Guarda o nome para exibir na mensagem de sucesso após a exclusão

        if($produto->delete()) // Executa a exclusão do registo
            return redirect()->route('produtos.index')->with('mensagem', "O produto '{$nomeProduto}' foi excluído com sucesso!");
        else
            return redirect()->route('produtos.index')->with('mensagem', "Erro ao excluir o produto!");
    }
}

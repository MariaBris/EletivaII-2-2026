<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultar Categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-3">
        <h1>Dados da Categoria</h1>
        <form method="post" action="/categorias/{{ $categoria->id }}">
            @csrf
            @method('DELETE')
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da categoria</label>
                <input type="text" id="nome" name="nome" class="form-control" disabled value="{{ $categoria->nome }}">
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" id="descricao" name="descricao" class="form-control" disabled value="{{ $categoria->descricao }}">
            </div>
            <a href="/categorias" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
    </div>
</body>
</html>

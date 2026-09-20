<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categorias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-3">
        @if(session('mensagem'))
            <div class="alert alert-success">{{ session('mensagem') }}</div>
        @endif
        <h2>Categorias</h2>
        <a href="/categorias/create" class="btn btn-success mb-3">Novo Registro</a>
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorias as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td>{{ $c->nome }}</td>
                        <td>{{ $c->descricao }}</td>
                        <td class="d-flex gap-2">
                            <a href="/categorias/{{ $c->id }}/edit" class="btn btn-sm btn-warning">Editar</a>
                            <a href="/categorias/{{ $c->id }}" class="btn btn-sm btn-info">Consultar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>

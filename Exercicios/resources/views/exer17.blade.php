@extends('layout')
@section('titulo', 'Exercício 17')
@section('conteudo')
<h1>Exercício 17</h1>
<form method="post" action="/exer17resp">
    <div class="mb-3">
        <label for="capital" class="form-label">Insira o valor do capital R$:</label>
        <input type="text" id="capital" name="capital" class="form-control" required="">
    </div>
    <div class="mb-3">
        <label for="juros" class="form-label">Insira a taxa de juros por mês(%):</label>
        <input type="text" id="juros" name="juros" class="form-control" required="">
    </div>
    <div class="mb-3">
        <label for="periodo" class="form-label">Insira o periodo(meses):</label>
        <input type="text" id="periodo" name="periodo" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($valor, $rendimento)
<p>O valor do rendimento é de R${{ $rendimento }}, com valor final de  R${{ $valor }}</p>
@endisset
@endsection
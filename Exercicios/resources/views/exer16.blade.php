@extends('layout')
@section('titulo', 'Exercício 16')
@section('conteudo')
<h1>Exercício 16</h1>
<form method="post" action="/exer16resp">
    <div class="mb-3">
        <label for="valor" class="form-label">Insira o valor R$:</label>
        <input type="text" id="valor" name="valor" class="form-control" required="">
    </div>
    <div class="mb-3">
        <label for="desconto" class="form-label">Insira o valor do desconto(%):</label>
        <input type="text" id="desconto" name="desconto" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($valor)
<p>O valor com desconto é R${{ $valor }}</p>
@endisset
@endsection
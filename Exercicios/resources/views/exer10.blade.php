@extends('layout')
@section('titulo', 'Exercício 10')
@section('conteudo')
<h1>Exercício 10</h1>
<form method="post" action="/exer10resp">
    <div class="mb-3">
              <label for="largura" class="form-label">Insira o valor da largura:</label>
              <input type="text" id="largura" name="largura" class="form-control" required="">
            </div><div class="mb-3">
              <label for="altura" class="form-label">Insira o valor da altura:</label>
              <input type="text" id="altura" name="altura" class="form-control" required="">
            </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($peri)
<p>Perímetro: {{ $peri }}</p>
@endisset
@endsection
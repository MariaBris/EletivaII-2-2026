@extends('layout')
@section('titulo', 'Exercício 11')
@section('conteudo')
<h1>Exercício 11</h1>
<form method="post" action="/exer11resp">
    <div class="mb-3">
        <label for="raio" class="form-label">Insira o valor do raio do círculo:</label>
        <input type="text" id="raio" name="raio" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($peri)
<p>Perímetro: {{ $peri }}</p>
@endisset
@endsection
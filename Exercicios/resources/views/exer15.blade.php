@extends('layout')
@section('titulo', 'Exercício 15')
@section('conteudo')
<h1>Exercício 15</h1>
<form method="post" action="/exer15resp">
    <div class="mb-3">
        <label for="peso" class="form-label">Insira seu peso(kg):</label>
        <input type="text" id="peso" name="peso" class="form-control" required="">
    </div>
    <div class="mb-3">
        <label for="altura" class="form-label">Insira sua altura(m):</label>
        <input type="text" id="altura" name="altura" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($imc)
<p>IMC: {{ $imc }}</p>
@endisset
@endsection
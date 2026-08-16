@extends('layout')
@section('titulo', 'Exercício 7')
@section('conteudo')
<h1>Exercício 7</h1>
<form method="post" action="/exer7resp">
    <div class="mb-3">
        <label for="tempF" class="form-label">Insira o valor em Fahrenheit:</label>
        <input type="text" id="tempF" name="tempF" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($temp)
<p>A temperatura em Celsius é: {{ $temp}}</p>
@endisset
@endsection
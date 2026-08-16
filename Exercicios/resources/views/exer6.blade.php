@extends('layout')
@section('titulo', 'Exercício 6')
@section('conteudo')
<h1>Exercício 6</h1>
<form method="post" action="/exer6resp">
    <div class="mb-3">
        <label for="celsius" class="form-label">Digite a temperatura em Celsius:</label>
        <input type="number" id="celsius" name="celsius" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($temp)
<p>A temperatura em Fahrenheit é: {{ $temp}}</p>
@endisset
@endsection
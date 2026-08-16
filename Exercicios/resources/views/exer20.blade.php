@extends('layout')
@section('titulo', 'Exercício 20')
@section('conteudo')
<h1>Exercício 20</h1>
<form method="post" action="/exer20resp">
    <div class="mb-3">
        <label for="distancia" class="form-label">Insira a distância(KM):</label>
        <input type="text" id="distancia" name="distancia" class="form-control" required="">
    </div>
    <div class="mb-3">
        <label for="tempo" class="form-label">Insira o tempo(Horas):</label>
        <input type="text" id="tempo" name="tempo" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($resultado)
<p>Velocidade média de {{$resultado}}KM/h</p>
@endisset
@endsection
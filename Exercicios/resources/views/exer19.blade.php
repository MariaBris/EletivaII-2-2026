@extends('layout')
@section('titulo', 'Exercício 19')
@section('conteudo')
<h1>Exercício 19</h1>
<form method="post" action="/exer19resp">
    <div class="mb-3">
        <label for="dia" class="form-label">Insira a quantidade de dias:</label>
        <input type="text" id="dia" name="dia" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($hora, $minuto, $segundo)
<p>Essa quantidade de dias equivale a {{ $hora }} h, {{ $minuto }} min e {{$segundo }} s.</p>
@endisset
@endsection
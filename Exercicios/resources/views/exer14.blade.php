@extends('layout')
@section('titulo', 'Exercício 14')
@section('conteudo')
<h1>Exercício 14</h1>
<form method="post" action="/exer14resp">
    <div class="mb-3">
              <label for="km" class="form-label">Insira um valor em km</label>
              <input type="text" id="km" name="km" class="form-control" required="">
            </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($milha)
<p>Conversão {{ $milha }}mi</p>
@endisset
@endsection
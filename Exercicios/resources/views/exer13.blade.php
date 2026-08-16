@extends('layout')
@section('titulo', 'Exercício 13')
@section('conteudo')
<h1>Exercício 13</h1>
<form method="post" action="/exer13resp">
    <div class="mb-3">
        <label for="metro" class="form-label">Insira um valor em metros:</label>
        <input type="text" id="metro" name="metro" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($cm)
<p>Conversão {{ $cm }}cm</p>
@endisset
@endsection
@extends('layout')
@section('titulo', 'Exercício 12')
@section('conteudo')
<h1>Exercício 12</h1>
<form method="post" action="/exer12resp">
    <div class="mb-3">
        <label for="base" class="form-label">Insira o valor da base:</label>
        <input type="text" id="base" name="base" class="form-control" required="">
    </div>
    <div class="mb-3">
        <label for="expoente" class="form-label">Insira o valor do expoente:</label>
        <input type="text" id="expoente" name="expoente" class="form-control" required="">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
@isset($resultado, $base, $expoente)
<p>Resultado de {{ $base }}^{{ $expoente }}: {{ $resultado }}</p>
@endisset
@endsection
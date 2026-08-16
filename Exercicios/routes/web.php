<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExerciciosController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/exer1', [ExerciciosController::class, 'abrirFormExer1']);

Route::post('exer1resp', [ExerciciosController::class, 'respostaExer1']);

Route::get('/exer2', [ExerciciosController::class, 'abrirFormExer2']);

Route::post('exer2resp', [ExerciciosController::class, 'respostaExer2']);

Route::get('/exer3', [ExerciciosController::class, 'abrirFormExer3']);

Route::post('exer3resp', [ExerciciosController::class, 'respostaExer3']);

Route::get('/exer4', [ExerciciosController::class, 'abrirFormExer4']);

Route::post('exer4resp', [ExerciciosController::class, 'respostaExer4']);
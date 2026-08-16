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

Route::get('/exer5', [ExerciciosController::class, 'abrirFormExer5']);

Route::post('exer5resp', [ExerciciosController::class, 'respostaExer5']);

Route::get('/exer6', [ExerciciosController::class, 'abrirFormExer6']);

Route::post('exer6resp', [ExerciciosController::class, 'respostaExer6']);

Route::get('/exer7', [ExerciciosController::class, 'abrirFormExer7']);

Route::post('exer7resp', [ExerciciosController::class, 'respostaExer7']);

Route::get('/exer8', [ExerciciosController::class, 'abrirFormExer8']);

Route::post('exer8resp', [ExerciciosController::class, 'respostaExer8']);

Route::get('/exer9', [ExerciciosController::class, 'abrirFormExer9']);

Route::post('exer9resp', [ExerciciosController::class, 'respostaExer9']);

Route::get('/exer10', [ExerciciosController::class, 'abrirFormExer10']);

Route::post('exer10resp', [ExerciciosController::class, 'respostaExer10']);

Route::get('/exer11', [ExerciciosController::class, 'abrirFormExer11']);

Route::post('exer11resp', [ExerciciosController::class, 'respostaExer11']);

Route::get('/exer12', [ExerciciosController::class, 'abrirFormExer12']);

Route::post('exer12resp', [ExerciciosController::class, 'respostaExer12']);

Route::get('/exer13', [ExerciciosController::class, 'abrirFormExer13']);

Route::post('exer13resp', [ExerciciosController::class, 'respostaExer13']);

Route::get('/exer14', [ExerciciosController::class, 'abrirFormExer14']);

Route::post('exer14resp', [ExerciciosController::class, 'respostaExer14']);

Route::get('/exer15', [ExerciciosController::class, 'abrirFormExer15']);

Route::post('exer15resp', [ExerciciosController::class, 'respostaExer15']);

Route::get('/exer16', [ExerciciosController::class, 'abrirFormExer16']);

Route::post('exer16resp', [ExerciciosController::class, 'respostaExer16']);

Route::get('/exer17', [ExerciciosController::class, 'abrirFormExer17']);

Route::post('exer17resp', [ExerciciosController::class, 'respostaExer17']);

Route::get('/exer18', [ExerciciosController::class, 'abrirFormExer18']);

Route::post('exer18resp', [ExerciciosController::class, 'respostaExer18']);

Route::get('/exer19', [ExerciciosController::class, 'abrirFormExer19']);

Route::post('exer19resp', [ExerciciosController::class, 'respostaExer19']);

Route::get('/exer20', [ExerciciosController::class, 'abrirFormExer20']);

Route::post('exer20resp', [ExerciciosController::class, 'respostaExer20']);


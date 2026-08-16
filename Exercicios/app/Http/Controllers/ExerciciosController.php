<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExerciciosController extends Controller
{
    public function abrirFormExer1(){
        return view('exer1');
    }

    public function respostaExer1(Request $request){
        $valor1 = $request->valor1;
        $valor2 = $request->valor2;
        $soma = $valor1 + $valor2;
        return view('exer1', ['soma' => $soma]);
    }

    public function abrirFormExer2(){
        return view('exer2');
    }

    public function respostaExer2(Request $request){
        $valor1 = $request->valor1;
        $valor2 = $request->valor2;
        $sub = $valor1 - $valor2;
        return view('exer2', ['sub' => $sub]);
    }

    public function abrirFormExer3(){
        return view('exer3');
    }

    public function respostaExer3(Request $request){
        $valor1 = $request->valor1;
        $valor2 = $request->valor2;
        $mult = $valor1 * $valor2;
        return view('exer3', ['mult' => $mult]);
    }

    public function abrirFormExer4(){
        return view('exer4');
    }

    public function respostaExer4(Request $request){
        $valor1 = $request->valor1;
        $valor2 = $request->valor2;
        if ($valor2 == 0){
            $divi = "Divisão por 0!";
            return view('exer4', ['divi' => $divi]);
        } else{
            $divi = $valor1 / $valor2;
            return view('exer4', ['divi' => $divi]);
        } 
    }

    public function abrirFormExer5(){
        return view('exer5');
    }
    public function respostaExer5(Request $request){
        $valor1 = $request->valor1;
        $valor2 = $request->valor2;
        $valor3 = $request->valor3;
        $med = ($valor1 + $valor2 + $valor3) / 3;
        return view('exer5', ['med' => number_format($med, 2, ',', '.')]);
    }

    public function abrirFormExer6(){
        return view('exer6');
    }
    public function respostaExer6(Request $request){
        $celsius = $request->celsius;
        $temp = ($celsius * (9/5)) + 32;
        return view('exer6', ['temp' => $temp]);
    }

    public function abrirFormExer7(){
        return view('exer7');
    }
    public function respostaExer7(Request $request){
        $tempF = $request->tempF;
        $temp = (5 / 9) * ($tempF - 32);
        return view('exer7', ['temp' => $temp]);
    }

    public function abrirFormExer8(){
        return view('exer8');
    }

    public function respostaExer8(Request $request){
        $valor1 = $request->valor1;
        $valor2 = $request->valor2;
        $area = $valor1 * $valor2;
        return view('exer8', ['area' => $area]);
    }

    public function abrirFormExer9(){
        return view('exer9');
    }

    public function respostaExer9(Request $request){
        $raio = $request->raio;
        $area = 3.14 * ($raio ** 2);
        return view('exer9', ['area' => $area]);
    }  
    
    public function abrirFormExer10(){
        return view('exer10');
        }
        
        public function respostaExer10(Request $request){
            $largura = $request->largura;
            $altura = $request->altura;
            $perimetro = 2 * ($largura + $altura);
        return view('exer10', ['peri' => $perimetro]);
    }

    public function abrirFormExer11(){
        return view('exer11');
    }

    public function respostaExer11(Request $request){
        $raio = $request->raio;
        $perimetro = 2 * 3.14 * $raio;
        return view('exer11', ['peri' => $perimetro]);
    }

    public function abrirFormExer12(){
        return view('exer12');
    }

    public function respostaExer12(Request $request){
        $base = $request->base;
        $expoente = $request->expoente;
        $resultado = $base ** $expoente;
        return view('exer12', compact('resultado', 'base', 'expoente'));
    }

    public function abrirFormExer13(){
        return view('exer13');
    }

    public function respostaExer13(Request $request){
        $metro = $request->metro;
        $cm = $metro * 100;
        return view('exer13', ['cm' => $cm]);
    }

    public function abrirFormExer14(){
        return view('exer14');
    }

    public function respostaExer14(Request $request){
        $km = $request->km;
        $milha = $km / 1.609;
        return view('exer14', ['milha' => number_format($milha, 2, ',', '.')]);
    }

    public function abrirFormExer15(){
        return view('exer15');
    }

    public function respostaExer15(Request $request){
        $peso = $request->peso;
        $altura = $request->altura;
        $imc = $peso / ($altura * $altura);
        return view('exer15', ['imc' => number_format($imc, 2, ',', '.')]);
    }
}

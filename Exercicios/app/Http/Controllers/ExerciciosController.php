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
}

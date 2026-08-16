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
         $valor1 = $_POST['valor1'];
        $valor2 = $_POST['valor2'];
        $valor3 = $_POST['valor3'];
        $med = ($valor1 + $valor2 + $valor3) / 3;
        return view('exer5', ['med' => number_format($med, 2, ',', '.')]);
    }
}

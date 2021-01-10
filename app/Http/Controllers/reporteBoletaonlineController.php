<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\pdfBoletaonlineController;

use App\Notas;
use App\Seccion;
use App\NotasItems;
use App\EvaluacionesPeriodo;
use App\InfoCentroEducativo;
use App\Periodoevaluacion;
use App\Expedienteestudiante;
use Laracasts\Flash\Flash;

class reporteBoletaonlineController extends Controller
{
  public function index($id, $anio)
  {
  //	dd($anio);
 $sec=Seccion::whereHas('seccion_estudiante', function($query) use ($anio,$id){
$query->where([['tb_matriculaestudiante.anio','=',$anio],['tb_matriculaestudiante.estudiante_id','=',$id],['tb_matriculaestudiante.v_estadomatricula','aprobada']]);
})->first();
//dd($sec);
if($sec!=null)//hi esta matriculado
{
    $centroEscolar = InfoCentroEducativo::first();
    $seccion_id = $sec->id;
    $notas   = Notas::where('seccion_id', $seccion_id)->get();
    
    if(!count($notas)>0)//si no hay notas registradas
    {
      
    }
    $itemsNotas = $this->orderStudentNota($notas,$id);
    //dd($notas);
    $evaluaciones = EvaluacionesPeriodo::orderby('codigo_eval', 'asc')->get();
    $profesor = $sec->seccion_empleado;
    //dd($profesor);
    //$periodo = Periodoevaluacion::find(3);
    //dd($periodo);
    $students = DB::table('tb_expedienteestudiante')
      ->join('tb_matriculaestudiante', 'tb_matriculaestudiante.estudiante_id', '=', 'tb_expedienteestudiante.id')
      ->where('tb_matriculaestudiante.estudiante_id', $id)
      ->where('tb_matriculaestudiante.seccion_id', $seccion_id)
      ->select(
        "tb_expedienteestudiante.v_nombres",
        "tb_expedienteestudiante.v_apellidos",
        "tb_expedienteestudiante.v_nie",
        "tb_expedienteestudiante.v_expediente")
      ->orderBy('tb_expedienteestudiante.v_apellidos', 'asc')
      ->orderBy('tb_expedienteestudiante.v_nombres', 'asc')
      ->get();
//dd($students);
    $pdf = new pdfBoletaonlineController("L","mm","Letter");
    foreach ($students as $value) {
      $pdf->AddPage();
      $pdf->headerBoletaNotas($centroEscolar);

      $pdf->boletaTitulo([
        "seccion" =>  $sec,
        "alumno" => $value,
        "profesor"  => $profesor
      ]);
  
      $pdf->tableNotesBoleta([
        //"periodo" => $periodo->descripcion,
        "notas"   => @$itemsNotas,
        "eva"     => $evaluaciones,
        "students"  => $students 
      ]);

      $pdf->footerNotesBoletas([
        "profesor"  => $profesor,
        "centro"    => $centroEscolar
      ]);

      $pdf->FooterConstancia();
    }

    return response()->make($pdf->Output(), 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="doc.pdf"'
    ]);
}//fin del else
  }

   public function orderStudentNota($notas = array(),$id)
  {
    $result = array();
    foreach ($notas as $item) {
      
      foreach ($item->notas as $value) {
         $result[$value->alumno->v_expediente=$id]['notas'][$item->asignatura->asignatura][$item->periodo->nombre][$item->evaluacion->codigo_eval] = floatval($value->calificacion) * (floatval($item->evaluacion->d_porcentajeActividad)/100);


      }
    }
    return $result;
  }
}



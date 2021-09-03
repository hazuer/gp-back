<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\orderWork;
use App\Models\catDesignInks;


use Illuminate\Support\Str;
use Carbon\Carbon;

class deliveryOrdersController extends Controller
{
    //function get list delivery orders
    public function deliveryOrdersList(Request $req)
    {
        try {

            $query = orderWork::leftJoin('datos_usuario', 'datos_usuario.id_dato_usuario', 'orden_trabajo.id_operador_responsable')
                ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', 'orden_trabajo.id_cliente_autoriza')
                ->leftJoin('cat_estatus_ot', 'cat_estatus_ot.id_cat_estatus_ot', 'orden_trabajo.id_cat_estatus_ot')
                ->leftJoin('cat_diseno', 'cat_diseno.id_cat_diseno', 'orden_trabajo.id_cat_diseno')
                ->leftJoin('diseno_tinta', 'diseno_tinta.id_cat_diseno', 'cat_diseno.id_cat_diseno')
                ->leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'diseno_tinta.id_cat_tinta')
                ->select(
                    'orden_trabajo.id_orden_trabajo',
                    'orden_trabajo.orden_trabajo_of',
                    'orden_trabajo.fecha_creacion',
                    'orden_trabajo.fecha_entrega',
                    'orden_trabajo.fecha_cobro',
                    'orden_trabajo.fecha_cierre_orden',
                    'orden_trabajo.id_operador_responsable',
                    'datos_usuario.nombre as nombre_operador',
                    'datos_usuario.apellido_paterno as ap_paterno_operador',
                    'datos_usuario.apellido_materno as ap_materno_operador',
                    'orden_trabajo.id_cliente_autoriza',
                    'cat_cliente.nombre_cliente',
                    'orden_trabajo.id_cat_estatus_ot',
                    'cat_estatus_ot.estatus_ot',
                    'orden_trabajo.id_cat_diseno',
                    'cat_diseno.nombre_diseno'
                )
                ->where('orden_trabajo.id_cat_planta', auth()->user()->id_cat_planta);


            //if search has design name
            if ($req->has('nombre_diseno') && !is_null($req->nombre_diseno)) {
                $query->orWhereRaw("cat_diseno.nombre_diseno  LIKE '%" . $req->nombre_diseno . "%'");
            }
            //if search has design description
            if ($req->has('descripcion') && !is_null($req->descripcion)) {
                $query->orWhereRaw("cat_diseno.descripcion  LIKE '%" . $req->descripcion . "%'");
            }

            //if search has ink name
            if ($req->has('nombre_tinta') && !is_null($req->nombre_tinta)) {
                $query->orWhereRaw("cat_tinta.nombre_tinta  LIKE '%" . $req->nombre_tinta . "%'")
                    ->where('diseno_tinta.id_cat_estatus', 1);
            }

            //if search contain plan
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_diseno.id_cat_planta', '=', $req->id_cat_planta);
            }
            //if search contain status
            if ($req->has('id_cat_estatus_ot') && !is_null($req->id_cat_estatus_ot)) {
                $query->Where('orden_trabajo.id_cat_estatus_ot', '=', $req->id_cat_estatus_ot);
            }

            //method sort
            $direction  = "ASC";
            //if request has orderBy 
            $sortField = $req->has('ordenarPor') && !is_null($req->ordenarPor) ? $req->ordenarPor : 'estatus';

            if (Str::of($sortField)->startsWith('-')) {
                $direction  = "DESC";
                $sortField = Str::of($sortField)->substr(1);
            }
            switch ($sortField) {
                case 'nombre_diseno':
                    $sortField = "cat_diseno.nombre_diseno";
                    break;
                case 'descripcion':
                    $sortField = "cat_diseno.descripcion";
                    break;
                case 'nombre_planta':
                    $sortField = "cat_planta.nombre_planta";
                    break;
                case 'estatus':
                    $sortField = "orden_trabajo.id_cat_estatus_ot";
                    break;
            }
            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $deliveryOrdersTotal = $query->count('orden_trabajo.id_orden_trabajo'); //total rows
            $deliveryOrdersList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result

            return response()->json([
                'result' => true,
                'deliveryOrdersTotal' =>  $deliveryOrdersTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($deliveryOrdersTotal / $perPage),
                'deliveryOrdersList' => $deliveryOrdersList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function registerdeliveryOrder()
    {
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\orderWork;
use App\Models\inkDetailsWorkOrders;
use App\Models\userData;
use App\Models\catCustomers;
use App\Models\catDesign;
use App\Models\catMachines;
use App\Models\catStatusOW;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class deliveryOrdersController extends Controller
{

    public function deliveryOrderDataToList()
    {
        try {
            //operators list
            $operatorsList = userData::leftJoin('usuario', 'usuario.id_dato_usuario', 'datos_usuario.id_dato_usuario')
                ->select(
                    'usuario.id_usuario',
                    DB::raw('CONCAT(datos_usuario.nombre," ",datos_usuario.apellido_paterno," ",datos_usuario.apellido_materno) AS nombre_operador_responsable')
                )
                ->where('usuario.id_cat_planta', auth()->user()->id_cat_planta)
                ->whereIn('usuario.id_cat_perfil', [2, 4])
                ->where('id_cat_estatus', 1)
                ->get();
            //customers list
            $customersList = catCustomers::select('id_cat_cliente', 'nombre_cliente')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();
            //status order work  list
            $statusOWList = catStatusOW::select('id_cat_estatus_ot', 'estatus_ot')
                ->get();

            //designs list
            $machinesList = catMachines::select('id_cat_maquina', 'nombre_maquina')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();
            //designs list
            $designsList = catDesign::select('id_cat_diseno', 'nombre_diseno')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();

            return response()->json([
                'result' => true,
                'operatorsList' => $operatorsList,
                'customersList' =>  $customersList,
                'statusOWList' => $statusOWList,
                'machinesList' => $machinesList,
                'designsList' => $designsList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }




    //function get list delivery orders
    public function deliveryOrdersList(Request $req)
    {
        try {

            $query = orderWork::leftJoin('usuario', 'usuario.id_usuario', 'orden_trabajo.id_operador_responsable')
                ->leftJoin('datos_usuario', 'datos_usuario.id_dato_usuario', 'usuario.id_dato_usuario')
                ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', 'orden_trabajo.id_cliente_autoriza')
                ->leftJoin('cat_estatus_ot', 'cat_estatus_ot.id_cat_estatus_ot', 'orden_trabajo.id_cat_estatus_ot')
                ->leftJoin('cat_maquina', 'cat_maquina.id_cat_maquina', 'orden_trabajo.id_cat_maquina')
                ->leftJoin('cat_diseno', 'cat_diseno.id_cat_diseno', 'orden_trabajo.id_cat_diseno')
                ->leftJoin('ot_detalle_tinta', 'ot_detalle_tinta.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                ->leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'ot_detalle_tinta.id_cat_tinta')
                ->select(
                    'orden_trabajo.id_orden_trabajo',
                    'orden_trabajo.orden_trabajo_of',
                    'orden_trabajo.fecha_creacion',
                    'orden_trabajo.id_operador_responsable',
                    DB::raw('CONCAT(datos_usuario.nombre," ",datos_usuario.apellido_paterno," ",datos_usuario.apellido_materno) AS nombre_operador_responsable'),
                    'orden_trabajo.id_cliente_autoriza',
                    'cat_cliente.nombre_cliente',
                    'orden_trabajo.id_cat_estatus_ot',
                    'cat_estatus_ot.estatus_ot',
                    'orden_trabajo.id_cat_maquina',
                    'cat_maquina.nombre_maquina',
                    'orden_trabajo.id_cat_diseno',
                    'cat_diseno.nombre_diseno',
                    'orden_trabajo.adiciones'
                )
                ->where('orden_trabajo.id_cat_planta', auth()->user()->id_cat_planta);

            //valid user profile
            switch (auth()->user()->id_cat_perfil) {

                case 2;
                    $query->where('orden_trabajo.id_operador_responsable', auth()->user()->id_usuario);
                    break;
                case 4;
                    //if search  has user id
                    if ($req->has('id_usuario') && !is_null($req->id_usuario)) {
                        $query->orWhere('orden_trabajo.id_operador_responsable', '=', $req->id_usuario);
                    }
                    //if search  has customer
                    if ($req->has('id_cat_cliente') && !is_null($req->id_cat_cliente)) {
                        $query->orWhere('orden_trabajo.id_cliente_autoriza', '=', $req->id_cat_cliente);
                    }
                    break;
                default;
            }

            $query->distinct();


            //if search has orden_trabajo_of name
            if ($req->has('orden_trabajo_of') && !is_null($req->orden_trabajo_of)) {
                $query->orWhereRaw("orden_trabajo.orden_trabajo_of  LIKE '%" . $req->orden_trabajo_of . "%'");
            }
            //if search has dates
            if ($req->has('fecha_inicio') && !is_null($req->fecha_inicio) && $req->has('fecha_fin') && !is_null($req->fecha_fin)) {
                $query->whereBetween('orden_trabajo.fecha_creacion', [$req->fecha_inicio . ' 00:00:00', $req->fecha_fin . ' 23:59:59']);
            }
            //if search contain status
            if ($req->has('id_cat_diseno') && !is_null($req->id_cat_diseno)) {
                $query->orWhere('orden_trabajo.id_cat_diseno', '=', $req->id_cat_diseno);
            }

            //if search has ink name
            if ($req->has('nombre_tinta') && !is_null($req->nombre_tinta)) {
                $query->WhereRaw("cat_tinta.nombre_tinta  LIKE '%" . $req->nombre_tinta . "%'");
            }

            //if search contain status
            if ($req->has('id_cat_estatus_ot') && !is_null($req->id_cat_estatus_ot)) {
                $query->Where('orden_trabajo.id_cat_estatus_ot', '=', $req->id_cat_estatus_ot);
            }

            //if search contain status
            if ($req->has('adiciones') && !is_null($req->adiciones)) {
                $query->Where('orden_trabajo.adiciones', '=', $req->adiciones);
            }

            //method sort
            $direction  = "ASC";
            //if request has orderBy 
            $sortField = $req->has('ordenarPor') && !is_null($req->ordenarPor) ? $req->ordenarPor : '-id_orden_trabajo';

            if (Str::of($sortField)->startsWith('-')) {
                $direction  = "DESC";
                $sortField = Str::of($sortField)->substr(1);
            }
            switch ($sortField) {
                case 'orden_trabajo_of':
                    $sortField = "orden_trabajo.orden_trabajo_of";
                    break;
                case 'nombre_operador_responsable':
                    $sortField = "datos_usuario.nombre";
                    break;
                case 'nombre_cliente':
                    $sortField = "cat_cliente.nombre_cliente";
                    break;
                case 'estatus_ot':
                    $sortField = "cat_estatus_ot.estatus_ot";
                    break;
                case 'nombre_maquina':
                    $sortField = "cat_maquina.nombre_maquina";
                    break;
                case 'nombre_diseno':
                    $sortField = "cat_diseno.nombre_diseno";
                    break;
                case 'id_orden_trabajo':
                    $sortField = "orden_trabajo.id_orden_trabajo";
                    break;
            }
            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $deliveryOrdersTotal = $query->count('orden_trabajo.id_orden_trabajo'); //total rows
            $deliveryOrders = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result
            //final list
            $deliveryOrdersList = array();
            // orders construction 
            foreach ($deliveryOrders as $deliveryOrder) {
                $inks = array(); //inks to each designs
                //query to get inks 
                $inksList = inkDetailsWorkOrders::leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'ot_detalle_tinta.id_cat_tinta')
                    ->where('ot_detalle_tinta.id_orden_trabajo', $deliveryOrder->id_orden_trabajo)
                    ->get();
                //each ink to ink details work order
                foreach ($inksList as $ink) {
                    //add array inks 
                    array_push($inks, array(
                        'nombre_tinta' => $ink->nombre_tinta,
                        'codigo_cliente' => $ink->codigo_cliente,
                        'codigo_gp' => $ink->codigo_cliente,
                        'aditivo' => $ink->aditivo == 0 ? 'No' : 'Si',
                    ));
                }
                //add array final list
                array_push($deliveryOrdersList, array(
                    'id_orden_trabajo'  => $deliveryOrder->id_orden_trabajo,
                    'orden_trabajo_of'  => $deliveryOrder->orden_trabajo_of,
                    'fecha_creacion'  => $deliveryOrder->fecha_creacion,
                    'id_operador_responsable'  => $deliveryOrder->id_operador_responsable,
                    'nombre_operador_responsable'  => $deliveryOrder->nombre_operador_responsable,
                    'id_cliente_autoriza'  => $deliveryOrder->id_cliente_autoriza,
                    'nombre_cliente'  => $deliveryOrder->nombre_cliente,
                    'id_cat_estatus_ot' => $deliveryOrder->id_cat_estatus_ot,
                    'estatus_ot'  => $deliveryOrder->estatus_ot,
                    'id_cat_maquina' => $deliveryOrder->id_cat_maquina,
                    'nombre_maquina'  => $deliveryOrder->nombre_maquina,
                    'id_cat_diseno' => $deliveryOrder->id_cat_diseno,
                    'nombre_diseno' => $deliveryOrder->nombre_diseno,
                    'tintas' => $inks, //all orders inks
                    'adiciones' => $deliveryOrder->adiciones
                ));
            }
            return response()->json([
                'result' => true,
                'deliveryOrdersTotal' => $deliveryOrdersTotal,
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

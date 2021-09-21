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
use App\Models\catShift;
use App\Models\catInks;
use App\Models\catTaras;
use App\Models\catReasons;
use App\Models\catDesignInks;
use App\Models\plantFolio;
use App\Models\catReading;
use App\Http\Controllers\ComunFunctionsController;

use App\Http\Requests\DeliveryOrders\registerOERequest;
use App\Http\Requests\DeliveryOrders\updateOEResquest;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class deliveryOrdersController extends Controller
{

    //resources list delivery orders
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

            //machines list
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
                ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', 'orden_trabajo.id_cat_cliente')
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
                    'orden_trabajo.id_cat_cliente',
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
            //sort list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $deliveryOrdersTotal = $query->count('orden_trabajo.id_orden_trabajo'); //total rows
            $deliveryOrdersList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result
            // //final list
            // $deliveryOrdersList = array();
            // // orders construction 
            // foreach ($deliveryOrders as $deliveryOrder) {
            //     $inks = array(); //inks to each designs
            //     //query to get inks 
            //     $inksList = inkDetailsWorkOrders::leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'ot_detalle_tinta.id_cat_tinta')
            //         ->where('ot_detalle_tinta.id_orden_trabajo', $deliveryOrder->id_orden_trabajo)
            //         ->get();
            //     //each ink to ink details work order
            //     foreach ($inksList as $ink) {
            //         //add array inks 
            //         array_push($inks, array(
            //             'nombre_tinta' => $ink->nombre_tinta,
            //             'codigo_cliente' => $ink->codigo_cliente,
            //             'codigo_gp' => $ink->codigo_cliente,
            //             'aditivo' => $ink->aditivo == 0 ? 'No' : 'Si',
            //         ));
            //     }
            //     //add array final list
            //     array_push($deliveryOrdersList, array(
            //         'id_orden_trabajo'  => $deliveryOrder->id_orden_trabajo,
            //         'orden_trabajo_of'  => $deliveryOrder->orden_trabajo_of,
            //         'fecha_creacion'  => $deliveryOrder->fecha_creacion,
            //         'id_operador_responsable'  => $deliveryOrder->id_operador_responsable,
            //         'nombre_operador_responsable'  => $deliveryOrder->nombre_operador_responsable,
            //         'id_cliente_autoriza'  => $deliveryOrder->id_cliente_autoriza,
            //         'nombre_cliente'  => $deliveryOrder->nombre_cliente,
            //         'id_cat_estatus_ot' => $deliveryOrder->id_cat_estatus_ot,
            //         'estatus_ot'  => $deliveryOrder->estatus_ot,
            //         'id_cat_maquina' => $deliveryOrder->id_cat_maquina,
            //         'nombre_maquina'  => $deliveryOrder->nombre_maquina,
            //         'id_cat_diseno' => $deliveryOrder->id_cat_diseno,
            //         'nombre_diseno' => $deliveryOrder->nombre_diseno,
            //         'tintas' => $inks, //all orders inks
            //         'adiciones' => $deliveryOrder->adiciones
            //     ));
            // }
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

    //resources regiter delivery oder
    public function registerDeliveryOrderResources()
    {
        try {
            //operator
            $operator = userData::leftJoin('usuario', 'usuario.id_dato_usuario', 'datos_usuario.id_dato_usuario')
                ->select(
                    DB::raw('CONCAT(nombre," ",apellido_paterno," ",apellido_materno) AS nombre_operador_responsable')
                )
                ->where('usuario.id_usuario', auth()->user()->id_usuario)
                ->first();
            //customer
            $customer = catCustomers::select('nombre_cliente')
                ->where('id_cat_cliente', auth()->user()->id_cat_cliente)
                ->first();

            //status order work  list
            $statusOWList = catStatusOW::select('id_cat_estatus_ot', 'estatus_ot')
                ->get();

            //desings
            $designsList = catDesign::select('id_cat_diseno', 'nombre_diseno')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();
            //machines list
            $machinesList = catMachines::select('id_cat_maquina', 'nombre_maquina')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();

            //Shifts
            $shiftsList = catShift::all();
            //taras
            $tarasList = catTaras::select('id_cat_tara', 'nombre_tara')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();
            //Reasons   
            $reasonsList = catReasons::select('id_cat_razon', 'razon')
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->where('id_cat_estatus', 1)
                ->get();

            //additives
            $additivesList = catInks::select('id_cat_tinta', 'nombre_tinta', 'codigo_cliente', 'codigo_gp', 'aditivo')
                ->where('id_cat_estatus', 1)
                ->where('aditivo', 1)
                ->where('id_cat_planta', auth()->user()->id_cat_planta)
                ->get();
            //cat reading  
            $catReading = catReading::all();


            return response()->json([
                'result' => true,
                'operator' => $operator,
                'customer' =>  $customer,
                'statusOWList' =>  $statusOWList,
                'designsList' => $designsList,
                'machinesList' => $machinesList,
                'shiftsList' => $shiftsList,
                'tarasList' => $tarasList,
                'reasonsList' => $reasonsList,
                'additivesList' => $additivesList,
                'catReading' => $catReading
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //get inks Design
    public function getInkDesing(Request $req)
    {
        try {
            $inksList = catDesignInks::leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'diseno_tinta.id_cat_tinta')
                ->select(
                    'cat_tinta.id_cat_tinta',
                    'cat_tinta.nombre_tinta',
                    'cat_tinta.codigo_cliente',
                    'cat_tinta.codigo_gp',
                    'cat_tinta.aditivo'
                )
                ->where('diseno_tinta.id_cat_diseno', $req->id_cat_diseno)
                ->where('cat_tinta.id_cat_estatus', 1)
                ->get();

            return response()->json([
                'result' => true,
                'inksList' => $inksList,
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //register OE
    public function registerdeliveryOrder(registerOERequest $req)
    {
        DB::beginTransaction();

        try {
            //variables
            $user = auth()->user()->id_usuario;
            $plant = auth()->user()->id_cat_planta;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');
            $customer = auth()->user()->id_cat_cliente;
            //folio
            $folioDB = plantFolio::where('id_cat_planta', $plant)
                ->where('folio_entrega', '<>', Null)
                ->first();
            if ($folioDB) {
                $folio = $folioDB->folio_entrega + 1;
            } else {
                $folio = 1;
            }
            //adiciones
            $adicciones = 0;
            $collection = collect($req->tintas);
            if ($collection->contains('aditivo', true)) {
                $adicciones = 1;
            }


            $newOrder = new orderWork;
            $newOrder->orden_trabajo_of = $req->orden_trabajo_of;
            $newOrder->id_cat_maquina = $req->id_cat_maquina;
            $newOrder->id_cat_diseno = $req->id_cat_diseno;
            $newOrder->cantidad_programado = $req->cantidad_programado;
            $newOrder->peso_total = $req->peso_total;
            $newOrder->id_cat_turno = $req->id_cat_turno;
            $newOrder->linea = $req->linea;
            $newOrder->id_cat_planta = $plant;
            $newOrder->id_operador_responsable = $user;
            $newOrder->fecha_cierre_orden = $req->fecha_cierre_orden;
            $newOrder->adiciones = $adicciones;
            $newOrder->folio_entrega = $folio;
            $newOrder->id_cat_estatus_ot = 1;
            $newOrder->id_cat_cliente = $customer;
            $newOrder->id_usuario_crea =  $user;
            $newOrder->fecha_creacion = $dateNow;
            $newOrder->save();

            $pintInks = array(); //final array response
            //
            foreach ($req->tintas as $tinta) {

                $newDetail = new inkDetailsWorkOrders;
                $newDetail->id_orden_trabajo = $newOrder->id_orden_trabajo;
                $newDetail->id_cat_tinta = $tinta['id_cat_tinta'];
                $newDetail->lote = $tinta['lote'];
                $newDetail->id_cat_tara = $tinta['id_cat_tara'];
                $newDetail->peso_individual = $tinta['peso_individual'];
                $newDetail->utiliza_ph = $tinta['utiliza_ph'];
                $newDetail->mide_viscosidad = $tinta['mide_viscosidad'];
                $newDetail->utiliza_filtro = $tinta['utiliza_filtro'];
                $newDetail->porcentaje_variacion = $tinta['porcentaje_variacion'];
                $newDetail->id_cat_estatus = 1;
                $newDetail->peso_individual_gp = $tinta['peso_individual_gp'];
                $newDetail->id_cat_lectura_gp = $tinta['id_cat_lectura_gp'];
                $newDetail->id_cat_razon = $tinta['id_cat_razon'];
                $newDetail->aditivo_tinta = $tinta['aditivo_tinta'];
                $newDetail->id_usuario_crea = $user;
                $newDetail->fecha_creacion =  $dateNow;
                $newDetail->save();

                array_push($pintInks, array(
                    'id_cat_tinta' => $tinta['id_cat_tinta'],
                    'url' => url('/imprimirQr/' . $newDetail->id_ot_detalle_tinta)
                ));
            }


            if (auth()->user()->id_cat_perfil == 2 && $collection->contains('id_cat_lectura_gp', 1)) {
                //register log OE
                (new  ComunFunctionsController)->registerLogs(
                    $user,
                    'orden_trabajo',
                    $newOrder->id_orden_trabajo,
                    'Captura manual por supervisor GP - creación OE',
                    $plant
                );
            }

            //commit
            DB::commit();

            //reponse
            return response()->json([
                'result' => true,
                'message' => 'La orden de entrega se creo correctamente',
                'folio' => $folio,
                'printInks' => $pintInks

            ], 200);
        } catch (\Exception $exception) {

            //
            DB::rollback();
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
    //pint Qr
    public function getQr($id)
    {
        try {

            $dataQr = inkDetailsWorkOrders::leftJoin('orden_trabajo', 'orden_trabajo.id_orden_trabajo', 'ot_detalle_tinta.id_orden_trabajo')
                ->leftJoin('cat_diseno', 'cat_diseno.id_cat_diseno', 'orden_trabajo.id_cat_diseno')
                ->leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'ot_detalle_tinta.id_cat_tinta')
                ->select(
                    'ot_detalle_tinta.id_ot_detalle_tinta',
                    'cat_tinta.nombre_tinta',
                    'orden_trabajo.folio_entrega',
                    'orden_trabajo.orden_trabajo_of',
                    'cat_diseno.nombre_diseno'
                )->where('id_ot_detalle_tinta', $id)
                ->first();

            //return view
            return view('imprimirQr', compact('dataQr', $dataQr));
        } catch (\Exception $exception) {
            //
            DB::rollback();
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
    //get OE
    public function getDeliveryOrder(Request $req)
    {
        try {

            $deliveryOrderData = [];
            //get delivery
            $deliveryOrderData['deliveryOrder'] = orderWork::leftJoin('usuario', 'usuario.id_usuario', 'orden_trabajo.id_operador_responsable')
                ->leftJoin('datos_usuario', 'datos_usuario.id_dato_usuario', 'usuario.id_dato_usuario')
                ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', 'orden_trabajo.id_cat_cliente')
                ->leftJoin('cat_estatus_ot', 'cat_estatus_ot.id_cat_estatus_ot', 'orden_trabajo.id_cat_estatus_ot')
                ->leftJoin('cat_maquina', 'cat_maquina.id_cat_maquina', 'orden_trabajo.id_cat_maquina')
                ->leftJoin('cat_diseno', 'cat_diseno.id_cat_diseno', 'orden_trabajo.id_cat_diseno')
                ->leftJoin('cat_turno', 'cat_turno.id_cat_turno', 'orden_trabajo.id_cat_turno')
                ->select(
                    'orden_trabajo.id_orden_trabajo',
                    'orden_trabajo.orden_trabajo_of',
                    'orden_trabajo.fecha_creacion',
                    'orden_trabajo.id_operador_responsable',
                    DB::raw('CONCAT(datos_usuario.nombre," ",datos_usuario.apellido_paterno," ",datos_usuario.apellido_materno) AS nombre_operador_responsable'),
                    'orden_trabajo.id_cat_cliente',
                    'cat_cliente.nombre_cliente',
                    'orden_trabajo.id_cat_estatus_ot',
                    'cat_estatus_ot.estatus_ot',
                    'orden_trabajo.id_cat_maquina',
                    'cat_maquina.nombre_maquina',
                    'orden_trabajo.id_cat_diseno',
                    'cat_diseno.nombre_diseno',
                    'orden_trabajo.id_cat_turno',
                    'cat_turno.turno',
                    'orden_trabajo.cantidad_programado',
                    'orden_trabajo.peso_total',
                    'orden_trabajo.linea',
                    'orden_trabajo.fecha_cierre_orden',
                    'orden_trabajo.folio_entrega'
                )
                ->where('orden_trabajo.id_orden_trabajo', $req->id_orden_trabajo)
                ->get();


            //get inks and additives delivery order
            $deliveryOrderData['inks'] = inkDetailsWorkOrders::leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'ot_detalle_tinta.id_cat_tinta')
                ->leftJoin('cat_tara', 'cat_tara.id_cat_tara', 'ot_detalle_tinta.id_cat_tinta')
                ->leftJoin('cat_lectura', 'cat_lectura.id_cat_lectura', 'ot_detalle_tinta.id_cat_lectura_gp')
                ->leftJoin('cat_razon', 'cat_razon.id_cat_razon', 'ot_detalle_tinta.id_cat_razon')
                ->select(
                    'ot_detalle_tinta.id_ot_detalle_tinta',
                    'ot_detalle_tinta.id_orden_trabajo',
                    'ot_detalle_tinta.id_cat_tinta',
                    'cat_tinta.nombre_tinta',
                    'cat_tinta.codigo_cliente',
                    'cat_tinta.codigo_gp',
                    'cat_tinta.aditivo',
                    'ot_detalle_tinta.lote',
                    'ot_detalle_tinta.id_cat_tara',
                    'cat_tara.nombre_tara',
                    'ot_detalle_tinta.peso_individual',
                    'ot_detalle_tinta.utiliza_ph',
                    'ot_detalle_tinta.mide_viscosidad',
                    'ot_detalle_tinta.utiliza_filtro',
                    'ot_detalle_tinta.porcentaje_variacion',
                    'ot_detalle_tinta.peso_individual_gp',
                    'ot_detalle_tinta.id_cat_lectura_gp',
                    'cat_lectura.lectura',
                    'ot_detalle_tinta.id_cat_razon',
                    'cat_razon.razon',
                    'ot_detalle_tinta.aditivo_tinta'
                )
                ->where('ot_detalle_tinta.id_orden_trabajo', $req->id_orden_trabajo)
                ->get();
            //reponse
            return response()->json([
                'result' => true,
                'deliveryOrderData' => $deliveryOrderData,
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
    //Update delivery order
    public function updateDeliveryOrder(updateOEResquest $req)
    {
        DB::beginTransaction();
        try {
            //variables
            $user = auth()->user()->id_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');
            $plant = auth()->user()->id_cat_planta;
            //get order 
            $updateOrder = orderWork::find($req->id_orden_trabajo);
            //valid status OE
            switch ($updateOrder->id_cat_estatus_ot) {
                case 1: //new
                case 3: //updated
                    $updateOrder->orden_trabajo_of = $req->orden_trabajo_of;
                    $updateOrder->id_cat_maquina = $req->id_cat_maquina;
                    $updateOrder->cantidad_programado = $req->cantidad_programado;
                    $updateOrder->peso_total = $req->peso_total;
                    $updateOrder->id_cat_turno = $req->id_cat_turno;
                    $updateOrder->linea = $req->linea;
                    $updateOrder->fecha_cierre_orden = $req->fecha_cierre_orden;
                    //if design change
                    if ($updateOrder->id_cat_diseno != $req->id_cat_diseno) {
                        $updateOrder->id_cat_diseno = $req->id_cat_diseno;
                        inkDetailsWorkOrders::where('id_orden_trabajo', $req->id_orden_trabajo)->delete();
                    }
                    $updateOrder->id_cat_estatus_ot = 3; //update
                    $updateOrder->id_usuario_modifica =  $user;
                    $updateOrder->fecha_modificacion = $dateNow;
                    break;
            }
            //aditti validation
            $collection = collect($req->tintas);
            if ($collection->contains('aditivo', true)) {
                $updateOrder->adiciones = 1;
            }
            //update delivery order
            $updateOrder->save();
            //inks array
            $pintInks = array();
            //new or update inks
            foreach ($req->tintas as $tinta) {

                $updateOrnNew = inkDetailsWorkOrders::firstOrNew([
                    'id_orden_trabajo' => $req->id_orden_trabajo,
                    'id_cat_tinta' => $tinta['id_cat_tinta']
                ]);
                $updateOrnNew->lote = $tinta['lote'];
                $updateOrnNew->id_cat_tara = $tinta['id_cat_tara'];
                $updateOrnNew->peso_individual = $tinta['peso_individual'];
                $updateOrnNew->utiliza_ph = $tinta['utiliza_ph'];
                $updateOrnNew->mide_viscosidad = $tinta['mide_viscosidad'];
                $updateOrnNew->utiliza_filtro = $tinta['utiliza_filtro'];
                $updateOrnNew->porcentaje_variacion = $tinta['porcentaje_variacion'];
                $updateOrnNew->peso_individual_gp = $tinta['peso_individual_gp'];
                $updateOrnNew->id_cat_lectura_gp = $tinta['id_cat_lectura_gp'];
                $updateOrnNew->id_cat_razon = $tinta['id_cat_razon'];
                $updateOrnNew->aditivo_tinta = $tinta['aditivo_tinta'];
                //if register exists
                if ($updateOrnNew->exists) {
                    $updateOrnNew->id_usuario_crea = $user;
                    $updateOrnNew->fecha_creacion =  $dateNow;
                } else {
                    $updateOrnNew->id_cat_estatus = 1;
                    $updateOrnNew->id_usuario_crea =  $user;
                    $updateOrnNew->fecha_creacion =  $dateNow;
                }

                $updateOrnNew->save();

                array_push($pintInks, array(
                    'id_cat_tinta' => $updateOrnNew->id_cat_tinta,
                    'url' => url('/imprimirQr/' . $updateOrnNew->id_ot_detalle_tinta)
                ));
            }

            //register log OE
            if (auth()->user()->id_cat_perfil == 2 && $collection->contains('id_cat_lectura_gp', 1)) {

                (new  ComunFunctionsController)->registerLogs(
                    $user,
                    'orden_trabajo',
                    $req->id_orden_trabajo,
                    'Captura manual por supervisor GP - creación OE',
                    $plant
                );
            }

            //commit
            DB::commit();
            //return response 
            return response()->json([
                'result' => true,
                'message' => 'La orden de entrega se actulizo correctamente',
                'printInks' => $pintInks
            ], 200);
        } catch (\Exception $exception) {
            //if someting wrong rollback
            DB::rollback();
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}

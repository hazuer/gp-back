<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\orderWork;
use App\catCustomers;
use App\Http\Requests\RegisterCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Requests\ActiveDeactiveDeleteCustomerRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;

class catCustomersController extends Controller
{

    //function get customers list
    public function customersList(Request $req)
    {
        try {

            $query = catCustomers::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_cliente.id_cat_estatus')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'cat_cliente.id_cat_planta')
                ->select(
                    'cat_cliente.id_cat_cliente',
                    'cat_cliente.nombre_cliente',
                    'cat_planta.nombre_planta',
                    'cat_estatus.estatus'
                );

            //if search has reazon
            if ($req->has('nombre_cliente') && !is_null($req->nombre_cliente)) {
                $query->orWhereRaw("cat_cliente.nombre_cliente  LIKE '%" . $req->nombre_cliente . "%'");
            }

            //if search contain country
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_cliente.id_cat_planta', '=', $req->id_cat_planta);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->orWhere('cat_cliente.id_cat_estatus', '=', $req->id_cat_estatus);
            }

            //method sort
            $direction  = "ASC";
            //if request has orderBy 
            $sortField = $req->has('ordenarPor') && !is_null($req->ordenarPor) ? $req->ordenarPor : 'id_cat_estatus';

            if (Str::of($sortField)->startsWith('-')) {
                $direction  = "DESC";
                $sortField = Str::of($sortField)->substr(1);
            }
            switch ($sortField) {
                case 'nombre_cliente':
                    $sortField = "cat_cliente.nombre_cliente";
                    break;
                case 'id_cat_planta':
                    $sortField = "cat_cliente.id_cat_planta";
                    break;
                case 'id_cat_estatus':
                    $sortField = "cat_cliente.id_cat_estatus";
                    break;
            }
            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $customersTotal = $query->count(); //total rows
            $customersList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'customersTotal' =>  $customersTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($customersTotal / $perPage),
                'customersList' => $customersList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    //function register customer
    public function registerCustomer(RegisterCustomerRequest $req)
    {
        try {
            //valid if customer exists
            if (catCustomers::where('nombre_cliente', $req->nombre_cliente)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una cliente con este nombre asignada a esta planta"
                ], 401);
            }
            //register customer
            $newCustomer = new  catCustomers;
            $newCustomer->nombre_cliente = $req->nombre_cliente;
            $newCustomer->id_cat_planta = $req->id_cat_planta;
            $newCustomer->id_cat_estatus = 1;
            $newCustomer->id_usuario_crea = auth()->user()->id_dato_usuario;
            $newCustomer->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newCustomer->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de cliente con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar cliente"
                ], 401);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function update customer
    public function updateCustomer(UpdateCustomerRequest $req)
    {
        try {
            //valid if customer exists
            if (catCustomers::where('nombre_cliente', $req->nombre_cliente)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('id_cat_cliente', '<>', $req->id_cat_cliente)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una cliente con este nombre asignada a esta planta"
                ], 401);
            }
            //update customer
            $updateCustomer =   catCustomers::find($req->id_cat_cliente);
            $updateCustomer->nombre_cliente = $req->nombre_cliente;
            $updateCustomer->id_cat_planta = $req->id_cat_planta;
            $updateCustomer->id_usuario_modifica = auth()->user()->id_dato_usuario;
            $updateCustomer->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($updateCustomer->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de cliente con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar cliente"
                ], 401);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    //function active, deactive or delete customer
    public function activeDeactiveDeleteCustomer(ActiveDeactiveDeleteCustomerRequest $req)
    {
        try {
            //validation if customer will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {
                //count num orders deliveries that doesn't closed
                $numOrderDeliveries = orderWork::leftJoin('entrega', 'entrega.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->where('entrega.id_cliente_autoriza', $req->id_cat_cliente)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                //count num orders return that doesn't closed
                $numOrdersReturn = orderWork::leftJoin('devolucion', 'devolucion.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->where('devolucion.id_cliente_devuelve', $req->id_cat_cliente)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                if ($numOrderDeliveries > 0 || $numOrdersReturn > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "El cliente no puede ser desactivado o eliminado, aun tiene ordenes de trabajo sin terminar"
                    ], 201);
                }
            }
            //update status customer
            $updateCustomerstatus = catCustomers::find($req->id_cat_cliente);
            $updateCustomerstatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if customer will be delete
            if ($req->id_cat_estatus == 3) {
                $updateCustomerstatus->id_usuario_elimina = auth()->user()->id_dato_usuario;
                $updateCustomerstatus->fecha_eliminacion = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $updateCustomerstatus->id_usuario_modifica = auth()->user()->id_dato_usuario;
                $updateCustomerstatus->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            }

            if ($updateCustomerstatus->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de estatus con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar estatus"
                ], 401);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\catPlants;
use App\Models\catCustomers;
use App\Models\userData;
use App\Http\Requests\Users\RegisterRequest;
use App\Http\Requests\Users\LoginRequest;
use App\Http\Requests\Users\ForgotRequest;
use App\Mail\resetPassword;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ComunFunctionsController;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class userController extends Controller
{

    /////register form data origin/////
    public function getFormRegisterData()
    {
        try {
            //get plants 
            $catPlants = catPlants::select('id_cat_planta', 'nombre_planta')
                ->where('id_cat_estatus', 1)
                ->where('id_cat_estatus', 1)
                ->get();
            //get customers     
            $catCustomers = catCustomers::select('id_cat_cliente', 'nombre_cliente')
                ->where('id_cat_estatus', 1)
                ->where('id_cat_estatus', 1)
                ->get();

            return response()->json([
                'result' => true,
                'plants' => $catPlants,
                'customers' => $catCustomers

            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    /////////register function////////
    public function register(RegisterRequest $req)
    {

        DB::beginTransaction();

        try {
            $newUserData =  new userData;
            $newUserData->nombre = $req->nombre;
            $newUserData->apellido_paterno = $req->apellido_paterno;
            $newUserData->apellido_materno = $req->apellido_materno;
            $newUserData->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            $newUserData->save();

            $newUser = new User;
            $newUser->id_dato_usuario = $newUserData->id_dato_usuario;
            $newUser->correo = $req->correo;
            $newUser->id_cat_planta = $req->id_cat_planta;
            $newUser->id_cat_cliente = $req->id_cat_cliente;
            $newUser->id_cat_estatus = 5; // status waiting
            $newUser->save();

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => "Registro exitoso, esperar autorización"
            ], 201);
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

    /////login function///// 
    public function logIn(LoginRequest $req)
    {
        try {
            //get user

            $user = User::where('correo', $req->correo)->first();

            //if user doesn't exist 
            if (is_null($user)) {
                return response()->json([
                    'result' => false,
                    'message' => "El usuario no existe",
                ], 404);
            }

            //if user is not active 
            if (!is_null($user)  &&  $user->id_cat_estatus != 1) {
                return response()->json([
                    'result' => false,
                    'message' => "El usuario no esta activo ",
                ], 401);
            }
            //valid password
            if (!is_null($user) && Hash::check($req->password, $user->password)) {
                //create token
                $token = $user->createToken('Laravel')->accessToken;
                //login success

                $data = userData::leftJoin('usuario', 'usuario.id_dato_usuario', '=', 'datos_usuario.id_dato_usuario')
                    ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', '=', 'usuario.id_cat_cliente')
                    ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', '=', 'usuario.id_cat_planta')
                    ->leftJoin('cat_perfil', 'cat_perfil.id_cat_perfil', '=', 'usuario.id_cat_perfil')
                    ->select(
                        'usuario.id_dato_usuario',
                        'datos_usuario.nombre',
                        'datos_usuario.apellido_paterno',
                        'datos_usuario.apellido_materno',
                        'cat_perfil.perfil',
                        'cat_planta.nombre_planta',
                        'cat_cliente.nombre_cliente',
                        'usuario.id_cat_perfil',
                        'usuario.id_cat_cliente',
                        'usuario.id_cat_planta',
                        'usuario.id_cat_estatus'
                    )
                    ->where('usuario.id_dato_usuario', $user->id_dato_usuario)
                    ->first();

                return response()->json([
                    'result' => true,
                    'data' => [
                        'token' => $token,
                        'user' => [
                            'userId' => $data->id_dato_usuario,
                            'name' => $data->nombre,
                            'last_name' => $data->apellido_paterno,
                            'secondary_last_name' => $data->apellido_materno
                        ],
                        'catPlant' => [
                            'plantId' => $data->id_cat_planta,
                            'plantName' => $data->nombre_planta,
                        ],
                        'catProfile' => [
                            'profileId' => $data->id_cat_perfil,
                            'nameProfile' => $data->perfil,
                        ],
                        'catCustomer' => [
                            'customerId' => $data->id_cat_cliente,
                            'customerName' => $data->nombre_cliente
                        ]
                    ],
                    'message' => "Login Success",
                ], 200);
            } else {
                //credencial invalid
                return response()->json([
                    'result' => false,
                    'message' => 'Credenciales Invalidas'
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


    ///forgot function////
    public function resetPassword(ForgotRequest $req)
    {
        try {
            //get email value
            $correo = $req->correo;
            //valid if email exist
            if (User::where('id_cat_estatus', 1)->where('correo', $correo)->doesntExist()) {
                return response()->json([
                    'result' => false,
                    'message' => "El correo no existe"
                ], 404);
            }
            //generate password 
            $password = (new  ComunFunctionsController)->generatePassword();
            //update user password
            User::where('correo', $correo)
                ->update(['password' => Hash::make($password)]);
            //send email
            $subject = 'Recuperacion De Contraseña';
            Mail::to($correo)->send(new resetPassword($password, $subject));
            //reponse
            return response()->json([
                'result' => true,
                'message' => "Recuperacion exitosa, verifica tu email."
            ], 201);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }



    ////logOut function /////
    public function logOut()
    {
        try {
            //get user 
            $user = auth()->user();
            //delete token 
            $user->tokens->each(function ($token, $key) {
                $token->delete();
            });
            return response()->json(
                [
                    'result' => true,
                    'message' => "Logout success"
                ],
                200
            );
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json(
                [
                    'result' => false,
                    'message' => $exception->getMessage()
                ],
                500
            );
        }
    }
}

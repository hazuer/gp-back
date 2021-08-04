<?php

namespace App\Http\Controllers;


use App\User;
use App\catPlants;
use App\catCustomers;
use App\userData;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotRequest;
use App\Mail\resetPassword;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ComunFunctionsController;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class userController extends Controller
{

    /////register form data origin/////
    public function getFormRegisterData()
    {
        try {
            //get plants 
            $catPlants = catPlants::select('id_cat_planta', 'nombre_planta')
                ->where('id_cat_estatus', 1)
                ->get();
            //get customers     
            $catCustomers = catCustomers::select('id_cat_cliente', 'nombre_cliente')
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
        try {
            $newUserData =  new userData;
            $newUserData->correo = $req->correo;
            $newUserData->nombre = $req->nombre;
            $newUserData->apellido_paterno = $req->apellido_paterno;
            $newUserData->apellido_materno = $req->apellido_materno;
            $newUserData->id_cat_planta = $req->id_cat_planta;
            $newUserData->id_cat_cliente = $req->id_cat_cliente;
            $newUserData->id_cat_estatus = 5; // status waiting
            $newUserData->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newUserData->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro exitoso, esperar autorización"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "error registro"
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
                return response()->json([
                    'result' => true,
                    'token' => $token,
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
            Mail::to($correo)->send(new resetPassword($password));
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

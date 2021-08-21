<?php

namespace App\Http\Middleware;

use Closure;

class accessAdminSupervisor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->id_cat_perfil == 1 || auth()->user()->id_cat_perfil == 4) {
            return $next($request);
        } else {

            return response()->json([
                'result' => false,
                'message' => "Esta acción no está autorizada"
            ], 401);
        }
    }
}

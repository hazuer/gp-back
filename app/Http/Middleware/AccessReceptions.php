<?php

namespace App\Http\Middleware;

use Closure;

class AccessReceptions
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
        if (auth()->user()->id_cat_perfil == 3 || auth()->user()->id_cat_perfil == 6) {
            return $next($request);
        } else {

            return response()->json([
                'result' => false,
                'message' => "Esta acción no está autorizada"
            ], 401);
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarAdministrador
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // A conferência acontece no servidor, mesmo se a URL for digitada manualmente.
        abort_unless($request->user()?->role === 'ADM', 403, 'Acesso permitido somente ao administrador.');
        return $next($request);
    }
}

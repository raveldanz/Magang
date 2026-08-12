<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini');
        }
        return $next($request);
    }
}

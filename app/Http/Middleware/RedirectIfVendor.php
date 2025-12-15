<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika vendor mencoba akses route internal (bukan route vendor), redirect ke vendor.index
        // Tapi jangan redirect jika sedang di route vendor sendiri untuk menghindari loop
        if (Auth::guard('vendor')->check() && !$request->is('vendor*')) {
            return redirect()->route('vendor.index');
        }

        return $next($request);
    }
}

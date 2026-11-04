<?php

namespace App\http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {

        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'No autorizado'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

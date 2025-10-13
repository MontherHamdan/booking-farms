<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFarmOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!auth()->user()->isFarmOwner()) {
            return response()->json([
                'message' => 'Unauthorized. Farm owner access required.'
            ], 403);
        }

        return $next($request);
    }
}
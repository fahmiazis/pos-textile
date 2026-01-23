<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('nds-token');

        if (!$token) {
            return response()->json([
                'message' => 'Token required'
            ], 401);
        }

        $tokenData = ApiToken::where('token', $token)
            ->where('expired_at', '>', now())
            ->first();

        if (!$tokenData) {
            return response()->json([
                'message' => 'Token expired or invalid'
            ], 401);
        }

        auth()->loginUsingId($tokenData->user_id);

        // inject user id ke request
        $request->merge([
            'auth_user_id' => $tokenData->user_id
        ]);

        return $next($request);
    }
}

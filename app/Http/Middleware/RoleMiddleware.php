<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = $user->role;

        foreach ($roles as $role) {

            $requiredRole = UserRole::tryFrom($role);

            if ($requiredRole && $userRole === $requiredRole) {
                return $next($request);
            }
        }

        return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }
}

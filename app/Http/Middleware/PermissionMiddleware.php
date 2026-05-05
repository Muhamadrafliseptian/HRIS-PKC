<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key)
    {
        $user = Auth::user();
        $origin_menu = Menu::where('key', $key)->first();


        if ($origin_menu->is_active == 0) {
            return Inertia::render('Error/403');
        }

        if ($user->is_super == 1) {
            return $next($request);
        }

        $perms = explode(',', $user->permission);

        if ($origin_menu == null) {
            return Inertia::render('Error/403');
        }

        if (in_array($origin_menu->id, $perms) == false) {
            return Inertia::render('Error/403');
        }
        return $next($request);
    }
}

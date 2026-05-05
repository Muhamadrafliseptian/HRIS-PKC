<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhitelistApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelistedIps = [
            // '127.0.0.1',
            // '193.203.173.92', // warehouse and dapur sehat
            // '185.124.137.152', // hris,
            // '103.150.100.24', // pradana.site
        ];

        $clientIp = $request->ip();

        if (!in_array($clientIp, $whitelistedIps) && count($whitelistedIps) > 0) {
            $error = [
                'status' => false,
                'message' => 'Ip Anda Tidak Termasuk di White List Server',
                'validation_errors' => null,
                'params' => null
            ];
            return response()->json($error, 500);
        }

        return $next($request);
    }
}

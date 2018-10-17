<?php

namespace App\Http\Middleware;

use Closure;

class IpLimit
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
        $ip = $request->getClientIp();
        $trustIps = config('auth.trust_ips');
        if (!in_array($ip, $trustIps)) {
            return response("Forbidden", 403);
        }

        return $next($request);
    }
}

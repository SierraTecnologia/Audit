<?php

namespace Audit\Http\Middleware;

use Closure;
use Audit\Services\AuditsService;

class Audits
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->ajax()) {
            app(AuditsService::class)->log($request);
        }

        return $next($request);
    }
}

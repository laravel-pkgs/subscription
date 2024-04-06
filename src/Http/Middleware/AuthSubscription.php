<?php

namespace IICN\Subscription\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthSubscription
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard(config('subscription.guard'))->check()) {
            return $next($request);
        } else {
            abort(401);
        }
    }
}

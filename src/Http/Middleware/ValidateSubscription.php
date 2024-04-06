<?php

namespace IICN\Subscription\Http\Middleware;

use Closure;
use IICN\Subscription\Subscription;

class ValidateSubscription
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
    public function handle($request, Closure $next, string $type)
    {
        if (Subscription::canUse($type)) {
            return $next($request);
        } else {
            abort(403, 'You do not have access to this Ability');
        }
    }
}

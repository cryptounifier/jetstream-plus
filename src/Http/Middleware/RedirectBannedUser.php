<?php

namespace CryptoUnifier\JetstreamPlus\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\StatefulGuard;

class RedirectBannedUser
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new middleware instance.
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (method_exists($request->user(), 'isBanned') && $request->user()->isBanned()) {
            return inertia('Auth/Banned', [
                'reason' => DB::table('bans')
                    ->where('bannable_type', 'App\Models\User')
                    ->where('bannable_id', $request->user()->id)
                    ->value('comment'),
            ]);
        }

        return $next($request);
    }
}

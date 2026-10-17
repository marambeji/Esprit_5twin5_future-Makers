<?php

namespace App\Http\Middleware;

use App\Models\Distributor;
use Closure;
use Illuminate\Http\Request;

class EnsureDistributor
{
    public function handle(Request $request, Closure $next)
    {
        $distributor = Distributor::where('user_id', $request->user('web')?->id)->where('actif', true)->first();
        abort_unless($distributor, 403, 'Espace réservé aux distributeurs actifs.');
        $request->attributes->set('distributor', $distributor);

        return $next($request);
    }
}

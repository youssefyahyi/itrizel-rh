<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotSalarie
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user && $user->isSalarie()) {
            if ($user->employe_id) {
                return redirect()->route('personnel.show', $user->employe_id)
                    ->with('info', 'Accès restreint à votre fiche.');
            }
            abort(403, 'Accès non autorisé.');
        }
        return $next($request);
    }
}

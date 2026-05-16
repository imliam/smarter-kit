<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTeamUrlDefaults
{
    /**
     * Set the default URL parameters for team-based routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $team = $user->currentTeam ?? $user->personalTeam();

            if ($team && ! $user->currentTeam) {
                $user->update(['current_team_id' => $team->id]);
                $user->setRelation('currentTeam', $team);
            }

            if ($team) {
                URL::defaults([
                    'current_team' => $team->slug,
                    'team' => $team->slug,
                ]);
            }
        }

        return $next($request);
    }
}

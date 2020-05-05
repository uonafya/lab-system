<?php

namespace App\Http\Middleware;

use App\CovidConsumption;
use Closure;

class ConsumptionSubmission
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
        if (in_array(auth()->user()->user_type_id, [null])) {
            // Check if COVID consumption has been submitted
            $covid = new CovidConsumption;
            if ($covid->lastweekConsumption()->isEmpty()){
                return redirect('/pending');
            }
        }
        return $next($request);
    }
}

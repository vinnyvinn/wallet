<?php
/**
 * Created by PhpStorm.
 * User: mosesgathecha
 * Date: 05/03/2019
 * Time: 11:13
 */

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;

class IpMiddleware
{

    public function handle($request, Closure $next)
    {
        $ip = ':::1';
        $accessIp=$request->ip();
        Log::notice("The Access Ip :" . $accessIp . "==" . $ip);

        Log::notice( strcasecmp(':::1', ':::1')."   ".strcasecmp($accessIp, $ip). " ".strlen($accessIp). " ".strlen($ip));


        if (strcasecmp($request->ip(), $ip) != 0) {
            return "Page not found";
        }

        return $next($request);
    }

}
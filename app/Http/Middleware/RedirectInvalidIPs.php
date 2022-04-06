<?php
/**
 * Created by PhpStorm.
 * User: mosesgathecha
 * Date: 05/03/2019
 * Time: 12:58
 */

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;

class RedirectInvalidIPs
{

    /**
     * List of valid IPs.
     *
     * @var array
     */
    /**
     * List of valid IP-ranges.
     *
     * @var array
     */

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Closure|\Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    //    foreach ($request->getClientIps() as $ip) {
    //        Log::info($ip);
    //        if (!$this->isValidIp($ip) && !$this->isValidIpRange($ip)) {
    //         Log::info('Ip not in valid range');
    //        }

    //    }

        return $next($request);
    }

    /**
     * Check if the given IP is valid.
     *
     * @param $ip
     * @return bool
     */
    protected function isValidIp($ip)
    {
        return in_array($ip, explode(',', env('ALLOWED_IPS')));
    }

    /**
     * Check if the ip is in the given IP-range.
     *
     * @param $ip
     * @return bool
     */
    protected function isValidIpRange($ip)
    {
        return IpUtils::checkIp($ip, explode(',', env('ALLOWED_IPS')));
    }
}


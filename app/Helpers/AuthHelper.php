<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Traits\DataTransferTrait;
use Illuminate\Support\Facades\DB;

class AuthHelper
{
    use DataTransferTrait;
    public function getAuthenticatedUser(Request $request)
    {
        $url = env('USERS_ENDPOINT'). 'user';
        $user_response =  json_decode((new self)->serviceGetRequest($url, $request->header('Authorization')));
        if (isset($user_response)) {
            $user = DB::table('lp_users')->where('id', $user_response->data->user->id)->first();
            return $user;
        } else {
            throw new \Exception('Session has expired.Login and try again');
        }
    }
}
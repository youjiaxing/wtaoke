<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class WeChatAutoLogin
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
        $userData = session('wechat.oauth_user.default');
        if (!\Auth::user() || \Auth::user()->weixin_openid != $userData['id']) {
            \Auth::login(User::where('weixin_openid', $userData['id'])->first(), true);
        }

        return $next($request);
    }
}

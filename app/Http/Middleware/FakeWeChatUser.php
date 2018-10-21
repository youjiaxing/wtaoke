<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Overtrue\Socialite\User as SocialiteUser;

class FakeWeChatUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $limit = null)
    {
        // 使用ip校验
        if ($limit == 'ip') {
            $ip = $request->getClientIp();
            $trustIps = config('auth.trust_ips');
            if (!in_array($ip, $trustIps)) {
                return $next($request);
            }
        } else {
            // 使用环境校验
            if (!app()->environment($limit)) {
                return $next($request);
            }
        }

        $id = $request->input('id', null);
        if ($id) {
            $id = intval($id);
            $user = User::find($id);

            if ($user) {
                $weChatUser = new SocialiteUser([
                    'id' => array_get($user, 'weixin_openid'),
                    'name' => array_get($user, 'name'),
                    'nickname' => array_get($user, 'name'),
                    'avatar' => array_get($user, 'avatar'),
                    'email' => null,
                    'original' => [],
                    'provider' => 'WeChat',
                ]);
                session(['wechat.oauth_user.default' => $weChatUser]); // 同理，`default` 可以更换为您对应的其它配置名
            }
        }

        return $next($request);
    }
}

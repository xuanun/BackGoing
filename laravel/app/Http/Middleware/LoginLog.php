<?php


namespace App\Http\Middleware;
use App\Models\LoginLogs;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Cookie;
use Illuminate\Support\Facades\Redis;
use Redirect;

class LoginLog extends Middleware
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // TODO
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token');
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        $data = json_decode($cacheValue, true);
        $user_id = $data['id'];
        $user_name = $data['user_name'];
        $account = $data['account'];
        $browser_version = $request->input('browser_version');
        $system_version = $request->input('system_version');
        // 往下执行
        $response = $next($request);
        //记录日志
        // 取得用户的token
        $return_data = $response->original;
        $token = '';
        $url_path = $request->path();
        $url_path_arr = explode("/",$url_path);
        $code = $return_data['code'];
        $web_ip = $request->ip();
        $type = '';
        //判断操作类型
        if (strpos($url_path_arr[2], 'login') !== false ) {
            $type = '登录';
        } elseif(strpos($url_path_arr[2], 'logout') !== false) {
            $type = '退出';
        }
        if($code == 20000){
            if($type == '登录'){
                $token = $return_data['data']['user']['token'];
                $redis = Redis::connection('default');
                $cacheKey = "travel_user_login_".$token;
                $cacheValue = $redis->get($cacheKey);
                $data = json_decode($cacheValue, true);
                $user_id = $data['id'];
                $user_name = $data['user_name'];
                $account = $data['account'];
            }
            $type = $type.'成功';
            $status = '成功';
        }else{
            $account = $request->input('account');
            $status = '失败';
            $type = $type.'失败';
        }
        $model_Log = new LoginLogs();
        $model_Log->addData($type, $user_id, $web_ip, $user_name, $account, $status, $browser_version, $system_version);
        return  $response;
    }

}

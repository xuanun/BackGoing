<?php


namespace App\Http\Middleware;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Cookie;
use Illuminate\Support\Facades\Redis;
use Redirect;

class AuthLog extends Middleware
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
        // 往下执行
        $response = $next($request);
        //记录日志
        // 取得用户的token
        $token = $request->header('token');
        $url_path = $request->path();
        $url_path_arr = explode("/",$url_path);
        //判断模块
        $modular = '';
        if (strpos($url_path_arr[1], 'account') !== false ) {
            $modular = '登录及退出';
        } elseif(strpos($url_path_arr[1], 'role') !== false) {
            $modular = '角色管理';
        } elseif(strpos($url_path_arr[1], 'org') !== false) {
            $modular = '组织机构';
        }elseif (strpos($url_path_arr[1], 'index') !== false ) {
            $modular = '用户管理';
        }elseif (strpos($url_path_arr[1], 'aut') !== false ) {
            $modular = '实名认证';
        }elseif (strpos($url_path_arr[1], 'audit') !== false ) {
            $modular = '审核';
        }elseif (strpos($url_path_arr[1], 'issue') !== false ) {
            $modular = '发布';
        }elseif (strpos($url_path_arr[1], 'video') !== false ) {
            $modular = '视频';
        }elseif (strpos($url_path_arr[1], 'park') !== false ) {
            $modular = '停车场';
        }elseif (strpos($url_path_arr[1], 'area') !== false ) {
            $modular = '区域';
        }elseif (strpos($url_path_arr[1], 'bike') !== false ) {
            $modular = '自行车站点';
        }elseif (strpos($url_path_arr[1], 'msg') !== false ) {
            $modular = '推送消息';
        } elseif (strpos($url_path_arr[1], 'survey') !== false ) {
            $modular = '网上调查';
        }elseif (strpos($url_path_arr[1], 'publicity') !== false ) {
            $modular = '安全宣传';
        }
        //判断操作类型
        if (strpos($url_path_arr[2], 'add') !== false ) {
            $type = '新增';
        } elseif(strpos($url_path_arr[2], 'edit') !== false) {
            $type = '修改';
        } elseif(strpos($url_path_arr[2], 'del') !== false) {
            $type = '删除';
        }elseif (strpos($url_path_arr[2], 'batch_ident') !== false ) {
            $type = '认证审核';
        }elseif (strpos($url_path_arr[2], 'examine') !== false ) {
            $type = '审核';
        }elseif (strpos($url_path_arr[2], 'release') !== false ) {
            $type = '发布';
        }else{
            $type = '查看';
        }
        $path_url = $request->url();
        $param = json_encode($request->all(), true);
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        $data = json_decode($cacheValue, true);
        $user_id = $data['id'];
        $user_name = $data['user_name'];
        $user_phone = $data['phone'];
        $user_dept = $data['department_name'];
        $return_data = $response->original;
        $code = 404;
        if(isset($return_data['code'])){
            $code = $return_data['code'];
        }
        $web_ip = $request->ip();
        $status = '操作失败';
        if($code == 20000)
            $status = '操作成功';

        $model_Log = new Logs();
        $model_Log->addData($type, $modular, $path_url, $param, $user_id, $user_name, $user_phone, $user_dept, $web_ip, $status);
        return  $response;
    }

}

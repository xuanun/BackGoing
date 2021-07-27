<?php


namespace App\Http\Controllers\account;


use App\Http\Controllers\Controller;
use App\Models\AppIdent;
use App\Models\Department;
use App\Models\Permissions;
use App\Models\Report;
use App\Models\RolePermissions;
use App\Models\RoleUsers;
use App\Models\ShootHandy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /**
     * 用户登录
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $input = $request->all();
        $account = $input['account'] ? $input['account'] : '';
        $password = $input['password'] ? $input['password'] : '';

//        $obj_password = encrypt($password);
//        return $obj_password;
        if(empty($account)) return response()->json(['code'=>60000,'msg'=>'参数错误, 账号不能为空', 'data'=>[]]);
        if(empty($password)) return response()->json(['code'=>60000,'msg'=>'参数错误, 密码不能为空', 'data'=>[]]);
        $token = Str::random (64);
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        $model_user = new User();
        if(!empty($cacheValue)){
            $data = json_decode($cacheValue, true);
        }else{
            $object = $model_user->getUserInfoByAccount($account);
            if(empty($object)) return response()->json(['code'=>60000,'msg'=>'参数错误, 账户不存在', 'data'=>[]]);
            $data =  json_decode(json_encode($object),true);
        }
        if(empty($data)) return response()->json(['code'=>40000,'msg'=>'账号不存在', 'data'=>[]]);
        $obj_password = $data['password'];
        $obj_password = decrypt($obj_password);
//        return $obj_password;
        if($password != $obj_password) return response()->json(['code'=>40000,'msg'=>'密码不正确',  'data'=>[]]);
        $return = $model_user->UserLogin($data['id']);
        if($return['code'] == 20000){
            $return['data']['user']['id'] = $data['id'];
            $return['data']['user']['token'] = $token;
            $return['data']['user']['user_name'] = $data['user_name'];
            $return['data']['user']['nick_name'] = $data['nick_name'];
            $return['data']['user']['account'] = $data['account'];
            $return['data']['user']['avatar'] = empty($data['avatar']) ? $data['avatar'] : env('IMAGE_URL').$data['avatar'];
            $return['data']['user']['gander'] = $data['gander'];
            $return['data']['user']['register_time'] = $data['register_time'];
            $return['data']['user']['login_time'] = $data['login_time'];
            $return['data']['user']['phone'] = $data['phone'];
            $return['data']['user']['department_id'] = $data['department_id'];
            $return['data']['user']['created_time'] = $data['created_time'];
            $return['data']['user']['updated_time'] = $data['updated_time'];
            $return['time'] = time();
            $user_key = "travel_user".$account;
            $old_token = $redis->get($user_key);
            if($old_token)
            {
                $old_cacheKey = "travel_user_login_".$old_token;
                $redis->del($old_cacheKey);
            }
            $redis->set($user_key, $token);
        }
        $redis->set($cacheKey, json_encode($data));
        return response()->json($return);
    }

    /**
     * 用户退出登录
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $token = $request->header('token');
        if(empty($token)) return response()->json(['code'=>50000,'msg'=>'用户未登录',  'data'=>[]]);
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        if(!empty($cacheValue)){
            $data = json_decode($cacheValue, true);
        }else{
            return response()->json(['code'=>50000,'msg'=>'你的登录信息已失效',  'data'=>[]]);
        }
        $account = $data['account'];
        $user_key = "travel_user".$account;
        $redis->del($user_key);
        $redis->del($cacheKey);
        return response()->json(['code'=>20000,'msg'=>'退出登录成功', 'data'=>[]]);
    }

    /**
     * 用户修改密码
     * @param Request $request
     * @return mixed
     */
    public function editPassword(Request $request)
    {
        $input = $request->all();
        $token = $request->header('token');
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        $model_user = new User();
        if(!empty($cacheValue)){
            $data = json_decode($cacheValue, true);
        }
        else {
            return response()->json(['code'=>40000,'msg'=>'token 已经失效', 'data'=>[]]);
        }
        $old_password = $input['old_password'] ? $input['old_password'] : '';
        $password = $input['password'] ? $input['password'] : '';
        $enterPassword = $input['password1'] ? $input['password1'] : '';
        if(empty($old_password)) return response()->json(['code'=>60000,'msg'=>'原始密码不能为空','data'=>[]]);
        if(empty($password)) return response()->json(['code'=>60000,'msg'=>'新密码不能为空', 'data'=>[]]);
        if(empty($enterPassword)) return response()->json(['code'=>60000,'msg'=>'确认密码不能为空', 'data'=>[]]);
        if($password != $enterPassword) return response()->json(['code'=>40000,'msg'=>'两次密码输入不一致', 'data'=>[]]);
        if($old_password == $password) return response()->json(['code'=>40000,'msg'=>'新密码不能与旧密码一样', 'data'=>[]]);
        if($old_password !=  decrypt($data['password']))
            return response()->json(['code'=>40000,'msg'=>'原密码不正确','data'=>[]]);

        $user_id = $data['id'];
        $e_password = encrypt($password);
        $return_data = $model_user->editUserPassword($user_id, $e_password);
        return response()->json($return_data);
    }

    /**
     * 用户修改资料
     * @param Request $request
     * @return mixed
     */
    public function editUserInfo(Request $request)
    {
        $input = $request->all();
        $avatar = isset($input['avatar']) ? $input['avatar'] : 0;
        if(empty($avatar)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadAvatar($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $avatar = $upload_data['file_name'];
        }
        $dept_id = isset($input['dept_id']) ? $input['dept_id'] : 0;
        $phone = isset($input['phone']) ? $input['phone'] : 0;
        if(empty($avatar) || empty($phone) || empty($dept_id)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        $token = $request->header('token');
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        $model_user = new User();
        if(!empty($cacheValue)){
            $data = json_decode($cacheValue, true);
        }
        else {
            return response()->json(['code'=>50000,'msg'=>'token 已经失效', 'data'=>[]]);
        }
        $user_id = $data['id'];
        $return_data = $model_user->editUserInfo($user_id, $avatar, $dept_id, $phone);
        return response()->json($return_data);
    }

    /**
     * 上传头像
     * @param $tmp
     * @return mixed
     */
    public function uploadAvatar($tmp)
    {
        if(empty($tmp)) return ['code'=>40000,'msg'=>'文件流不存在', 'data'=>[]];
        if ($tmp->isValid())
        { //判断文件上传是否有效
            $FileType = $tmp->getClientOriginalExtension(); //获取文件后缀
            $FilePath = $tmp->getRealPath(); //获取文件临时存放位置
            $FileName = date('Ymd') . uniqid() . '.' . $FileType; //定义文件名
            Storage::disk('avatar')->put($FileName, file_get_contents($FilePath)); //存储文件
            $IMAGE_URL = env('IMAGE_URL');
            $AVATAR_URL= env('AVATAR_URL');
            $data['url'] = $IMAGE_URL.$AVATAR_URL. $FileName;
            $data['code'] = 20000;
            $data['file_name'] = $AVATAR_URL.$FileName;
            return $data;
        }
        return ['code'=>40000,'msg'=>'文件不存在', 'data'=>[]];
    }

    /**
     * 获取所有部门
     * @param Request $request
     * @return mixed
     */
    public function getAllDept(Request $request)
    {
        $input = $request->all();
        $model_department = new Department();
        $dept_data = $model_department->getAll();
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$dept_data];
        return response()->json($return_data);
    }

    /**
     * 获取违章类型
     * @return mixed
     */
    public function getAllViolation()
    {
        $array = [
            [
                'id'=>1,
                'name'=>'违停',
            ],
            [
                'id'=>2,
                'name'=>'违法',
            ],
            [
                'id'=>3,
                'name'=>'其他',
            ]
        ];
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$array];
        return response()->json($return_data);
    }

    /**
     * 获取审核状态
     * @param Request $request
     * @return mixed
     */
    public function getAllExamine(Request $request)
    {
        $array = [
            [
                'id'=>0,
                'name'=>'审核未通过',
            ],
            [
                'id'=>1,
                'name'=>'审核通过',
            ],
            [
                'id'=>2,
                'name'=>'审核中',
            ]
        ];
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$array];
        return response()->json($return_data);
    }

    /**
     * 认证查询
     * @param Request $request
     * @return mixed
     */
    public function getAppUserList(Request $request)
    {
        $input = $request->all();
        $web_user_ids = isset($input['web_user_id']) ? $input['web_user_id'] : ''; //认证人用户ID集合
        //$app_user_id = isset($input['app_user_id']) ? $input['app_user_id'] : 0; //app用户ID
        $audit_type = isset($input['audit_type']) ? $input['audit_type'] : ''; //认证状态
        $real_name = isset($input['real_name']) ? $input['real_name'] : ''; //姓名
        $gender = isset($input['gender']) ? $input['gender'] : ''; //性别
        $country = isset($input['country']) ? $input['country'] : ''; //国籍
        $audit_category = isset($input['audit_category']) ? $input['audit_category'] : ''; //证件类型
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $user_star = isset($input['user_star']) ? $input['user_star'] : '';//客户星级
        $id_card_no =  isset($input['id_card_no']) ? $input['id_card_no'] : '';//证件号码
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $page_size = isset($input['page_size']) ? $input['page_size'] : 10;//每页条数
        $page =  isset($input['page']) ? $input['page'] : 1;//页数
        $model_app_ident = new AppIdent();
        $ident_data = $model_app_ident->getList($audit_type, $real_name, $gender, $country, $audit_category,$start_time, $end_time, $user_star, $id_card_no, $phone, $web_user_ids, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$ident_data];
        return response()->json($return_data);
    }

    /**
     * 认证审核
     * @param Request $request
     * @return mixed
     */
    public function editIdent(Request $request)
    {
        $input = $request->all();
        $ident_id = isset($input['ident_id']) ? $input['ident_id'] : ''; //认证ID
        $old_audit_type = isset($input['old_audit_type']) ? $input['old_audit_type'] : ''; //认证状态
        $audit_type = isset($input['audit_type']) ? $input['audit_type'] : ''; //认证状态
        $audit_remarks = isset($input['audit_remarks']) ? $input['audit_remarks'] : ''; //审核说明
        $audit_web_user_id = isset($input['user_id']) ? $input['user_id'] : ''; //审核人
        if($audit_type == '') return response()->json(['code'=>40000,'msg'=>'认证状态不能为空', 'data'=>[]]);
        if($old_audit_type == 0) return response()->json(['code'=>40000,'msg'=>'已审核,未通过,不能再次审核', 'data'=>[]]);
        if($old_audit_type == 1) return response()->json(['code'=>40000,'msg'=>'已审核,已通过,不能再次审核', 'data'=>[]]);
        $model_app_ident = new AppIdent();
        $return_data = $model_app_ident->editIdent($ident_id, $audit_type, $audit_remarks, $audit_web_user_id);
        return response()->json($return_data);
    }

    /**
     * 批量认证审核
     * @param Request $request
     * @return mixed
     */
    public function batchEditIdent(Request $request)
    {
        $input = $request->all();
        $ident_ids = isset($input['ident_ids']) ? $input['ident_ids'] : []; //认证ID
        $audit_type = isset($input['audit_type']) ? $input['audit_type'] : ''; //认证状态
        $audit_remarks = isset($input['audit_remarks']) ? $input['audit_remarks'] : ''; //审核说明
        $audit_web_user_id = isset($input['user_id']) ? $input['user_id'] : ''; //审核人
        if(empty($ident_ids)) return response()->json(['code'=>40000, 'msg'=>'ID列表不能为空', 'data'=>[]]);
        $model_app_ident = new AppIdent();
        $exist_type = $model_app_ident->existAuditType($ident_ids, 2);
        if($exist_type) return response()->json(['code'=>40000,'msg'=>'所选列表中审核状态不全为未审核', 'data'=>[]]);
        $return_data = $model_app_ident->batchEditAudit($ident_ids, $audit_type, $audit_remarks, $audit_web_user_id);
        return response()->json($return_data);
    }

    /**
     * 首页未审核消息
     * @param Request $request
     * @return mixed
     */
    public function MessageAmount(Request $request)
    {
        $input = $request->all();
        $return_data = array();
        $return_data['sum_amount'] = 0;
        $token = $request->header('token');
        //实名认证
        $url_path = 'api/aut/app_user_list';
        $audit_status = $this->checkPer($token, $url_path);
        if($audit_status)
        {
            $model_app_ident = new AppIdent();
            $audit_count = $model_app_ident->getAuditCount( 2);
            $return_data['audit_count'] = $audit_count;
            $return_data['sum_amount'] = $audit_count;
        }
        //随手拍审核
        $url_path = 'api/audit/edit_shoot_handy';
        $shoot_handy_status = $this->checkPer($token, $url_path);
        if($shoot_handy_status)
        {
            $model_shoot_handy = new ShootHandy();
            $shoot_handy_count = $model_shoot_handy->getShootHandyCount(2);
            $return_data['shoot_handy_count'] = $shoot_handy_count;
            $return_data['sum_amount'] = $return_data['sum_amount'] + $shoot_handy_count;
        }
        $model_report = new Report();
        //路况审核
        $url_path = 'api/audit/road_status_list';
        $accident_status = $this->checkPer($token, $url_path);
        if($accident_status)
        {
            $type = 1 ; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
            $accident_count = $model_report->getReportCount($type, 2);
            $return_data['accident_count'] = $accident_count;
            $return_data['sum_amount'] = $return_data['sum_amount'] + $accident_count;
        }
        //交通事故审核
        $url_path = 'api/audit/accident_list';
        $traffic_status = $this->checkPer($token, $url_path);
        if($traffic_status)
        {
            $type = 2 ; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
            $traffic_count = $model_report->getReportCount($type, 2);
            $return_data['traffic_count'] = $traffic_count;
            $return_data['sum_amount'] = $return_data['sum_amount'] + $traffic_count;
        }

        //施工占道审核
        $url_path = 'api/audit/communicate_list';
        $failure_status = $this->checkPer($token, $url_path);
        if($failure_status)
        {
            $type = 3 ; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
            $failure_count = $model_report->getReportCount($type, 2);
            $return_data['failure_count'] = $failure_count;
            $return_data['sum_amount'] = $return_data['sum_amount'] + $failure_count;
        }
        return response()->json(['code'=>20000, 'msg'=>'', 'data'=>$return_data]);

    }
    /**
     * 判断权限显示那些消息
     * @param $token
     * @param $url_path
     * @return mixed
     */
    public function checkPer($token, $url_path)
    {
        $redis = Redis::connection('default');
        $cacheKey = "travel_user_login_".$token;
        $cacheValue = $redis->get($cacheKey);
        if(!empty($cacheValue))
            $data = json_decode($cacheValue, true);
        else
            return response()->json(['code' => 50000, 'msg' => '登录信息已失效', 'data' => []]);
        $user_id = $data['id'];

        //获取角色ID
        $model_role_users = new RoleUsers();
        $role_id = $model_role_users->getRoleIdByUserId($user_id);

        //获取角色权限ID
        $model_role_permissions = new RolePermissions();
        $per_ids = $model_role_permissions->getPerIdByRoleId($role_id);

        //获取权限路由
        $model_permissions = new Permissions();
        $per_paths = $model_permissions->getPermissions($per_ids);
        $per_array = array();
        foreach ($per_paths as $v)
        {
            $per_array[] = $v->url_path;
        }
        if (in_array($url_path, $per_array))
            return true;
        else
            return false;
    }
}

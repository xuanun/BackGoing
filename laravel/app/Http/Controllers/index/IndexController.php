<?php


namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\Initial;
use App\Models\RoleUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{

    /**
     * 用户管理 --WEB用户列表
     * @param Request $request
     * @return mixed
     */
    public function webUserList(Request $request)
    {
        $input = $request->all();
        $department_id = isset($input['department_id']) ? $input['department_id'] : ''; //部门ID
        $user_name = isset($input['user_name']) ? $input['user_name'] : ''; //用户名
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $role_id = isset($input['role_id']) ? $input['role_id'] : '';//角色ID
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;
        $model_web_user = new User();
        $user_data = $model_web_user->getUserList($department_id, $user_name, $phone, $role_id, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$user_data];
        return response()->json($return_data);
    }

    /**
     * 用户管理 ---WEB用户添加
     * @param Request $request
     * @return mixed
     */
    public function addWebUser(Request $request)
    {
        $input = $request->all();
        $user_name = isset($input['user_name']) ? $input['user_name'] : ''; //用户名
        $account = isset($input['account']) ? $input['account'] : ''; //账号
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $department_id = isset($input['department_id']) ? $input['department_id'] : ''; //部门ID
        $user_status = isset($input['user_status']) ? $input['user_status'] : ''; //状态
        $role_id = isset($input['role_id']) ? $input['role_id'] : '';//角色ID
        $model_web_initial = new Initial();
        $str_initial = $model_web_initial->getInitial();
        $password = encrypt($str_initial);
        //开始添加用户
        DB::beginTransaction();
        $model_web_user = new User();
        $exist_user = $model_web_user->existUser($account);
        if($exist_user) return response()->json(['code'=>40000,'msg'=>'账号已经存在', 'data'=>[]]);
        $user_data = $model_web_user->addUser($user_name, $account, $password, $phone, $department_id, $user_status);
        if($user_data['code'] != 20000) return response()->json( $user_data);
        //添加角色
        $model_role_user = new RoleUsers();
        $role_user_data = $model_role_user->addUserRole($user_data['data']['user_id'], $role_id);
        if($role_user_data['code'] != 20000) return response()->json( $role_user_data);
        DB::commit();
        return response()->json($role_user_data);
    }

    /**
     * 用户管理 ---WEB用户编辑
     * @param Request $request
     * @return mixed
     */
    public function editWebUser(Request $request)
    {
        $input = $request->all();
        $user_id = isset($input['user_id']) ? $input['user_id'] : ''; //用户ID
        $user_name = isset($input['user_name']) ? $input['user_name'] : ''; //用户名
        $account = isset($input['account']) ? $input['account'] : ''; //账号
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $user_status = isset($input['user_status']) ? $input['user_status'] : ''; //状态
        $role_id = isset($input['role_id']) ? $input['role_id'] : '';//角色ID

        $model_web_user = new User();
        $model_role_user = new RoleUsers();
        $user_info =  $model_web_user->getUserInfoByAccount($account);
        if(empty($user_info)) return response()->json(['code'=>40000,'msg'=>'账户不存在', 'data'=>[]]);
        $data =  json_decode(json_encode($user_info),true);
        if($data['phone'] != $phone){
            $exist_user = $model_web_user->existsMobile($phone);
            if($exist_user) return response()->json(['code'=>40000,'msg'=>'手机号已经存在', 'data'=>[]]);
        }
        $user_role = $model_role_user->getRoleIdByUserId($user_id);
        //开始修改用户信息
        DB::beginTransaction();
        $user_data = $model_web_user->editUser($user_id, $user_name, $account, $phone, $user_status);
        if($user_data['code'] != 20000) return response()->json( $user_data);
        //修改用户角色
        if($role_id != $user_role)
        {
            $role_user_data = $model_role_user->editUserRole($user_data['data']['user_id'], $role_id);
            if($role_user_data['code'] != 20000) return response()->json( $role_user_data);
        }else
            $role_user_data = $user_data;
        DB::commit();
        return response()->json($role_user_data);
    }

    /**
     * 用户管理 --WEB用户删除
     * @param Request $request
     * @return mixed
     */
    public function batchDelWebUser(Request $request)
    {
        $input = $request->all();
        $user_ids = isset($input['user_ids']) ? $input['user_ids'] : []; //用户ID数组
        $model_web_user = new User();
        $return_data = $model_web_user->delUserIds($user_ids);
        return response()->json($return_data);
    }

    /**
     * 用户管理 --App用户管理
     * @param Request $request
     * @return mixed
     */
    public function getAppUserList(Request $request)
    {
        $input = $request->all();
        $user_name = isset($input['user_name']) ? $input['user_name'] : ''; //用户名称
        $phone = isset($input['phone']) ? $input['phone'] : ''; //手机号
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;
        $model_app_ident = new AppUser();
        $ident_data = $model_app_ident->getList($user_name, $phone, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$ident_data];
        return response()->json($return_data);
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    protected $table = "easy_web_user";
    const INVALID = 0;
    const NORMAL = 1;
    protected $avatar = 'avatar.jpg';
    /**
     * 获取用户信息
     * @param $account
     * @return string
     */
    public function getUserInfoByAccount($account)
    {
        $result = DB::table($this->table)
            ->where("account",$account)
            ->where('user_status', self::NORMAL)
            ->first();
        return $result ? $result : '';
    }
    /**
     * @param $user_id
     * 用户登录. 更新用户信息
     * @return mixed
     */
    public function UserLogin($user_id)
    {
        DB::beginTransaction();
        $exists = $this->existsUserById($user_id);
        if($exists)
        {
            $updateArray = [
                'login_time' => date('Y-m-d H:i:s', time()),
                'updated_time' =>time(),
            ];
            $user_id = DB::table($this->table)->where('id', $user_id)->update($updateArray);
            if(!$user_id){
                DB::rollBack();
                return  ['code'=>40000,'msg'=>'登录失败', 'data'=>'', 'time'=>time()];
            }
        }
        DB::commit();
        return  ['code'=>20000,'msg'=>'登录成功'];
    }
    /**
     * @param $user_id
     * 查询用户id存不存在
     * @return mixed
     */
    public function existsUserById($user_id)
    {
        return DB::table($this->table)
            ->where('id', $user_id)
            ->where('user_status', self::NORMAL)
            ->exists();

    }

    /**
     * @param $department_id
     * @param $user_name
     * @param $phone
     * @param $role_id
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * 查看WEB人员列表信息（链表查询）
     * @return mixed
     */
    public function getUserList($department_id, $user_name, $phone, $role_id, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_web_user as user')
            ->select(DB::raw('user.id as user_id, user_name, nick_name, account, avatar, gander, register_time, role.id as role_id, role.name as role_name, user.phone, department_id, org.name as org_name, user_status'))
            ->leftJoin('easy_web_role_users as role_user', 'user.id', '=', 'role_user.user_id')
            ->leftJoin('easy_web_roles as role', 'role.id', '=', 'role_user.role_id')
            ->leftJoin('easy_web_organization as org', 'org.id', '=', 'user.department_id');
        if($department_id)
            $results = $results->where('department_id', $department_id);
        if($user_name)
            $results = $results->where('user_name', 'like','%'.$user_name.'%');
        if($phone)
            $results = $results->where('user.phone', $phone);
        if($role_id)
            $results = $results->where('role.id', $role_id);
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('user.updated_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results =$results
            ->orderBy('user.id','asc')
            ->paginate($page_size);

        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        $IMG_URL = env('IMG_URL');
        foreach($results as $v){
            $v->avatar = empty($v->avatar) ? '' : $IMG_URL.$v->avatar;
            $data['list'][] = $v;
        }
        return  $data;

    }

    /**
     * @param $user_name
     * @param $account
     * @param $password
     * @param $phone
     * @param $department_id
     * @param $user_status
     * 新增用户
     * @return mixed
     */
    public function addUser($user_name, $account, $password, $phone, $department_id, $user_status)
    {
        $exists = $this->existsMobile($phone);
        $return = ['code'=>40004,'msg'=>'新增失败', 'data'=>['手机号已经存在']];
        if(!$exists)
        {
            try{
                $insertArray = [
                    'user_name' =>$user_name,
                    'nick_name' =>$user_name,
                    'password'=> $password,
                    'account' => $account,
                    'avatar'=> $this->avatar,
                    'gander'=>1,
                    'register_time'=> date('Y-m-d H:i:s', time()),
                    'phone'=> $phone,
                    'department_id'=> $department_id,
                    'user_status'=> $user_status,
                    'updated_time' => time(),
                    'created_time' => time(),
                ];
                $id = DB::table($this->table)->insertGetId($insertArray);
                if($id){
                    $return = ['code'=>20000,'msg'=>'新增成功', 'data'=>['user_id'=>$id]];
                }
                else
                    DB::rollBack();
            }catch(\Exception $e){
                DB::rollBack();
                $return = ['code'=>40000,'msg'=>'新增失败', 'data'=>[$e->getMessage()]];
            }
        }
        return $return;
    }

    /**
     * @param $user_id
     * @param $user_name
     * @param $account
     * @param $phone
     * @param $user_status
     * 修改用户信息
     * @return mixed
     */
    public function editUser($user_id, $user_name, $account, $phone, $user_status)
    {
        try{
            $UpdateArray = [
                'user_name' =>$user_name,
                'phone'=> $phone,
                'user_status' => $user_status,
                'updated_time' => time(),
            ];
            DB::table($this->table)
                ->where('id', $user_id)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'编辑成功', 'data'=>['user_id'=>$user_id]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'编辑失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }
    /**
     * 查询账号是否存在
     * @param $account
     * @return mixed
     */
    public function existUser($account)
    {
        return  DB::table($this->table)
            ->where('account', $account)
            ->where('user_status', self::NORMAL)
            ->exists();
    }

    /**
     * @param $phone
     * 查询手机号是否存在
     * @return mixed
     */
    public function existsMobile($phone)
    {
        return DB::table($this->table)
            ->where('phone', $phone)
            ->where('user_status', self::NORMAL)
            ->exists();
    }
    /**
     * @param $user_ids
     * 批量删除用户
     * @return mixed
     */
    public function delUserIds($user_ids)
    {
        DB::beginTransaction();
        try{
            DB::table($this->table)
                ->whereIn('id', $user_ids)
                ->delete();
            $return = ['code'=>20000,'msg'=>'删除成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'删除失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * @param $user_id
     * @param $password
     * 重置用户密码到初始密码
     * @return mixed
     */
    public function resetUserPassword($user_id, $password)
    {
        DB::beginTransaction();
        try{
            $UpdateArray = [
                'password' => $password,
                'updated_time' => time(),
            ];
            $id = DB::table($this->table)
                ->where('id', $user_id)
                ->where('user_status', self::NORMAL)
                ->where('is_del', self::INVALID)
                ->update($UpdateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'重置密码成功', 'user_id'=>$id];
            }
            else{
                DB::rollBack();
                $return = ['code'=>40000,'msg'=>'重置密码失败', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'重置密码失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * @param $user_id
     * @param $password
     * 修改密码
     * @return mixed
     */
    public function editUserPassword($user_id, $password)
    {
        DB::beginTransaction();
        try{
            $UpdateArray = [
                'password' => $password,
                'updated_time' => time(),
            ];
            $id = DB::table($this->table)
                ->where('id', $user_id)
                ->where('user_status', self::NORMAL)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'修改密码成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改密码失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * @param $user_id
     * @param $avatar
     * @param $dept_id
     * @param $phone
     * 修改头像
     * @return mixed
     */
    public function editUserInfo($user_id, $avatar, $dept_id, $phone)
    {
        DB::beginTransaction();
        try{
            $UpdateArray = [
                'department_id' => $dept_id,
                'phone' => $phone,
                'avatar' => $avatar,
                'updated_time' => time(),
            ];
            DB::table($this->table)
                ->where('id', $user_id)
                ->where('user_status', self::NORMAL)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'修改资料成功', 'data'=>["avatar" => env("IMAGE_URL").$avatar]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改资料失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }


}

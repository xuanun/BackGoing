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
     * @param $page_size
     * @param $firm_id
     * 查看所有人员列表信息（链表查询）
     * @return mixed
     */
    public function getUserList($page_size, $firm_id)
    {
        $results =  DB::table('dove_user_factory as user_factory')
            ->select(DB::raw('user.created_time as created_time, user.user_name as name, user.mobile as mobile, roles.name as role_name,
            factory.name as factory_name, factory.id as factory_id, block.type_name as type_name, user_factory.block_id as block_id, user.data_status as data_status,
            user_factory.user_id as user_id'))
            ->leftJoin('dove_user as user', 'user.id', '=', 'user_factory.user_id')
            ->leftJoin('dove_factory as factory', 'factory.id', '=', 'user_factory.factory_id')
            ->leftJoin('dove_block as block', 'block.id', '=', 'user_factory.block_id')
            ->leftJoin('dove_role_users as role_users', 'role_users.user_id', '=', 'user_factory.user_id')
            ->leftJoin('dove_roles as roles', 'roles.id', '=', 'role_users.role_id')
            ->where('user.is_del', self::INVALID)
            ->where('user.firm_id', $firm_id)
            ->paginate($page_size);

        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        foreach($results as $v){
            $data['list'][] = $v;
        }
        return  $data;

    }

    /**
     * @param $user_name
     * @param $mobile
     * @param $password
     * @param $rsg_time
     * @param $firm_id
     * @param $is_firm
     * 新增用户
     * @return mixed
     */
    public function addUser($user_name, $mobile, $password, $rsg_time, $firm_id, $is_firm)
    {
        $exists = $this->existsMobile($mobile);
        $return = ['code'=>40004,'msg'=>'新增失败', 'data'=>['手机号已经存在']];
        if(!$exists)
        {
            try{
                $insertArray = [
                    'user_name' =>$user_name,
                    'nick_name' =>$user_name,
                    'mobile' => $mobile,
                    'password'=>$password,
                    'avatar'=>$this->avatar,
                    'level'=>1,
                    'gander'=>1,
                    'register_time'=>$rsg_time,
                    'firm_id'=>$firm_id,
                    'data_status'=>self::NORMAL,
                    'is_del'=>self::INVALID,
                    'is_firm'=> $is_firm,
                    'updated_time' => time(),
                    'created_time' => time(),
                ];
                $id = DB::table($this->table)->insertGetId($insertArray);
                if($id){
                    $return = ['code'=>20000,'msg'=>'新增成功', 'user_id'=>$id];
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
     * @param $user_name
     * @param $mobile
     * @param $user_id
     * @param $rsg_time
     * @param $firm_id
     * 查看所有人员列表信息（链表查询）
     * @return mixed
     */
    public function editUser($user_id, $user_name, $mobile, $rsg_time, $firm_id)
    {
        $exists = $this->existsUserById($user_id);
        $return = ['code'=>40004,'msg'=>'编辑失败', 'data'=>['账号不存在']];
        if($exists)
        {
            try{
                $UpdateArray = [
                    'user_name' =>$user_name,
                    'mobile' => $mobile,
                    'register_time'=>$rsg_time,
                    'firm_id'=>$firm_id,
                    'updated_time' => time(),
                ];
                DB::table($this->table)
                    ->where('id', $user_id)
                    ->where('data_status', self::NORMAL)
                    ->update($UpdateArray);
                $return = ['code'=>20000,'msg'=>'编辑成功', 'user_id'=>$user_id];
            }catch(\Exception $e){
                DB::rollBack();
                $return = ['code'=>40000,'msg'=>'编辑失败', 'data'=>[$e->getMessage()]];
            }
        }
        return $return;
    }


    /**
     * @param $mobile
     * 查询手机号是否存在
     * @return mixed
     */
    public function existsMobile($mobile)
    {
        return DB::table($this->table)
            ->where('mobile', $mobile)
            ->where('data_status', self::NORMAL)
            ->exists();
    }

    /**
     * @param $user_id
     * 软删除用户
     * @return mixed
     */
    public function delUserInfo($user_id)
    {
        $exists = $this->existsUserById($user_id);
        $return = ['code'=>40004,'msg'=>'删除失败', 'data'=>['账号不存在']];
        if($exists)
        {
            try{
                $UpdateArray = [
                    'is_del' => self::NORMAL,
                    'updated_time' => time(),
                ];
                $id = DB::table($this->table)
                    ->where('id', $user_id)
                    ->update($UpdateArray);
                if($id){
                    $return = ['code'=>20000,'msg'=>'删除成功', 'user_id'=>$id];
                }
                else
                    DB::rollBack();
            }catch(\Exception $e){
                DB::rollBack();
                $return = ['code'=>40000,'msg'=>'删除失败', 'data'=>[$e->getMessage()]];
            }
        }
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
                ->where('data_status', self::NORMAL)
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
                ->where('data_status', self::NORMAL)
                ->where('is_del', self::INVALID)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'重置密码成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'重置密码失败', 'data'=>[$e->getMessage()]];
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
                ->where('data_status', self::NORMAL)
                ->where('is_del', self::INVALID)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'修改头像成功', 'data'=>["avatar" => env("IMAGE_URL").$avatar]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改头像失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }


    /**
     * @param $firm_id
     * 查询手机号是否存在
     * @return mixed
     */
    public function getUserInfoByFirmId($firm_id)
    {
        return DB::table($this->table)
            ->select(DB::raw('id, user_name, mobile, avatar'))
            ->where('firm_id', $firm_id)
            ->where('is_firm', 1)
            ->where('data_status', self::NORMAL)
            ->where('is_del', self::INVALID)
            ->first();
    }

}

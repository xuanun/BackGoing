<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Roles extends Model
{
    protected $table = "easy_web_roles";
    const INVALID = 0;
    const NORMAL = 1;
    /**
     * 查询管理员角色列表基本信息
     * @param $role_name
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getRoleUserList($role_name, $start_time, $end_time, $page_size)
    {
        $results =  DB::table($this->table)
            ->select(DB::raw('id, name, role_desc, role_check, role_sort, data_status, updated_time'));
        if($role_name)
            $results = $results->where('name', 'like','%'.$role_name.'%');
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('updated_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results =$results->orderBy('role_sort', 'asc')
            ->orderBy('id','desc')
            ->paginate($page_size);

        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        foreach($results as $v){
            $v->updated_time = date('Y-m-d H:i:s', $v->updated_time);
            $data['list'][] = $v;
        }
        return  $data;
    }
    /**
     * 角色列表-删除角色
     * @param $role_ids
     * @return mixed
     */
    public function delRole($role_ids)
    {
        DB::beginTransaction();
        try{
            DB::table($this->table)
                ->whereIn('id', $role_ids)
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
     * 角色列表-新增角色
     * @param $name
     * @param $role_desc
     * @param $role_check
     * @param $role_sort
     * @param $data_status
     * @return mixed
     */
    public function addRole($name, $role_desc, $role_check, $role_sort, $data_status)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $insertArray = [
                'name' =>$name,
                'role_desc' => $role_desc,
                'role_check' => $role_check,
                'role_sort' => $role_sort,
                'data_status'=> $data_status,
                'updated_time' => time(),
                'created_time' =>time(),
            ];
            $id = DB::table($this->table)->insertGetId($insertArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'新增成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'新增失败', 'data'=>[]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 角色列表-修改角色
     * @param $role_id
     * @param $name
     * @param $role_desc
     * @param $role_sort
     * @param $role_check
     * @param $data_status
     * @return mixed
     */
    public function editRole($role_id, $name, $role_desc, $role_sort, $role_check, $data_status)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'name' =>$name,
                'role_desc' => $role_desc,
                'role_sort' => $role_sort,
                'role_check' => $role_check,
                'data_status'=> $data_status,
                'updated_time' => time(),
            ];
            $id = DB::table($this->table)->where('id', $role_id)->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'修改成功', 'role_id'=>$id];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改失败', 'data'=>[]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 通过角色ID查询角色是否存在
     * @param $role_id
     * @return mixed
     */
    public function existByRoleId($role_id)
    {
        return  DB::table($this->table)
            ->where('id', $role_id)
            ->where('data_status', self::NORMAL)
            ->exists();
    }

    /**
     * 通过角色名称查询角色是否存在
     * @param $role_name
     * @return mixed
     */
    public function existByRoleName($role_name)
    {
        return  DB::table($this->table)
            ->where('name', $role_name)
            ->exists();
    }

    /**
     * 通过角色名称查询角色是否存在
     * @param $role_name
     * @param $role_id
     * @return mixed
     */
    public function existByRoleNameById($role_id, $role_name)
    {
        return  DB::table($this->table)
            ->where('name', $role_name)
            ->where('id', '!=',$role_id)
            ->exists();
    }

    /**
     * 获取企业全部角色
     * @param $firm_id
     * @return mixed
     */
    public function getAllRoles( $firm_id)
    {
        return  DB::table($this->table)
            ->select(DB::raw('id, name'))
            ->where('firm_id', $firm_id)
            ->where('data_status', self::NORMAL)
            ->get();
    }


    /**
     * 平台设置-新增角色
     * @param $name
     * @param $role_desc
     * @param $firm_id
     * @return mixed
     */
    public function addRoleByFirmID($name, $role_desc, $firm_id)
    {
        try{
            $insertArray = [
                'name' =>$name,
                'role_desc' => $role_desc,
                'firm_id' => $firm_id,
                'data_status'=> self::NORMAL,
                'updated_at' => time(),
                'created_at' =>time(),
            ];
            $id = DB::table($this->table)->insertGetId($insertArray);
            if(!$id){
                DB::rollBack();
                return  ['code'=>40000,'msg'=>'新增失败', 'data'=>[]];
            }else{
                return ['code'=>20000,'msg'=>'新增成功', 'role_id'=>$id];
            }
        }catch(\Exception $e){
            DB::rollBack();
            return  ['code'=>40000,'msg'=>'新增失败', 'data'=>[]];
        }
    }
}

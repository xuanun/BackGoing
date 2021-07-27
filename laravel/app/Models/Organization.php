<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Organization extends Model
{
    protected $table = "easy_web_organization";

    /**
     * 获取组织机构数据
     * @param $p_id
     * @return mixed
     */
    public function getListByPid($p_id)
    {
        return $results =  DB::table($this->table)
            ->select(DB::raw('id, p_id, name, functionary, sort, phone, updated_time'))
            ->where('p_id', $p_id)
            ->orderBy('sort', 'asc')
            ->orderBy('id','desc')
            ->get();
    }

    /**
     * 获取数据列表
     * @param $id
     * @param $org_name
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getDataList($id, $org_name, $start_time, $end_time, $page_size)
    {
       $results =  DB::table($this->table)
            ->select(DB::raw('id, p_id, name, functionary, sort, phone, data_status, updated_time'));
        if($id !== '')
            $results = $results->where('p_id', $id);
        if($org_name)
            $results = $results->where('name', 'like','%'.$org_name.'%');
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('updated_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results= $results
            ->orderBy('sort', 'asc')
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
     * 组织机构列表-新增数据
     * @param $p_id
     * @param $name
     * @param $functionary
     * @param $sort
     * @param $phone
     * @param $data_status
     * @return mixed
     */
    public function addData($p_id, $name, $functionary, $sort, $phone, $data_status)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $insertArray = [
                'p_id' => $p_id,
                'name' =>$name,
                'functionary' => $functionary,
                'sort' => $sort,
                'phone' => $phone,
                'data_status' => $data_status,
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
     * 组织机构列表-修改数据
     * @param $id
     * @param $p_id
     * @param $name
     * @param $functionary
     * @param $sort
     * @param $phone
     * @param $data_status
     * @return mixed
     */
    public function editData($id, $p_id, $name, $functionary, $sort, $phone, $data_status)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'p_id' => $p_id,
                'name' =>$name,
                'functionary' => $functionary,
                'sort' => $sort,
                'phone' => $phone,
                'data_status' => $data_status,
                'updated_time' => time(),
            ];
            $id = DB::table($this->table)->where('id', $id)->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'修改成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改失败', 'data'=>[]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 组织机构列表-批量删除
     * @param $ids
     * @return mixed
     */
    public function delRole($ids)
    {
        DB::beginTransaction();
        try{
            DB::table($this->table)
                ->whereIn('id', $ids)
                ->delete();
            $return = ['code'=>20000,'msg'=>'删除成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'删除失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }
}

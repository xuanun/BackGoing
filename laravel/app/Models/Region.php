<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Region extends Model
{
    protected $table = "easy_region";

    /**
     * 获取全部数据列表
     * @return mixed
     */
    public function getAllList()
    {
        return $results =  DB::table($this->table)
            ->select(DB::raw('region_id, GIs'))
            ->orderBy('region_id','desc')
            ->get();
    }

    /**
     * 获取数据列表
     * @param $area_name
     * @param $user_name
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($area_name, $user_name, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_region as region')
            ->select(DB::raw('region.region_id, region.name as area_name, region.explain, region.GIs, region.web_uid, region.update_time, web_user.user_name as user_name'))
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'region.web_uid');
        if($area_name !== '')
            $results = $results->where('region.name', 'like','%'.$area_name.'%');
        if($user_name !== '')
            $results = $results->where('web_user.user_name', 'like','%'.$user_name.'%');
        if($start_time && $end_time){
            $results = $results->whereBetween('region.update_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results= $results
            ->orderBy('region.region_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        foreach($results as $v){
            $v->update_time = empty($v->update_time) ? '/' : date('Y-m-d H:i:s', $v->update_time);
            $v->GIs = json_decode($v->GIs);
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * @param $area_name
     * @param $explain
     * @param $GIs
     * @param $user_id
     * 新增区域数据
     * @return mixed
     */
    public function addData($area_name,  $explain, $GIs, $user_id)
    {
        try{
            $insertArray = [
                'name' => $area_name,
                'explain' => $explain,
                'GIs'=> $GIs,
                'web_uid' => $user_id,
                'creation_time' => time(),
                'update_time' => time(),
            ];
            $id = DB::table($this->table)->insertGetId($insertArray);
            $return = ['code'=>20000,'msg'=>'新增成功', 'data'=>['region_id'=>$id]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'新增失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }

    /**
     * @param $region_id
     * @param $area_name
     * @param $explain
     * @param $GIs
     * @param $user_id
     * 修改用户信息
     * @return mixed
     */
    public function editData($region_id, $area_name,  $explain, $GIs, $user_id)
    {
        try{
            $UpdateArray = [
                'name' => $area_name,
                'explain' => $explain,
                'GIs'=> $GIs,
                'web_uid' => $user_id,
                'update_time' => time(),
            ];
            DB::table($this->table)
                ->where('region_id', $region_id)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'编辑成功', 'data'=>['user_id'=>$user_id]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'编辑失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }

    /**
     * @param $region_ids
     * 批量删除数据
     * @return mixed
     */
    public function delIds($region_ids)
    {
        try{
            DB::table($this->table)
                ->whereIn('region_id', $region_ids)
                ->delete();
            DB::table('easy_vp_region')
                ->whereIn('region_id', $region_ids)
                ->delete();
            $return = ['code'=>20000,'msg'=>'删除成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'删除失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }
}

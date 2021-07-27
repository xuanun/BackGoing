<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Park extends Model
{
    protected $table = "easy_park";
    const INVALID = 0;
    const NORMAL = 1;

    /**
     * 获取数据列表
     * @param $park_code
     * @param $park_name
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($park_code, $park_name, $start_time, $end_time, $page_size)
    {
        $results =  DB::table($this->table)
            ->select(DB::raw('id, park_code, park_name, longitude, latitude, sum_berth, surplus_berth, status, updated_time, created_time'));
        if($park_code !== '')
            $results = $results->where('park_code', 'like','%'.$park_code.'%');
        if($park_name !== '')
            $results = $results->where('park_name', 'like','%'.$park_name.'%');
        if($start_time && $end_time){
            $results = $results->whereBetween('updated_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results= $results
            ->orderBy('id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        foreach($results as $v){
            $v->lng_lat = $v->longitude.','.$v->latitude;
            $v->updated_time = empty($v->updated_time) ? '/' : date('Y-m-d H:i:s', $v->updated_time);
            $v->created_time = empty($v->created_time) ? '/' : date('Y-m-d H:i:s', $v->created_time);
            if($v->status == 0)
                $v->state_name = "关闭";
            if($v->status == 1)
                $v->state_name  = "正常";
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * @param $park_code
     * @param $park_name
     * @param $longitude
     * @param $latitude
     * @param $sum_berth
     * @param $surplus_berth
     * @param $status
     * 新增数据
     * @return mixed
     */
    public function addData($park_code, $park_name, $longitude, $latitude, $sum_berth, $surplus_berth, $status)
    {
        try{
            $insertArray = [
                'park_code' => $park_code,
                'park_name' => $park_name,
                'longitude'=> $longitude,
                'latitude' => $latitude,
                'sum_berth'=> $sum_berth,
                'surplus_berth' => $surplus_berth,
                'status'=> $status,
                'updated_time' => time(),
                'created_time' => time(),
            ];
            $id = DB::table($this->table)->insertGetId($insertArray);
            $return = ['code'=>20000,'msg'=>'新增成功', 'data'=>['id'=>$id]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'新增失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }

    /**
     * @param $id
     * @param $park_code
     * @param $park_name
     * @param $longitude
     * @param $latitude
     * @param $sum_berth
     * @param $surplus_berth
     * @param $status
     * 修改数据
     * @return mixed
     */
    public function editData($id, $park_code, $park_name, $longitude, $latitude, $sum_berth, $surplus_berth, $status)
    {
        try{
            $UpdateArray = [
                'park_code' => $park_code,
                'park_name' => $park_name,
                'longitude'=> $longitude,
                'latitude' => $latitude,
                'sum_berth'=> $sum_berth,
                'surplus_berth' => $surplus_berth,
                'status'=> $status,
                'update_time' => time(),
            ];
            DB::table($this->table)
                ->where('id', $id)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'编辑成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'编辑失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }

    /**
     * @param $ids
     * 批量删除数据
     * @return mixed
     */
    public function delIds($ids)
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

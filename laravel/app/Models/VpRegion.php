<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VpRegion extends Model
{
    protected $table = "easy_vp_region";

    /**
     * 获取区域内 视频数量/诱导屏数量
     * @param $region_id
     * @param $type
     * @return mixed
     */
    public function getCount($region_id, $type)
    {
        return DB::table('easy_vp_region as vr')
            ->leftJoin('easy_video_picture as vp', 'vp.vp_id', '=', 'vr.vp_id')
            ->where('vr.region_id', $region_id)
            ->where('vp.type', $type)
            ->count();

    }
    /**
     * @param $region_id
     * @param $vp_id
     * 新增关联数据
     * @return mixed
     */
    public function addData($region_id,  $vp_id)
    {
        try{
            $insertArray = [
                'region_id' => $region_id,
                'vp_id' => $vp_id,
                'created_time' => time(),
                'updated_time' => time(),
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
     * @param $vp_id
     * 查询数据是否存在
     * @return mixed
     */
    public function existsData($region_id, $vp_id)
    {
        return DB::table($this->table)
            ->where('region_id', $region_id)
            ->where('vp_id', $vp_id)
            ->exists();
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
            $return = ['code'=>20000,'msg'=>'删除成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'删除失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }
    /**
     * @param $vp_id
     * 批量删除数据
     * @return mixed
     */
    public function delVpId($vp_id)
    {
        try{
            DB::table($this->table)
                ->where('vp_id', $vp_id)
                ->delete();
            $return = ['code'=>20000,'msg'=>'删除成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'删除失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }
}

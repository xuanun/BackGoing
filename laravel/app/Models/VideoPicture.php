<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class VideoPicture extends Model
{
    protected $table = "easy_video_picture";
    const INVALID = 0;
    const NORMAL = 1;

    /**
     * 获取全部数据列表
     * @return mixed
     */
    public function getAllList()
    {
        return $results =  DB::table($this->table)
            ->select(DB::raw('vp_id, vp_code, vp_name, type, longitude, latitude, vp_url, state, web_uid, update_time'))
            ->orderBy('vp_id','desc')
            ->get();
    }

    /**
     * 获取数据列表
     * @param $type
     * @param $vp_code
     * @param $vp_name
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($type, $vp_code, $vp_name, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_video_picture as video')
            ->select(DB::raw('video.vp_id, video.vp_code, video.vp_name, video.type, video.longitude, video.latitude, video.vp_url, video.state, video.web_uid, update_time, web_user.user_name'))
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'video.web_uid');
        if($type)
            $results = $results->where('video.type', $type);
        if($vp_code !== '')
            $results = $results->where('video.vp_code', 'like','%'.$vp_code.'%');
        if($vp_name !== '')
            $results = $results->where('video.vp_name', 'like','%'.$vp_name.'%');
        if($start_time && $end_time){
            $results = $results->whereBetween('video.update_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results= $results
            ->orderBy('video.vp_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        $IMG_URL = env('IMAGE_URL');
        $VIDEO_URL = env('VIDEO_URL');
        foreach($results as $v){
            $v->lng_lat = $v->longitude.','.$v->latitude;
            if($v->type == 2)
                $v->vp_url = empty($v->vp_url) ? '' : $IMG_URL.$v->vp_url;
            elseif($v->type == 1)
                $v->vp_url = empty($v->vp_url) ? '' : $VIDEO_URL.$v->vp_url;
            $v->update_time = date('Y-m-d H:i:s', $v->update_time);
            $v->state_name = '';
            if($v->state == 0)
                $v->state_name = "异常";
            if($v->state == 1)
                $v->state_name  = "正常";
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * @param $vp_code
     * @param $type
     * @param $vp_name
     * @param $longitude
     * @param $latitude
     * @param $vp_url
     * @param $state
     * @param $user_id
     * 新增数据
     * @return mixed
     */
    public function addData($vp_code, $type, $vp_name, $longitude, $latitude, $vp_url, $state, $user_id)
    {
            try{
                $insertArray = [
                    'vp_code' =>$vp_code,
                    'vp_name' =>$vp_name,
                    'type' =>$type,
                    'longitude'=> $longitude,
                    'latitude' => $latitude,
                    'vp_url'=> $vp_url,
                    'state'=> $state,
                    'web_uid'=> $user_id,
                    'creation_time' => time(),
                    'update_time' => time(),
                ];
                $id = DB::table($this->table)->insertGetId($insertArray);
                $return = ['code'=>20000,'msg'=>'新增成功', 'data'=>['vp_id'=>$id]];
            }catch(\Exception $e){
                DB::rollBack();
                $return = ['code'=>40000,'msg'=>'新增失败', 'data'=>[$e->getMessage()]];
            }
            return $return;
    }

    /**
     * @param $vp_id
     * @param $vp_code
     * @param $vp_name
     * @param $type
     * @param $longitude
     * @param $latitude
     * @param $vp_url
     * @param $state
     * @param $user_id
     * 修改数据
     * @return mixed
     */
    public function editData($vp_id, $vp_code, $vp_name, $type, $longitude, $latitude, $vp_url, $state, $user_id)
    {
        try{
            $UpdateArray = [
                'vp_code' =>$vp_code,
                'vp_name' => $vp_name,
                'type'=> $type,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'vp_url'=> $vp_url,
                'state' => $state,
                'web_uid' => $user_id,
                'update_time' => time(),
            ];
            DB::table($this->table)
                ->where('vp_id', $vp_id)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'编辑成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'编辑失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }

    /**
     * @param $vp_ids
     * 批量删除数据
     * @return mixed
     */
    public function delIds($vp_ids)
    {
        DB::beginTransaction();
        try{
            DB::table($this->table)
                ->whereIn('vp_id', $vp_ids)
                ->delete();
            DB::table('easy_vp_region')
                ->whereIn('vp_id', $vp_ids)
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
     * 查询ID是不是属于同一类
     * @param $vp_ids
     * @param $type
     * @return mixed
     */
    public function existType($vp_ids, $type)
    {
        return  DB::table($this->table)
            ->whereIn('vp_id', $vp_ids)
            ->where('type', '!=',$type)
            ->exists();
    }
}

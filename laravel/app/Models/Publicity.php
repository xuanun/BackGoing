<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Publicity extends Model
{
    protected $table = "easy_publicity";
    /**
     * 获取数据列表
     * @param $title
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($title, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_publicity as pub')
            ->select(DB::raw('pub.id, pub.title, web_user.user_name, pub.video, pub.status, pub.updated_time'))
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'pub.web_user_id');
        if($title)
            $results = $results->where('pub.title', 'like','%'.$title.'%');
        if($start_time && $end_time){
            $results = $results->whereBetween('pub.updated_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results= $results
            ->orderBy('pub.id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        $VIDEO_URL = env('VIDEO_URL');
        foreach($results as $v){
            $v->video_url = empty($v->video) ? '' : $VIDEO_URL.$v->video;
            $v->updated_time = date('Y-m-d H:i:s', $v->updated_time);
            $v->state_name = '';
            if($v->status == 0)
                $v->state_name = "异常";
            if($v->status == 1)
                $v->state_name  = "正常";
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * @param $title
     * @param $video
     * @param $status
     * @param $user_id
     * 新增数据
     * @return mixed
     */
    public function addData($title, $video, $status, $user_id)
    {
        try{
            $insertArray = [
                'web_user_id' => $user_id,
                'title' => $title,
                'video' => $video,
                'status'=> $status,
                'updated_time' => time(),
                'created_time' => time(),
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
     * @param $id
     * @param $title
     * @param $video
     * @param $status
     * @param $user_id
     * 修改数据
     * @return mixed
     */
    public function editData($id, $title, $video, $status, $user_id)
    {
        try{
            $UpdateArray = [
                'web_user_id' => $user_id,
                'title' => $title,
                'video' => $video,
                'status'=> $status,
                'updated_time' => time(),
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

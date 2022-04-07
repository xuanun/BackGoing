<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PushMassage extends Model
{
    protected $table = "easy_push_massage";
    const INVALID = 0;
    const NORMAL = 1;

    /**
     * 获取数据列表
     * @param $release_type
     * @param $title
     * @param $user_name
     * @param $c_start_time
     * @param $c_end_time
     * @param $r_start_time
     * @param $r_end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size)
    {
        $results =  DB::table('easy_push_massage as msg')
            ->select(DB::raw('msg.id, msg.title, msg.content,  msg.first_type_id, msg.second_type_id, msg.release_time, msg.valid_start_time, msg.valid_end_time, msg.release_web_uid, msg.created_time, msg.updated_time, web_user.user_name as user_name'))
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'msg.release_web_uid');
        if($release_type)
        {
            if($release_type == 1){
                $results = $results->where('msg.valid_start_time', '<=',time())->where('msg.valid_end_time', '>=',time());
            }
            if($release_type == 2){
                $results =  $results->where('msg.valid_start_time', '>',time());
            }
            if($release_type == 3){
                $results = $results->where(function ($query) use ($release_type){
                    $query->where('msg.valid_end_time', '<',time());
                });
            }
        }
        if($title !== '')
            $results = $results->where('msg.title', 'like','%'.$title.'%');
        if($user_name)
            $results = $results->where('web_user.user_name', 'like','%'.$user_name.'%');;
        if($c_start_time && $c_end_time){
            $results = $results->whereBetween('msg.created_time', [strtotime($c_start_time), strtotime($c_end_time)]);
        }
        if($r_start_time && $r_end_time){
            $results = $results->where('msg.valid_start_time', '>=',strtotime($r_start_time))->where('msg.valid_start_time', '<=',strtotime($r_end_time));
        }
        $results= $results
            ->orderBy('msg.id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        foreach($results as $v){
            $v->operation_name = '';
            $v->status = '';
            if($v->valid_start_time > time())
            {
                $v->operation_name = "待发布";
            }
            if($v->valid_start_time <= time() && $v->valid_end_time >= time())
            {
                $v->operation_name = "发布中";
                $v->status = "取消发布";
            }
            if($v->valid_start_time <= time())
            {
                $v->operation_name = "已发布";
            }
            $v->release_time = empty($v->release_time) ? '/' : date('Y-m-d H:i:s', $v->release_time);
            $v->created_time = empty($v->created_time) ? '/' : date('Y-m-d H:i:s', $v->created_time);
            $v->valid_start_time = empty($v->valid_start_time) ? '/' : date('Y-m-d H:i:s', $v->valid_start_time);
            $v->valid_end_time = empty($v->valid_end_time) ? '/' : date('Y-m-d H:i:s', $v->valid_end_time);
            $v->valid_time = $v->valid_start_time .' -- '. $v->valid_end_time;
            $data['list'][] = $v;
        }
        return  $data;
    }
    /**
     * 新增数据
     * @param $first_type_id
     * @param $second_type_id
     * @param $title
     * @param $content
     * @param $release_web_uid
     * @param $valid_start_time
     * @param $valid_end_time
     * @return mixed
     */
    public function addData($first_type_id, $second_type_id, $title, $content, $release_web_uid, $valid_start_time, $valid_end_time)
    {
        $valid_start_time = strtotime($valid_start_time);
        $valid_end_time = strtotime($valid_end_time);
        DB::beginTransaction();
        try{
            $insertArray = [
                'first_type_id' => $first_type_id,
                'second_type_id' => $second_type_id,
                'title' => $title,
                'content' => $content,
                'release_web_uid' => $release_web_uid,
                'valid_start_time' => $valid_start_time,
                'valid_end_time' => $valid_end_time,
                'release_time' => time(),
                'created_time' => time(),
                'updated_time' => time(),
            ];
            DB::table($this->table)->insertGetId($insertArray);
            $return = ['code'=>20000,'msg'=>'新增成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'新增失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

}

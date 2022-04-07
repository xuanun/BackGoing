<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dynamic extends Model
{
    protected $table = "easy_dynamic";
    /**
     * 获取数据列表  交通管制 施工占道 限行限号
     * @param $category
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
    public function getList($category, $release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size)
    {
        $results =  DB::table('easy_dynamic as dynamic')
            ->select(DB::raw('dynamic.dynamic_id, dynamic.category, dynamic.title, dynamic.content, dynamic.release_type, dynamic.release_start_time, dynamic.release_end_time, dynamic.release_web_uid, dynamic.release_time, dynamic.creation_time as created_time, dynamic.update_time as operation_time, web_user.user_name as user_name'))
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'dynamic.release_web_uid');
        if($category)
            $results = $results->where('dynamic.category', $category);
        if($release_type)
        {
            if($release_type == 1){
                $results = $results->where('dynamic.release_start_time', '<=',time())->where('dynamic.release_end_time', '>=',time())->where('dynamic.release_type', '!=',4);
            }
            if($release_type == 2){
                $results =  $results->where('dynamic.release_start_time', '>',time());
            }
            if($release_type == 3){
                $results = $results->where(function ($query) use ($release_type){
                    $query->where('dynamic.release_end_time', '<',time())->orWhere('dynamic.release_type',4);
                });
            }
        }
        if($title !== '')
            $results = $results->where('dynamic.title', 'like','%'.$title.'%');
        if($user_name)
            $results = $results->where('web_user.user_name', 'like','%'.$user_name.'%');;
        if($c_start_time && $c_end_time){
            $results = $results->whereBetween('dynamic.creation_time', [strtotime($c_start_time), strtotime($c_end_time)]);
        }
        if($r_start_time && $r_end_time){
            $results = $results->where('dynamic.release_start_time', '>=',strtotime($r_start_time))->where('dynamic.release_start_time', '<=',strtotime($r_end_time));
        }
        $results= $results
            ->orderBy('dynamic.dynamic_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        foreach($results as $v){
            $v->type_name = '';
            $v->status = '';
            $v->operation = '';
            $v->operation_user = $v->user_name;
            if($v->release_type != 4)
            {
                $v->operation = '创建信息';
                if($v->release_start_time > time())
                {
                    $v->type_name = "待发布";
                }
                if($v->release_start_time <= time() && $v->release_end_time >= time())
                {
                    $v->type_name = "发布中";
                    $v->status = "取消发布";
                }
                if($v->release_end_time <= time())
                {
                    $v->type_name = "已发布";
                }
            }else
            {
                $v->operation = '取消发布';
                $v->type_name = "已发布";
            }
            $v->release_time = empty($v->release_time) ? '/' : date('Y-m-d H:i:s', $v->release_time);
            $v->created_time = empty($v->created_time) ? '/' : date('Y-m-d H:i:s', $v->created_time);
            $v->release_start_time = empty($v->release_start_time) ? '/' : date('Y-m-d H:i:s', $v->release_start_time);
            $v->release_end_time = empty($v->release_end_time) ? '/' : date('Y-m-d H:i:s', $v->release_end_time);
            $v->valid_time = $v->release_start_time .' -- '. $v->release_end_time;
            $v->operation_time =  empty($v->operation_time) ? '/' : date('Y-m-d H:i:s', $v->operation_time);
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * 请求列表 时更新发布状态
     * @return mixed
     */
    public function updateStatus()
    {
        DB::beginTransaction();
        try{
            $updateArray = [
                'release_type' => 3,
                'update_time' => time(),
            ];
            DB::table($this->table)
                ->where('release_type', 1)
                ->where('release_end_time','<=',  time())
                ->update($updateArray);
            $return = ['code'=>20000,'msg'=>'请求成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'请求失败', 'data'=>[]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 发布 取消发布
     * @param $dynamic_id
     * @param $title
     * @param $content
     * @param $release_type
     * @param $release_web_uid
     * @param $release_start_time
     * @param $release_end_time
     * @param $category
     * @return mixed
     */
    public function releaseData($dynamic_id, $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category)
    {
        $release_start_time = strtotime($release_start_time);
        $release_end_time = strtotime($release_end_time);
        DB::beginTransaction();
        try{
            $updateArray = [
                'title' => $title,
                'content' => $content,
                'release_type' => $release_type,
                'release_web_uid' => $release_web_uid,
                'release_start_time' => $release_start_time,
                'release_end_time' => $release_end_time,
                'release_time' => time(),
                'update_time' => time(),
            ];
            DB::table($this->table)
                ->where('dynamic_id', $dynamic_id)
                ->where('category', $category)
                ->update($updateArray);
            $return = ['code'=>20000,'msg'=>'操作成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'操作失败', 'data'=>[]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 新增数据
     * @param $title
     * @param $content
     * @param $release_type
     * @param $release_web_uid
     * @param $release_start_time
     * @param $release_end_time
     * @param $category
     * @return mixed
     */
    public function addData($title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category)
    {
        $release_start_time = strtotime($release_start_time);
        $release_end_time = strtotime($release_end_time);
        DB::beginTransaction();
        try{
            $insertArray = [
                'category' => $category,
                'title' => $title,
                'content' => $content,
                'release_type' => $release_type,
                'release_web_uid' => $release_web_uid,
                'release_start_time' => $release_start_time,
                'release_end_time' => $release_end_time,
                'creation_time' => time(),
                'release_time' => time(),
                'update_time' => time(),
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

    /**
     * 获取数据列表  气象预警
     * @param $category
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
    public function getWeatherList($category, $release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size)
    {
        $results =  DB::table('easy_dynamic as dynamic')
            ->select(DB::raw('dynamic.dynamic_id, dynamic.category, dynamic.title, dynamic.content, dynamic.release_type, dynamic.release_start_time, dynamic.release_end_time, dynamic.release_web_uid, dynamic.release_time, dynamic.creation_time as created_time, dynamic.update_time as operation_time, dynamic.is_collect, web_user.user_name as user_name'))
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'dynamic.release_web_uid');
        if($category)
            $results = $results->where('dynamic.category', $category);
        if($release_type)
        {
            if($release_type == 1){
                $results = $results->where(function ($query) use ($release_type){
                    $query->where(function ($query) use ($release_type){
                        $query->where('dynamic.is_collect', '=',1)->Where('dynamic.release_type',1);
                    })->orWhere(function ($query) use ($release_type){
                        $query->where('dynamic.release_start_time', '<=',time())->where('dynamic.release_end_time', '>=',time())->where('dynamic.release_type', '!=',4);
                    });
                });
            }
            if($release_type == 2){
                $results = $results->where('dynamic.is_collect', '=',2)->where('dynamic.release_start_time', '>',time());
            }
            if($release_type == 3){
                $results = $results->where(function ($query) use ($release_type){
                    $query->where('dynamic.release_end_time', '<',time())->orWhere('dynamic.release_type',3)->orWhere('dynamic.release_type',4);
                });
            }
        }
        if($title !== '')
            $results = $results->where('dynamic.title', 'like','%'.$title.'%');
        if($user_name)
            $results = $results->where('web_user.user_name', 'like','%'.$user_name.'%');;
        if($c_start_time && $c_end_time){
            $results = $results->whereBetween('dynamic.creation_time', [strtotime($c_start_time), strtotime($c_end_time)]);
        }
        if($r_start_time && $r_end_time){
            $results = $results->where('dynamic.release_start_time', '>=',strtotime($r_start_time))->where('dynamic.release_start_time', '<=',strtotime($r_end_time));
        }
        $results= $results
            ->orderBy('dynamic.dynamic_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        foreach($results as $v){
            $v->type_name = '';
            $v->status = '';
            $v->operation = '';
            $v->operation_user = $v->user_name;
            if($v->release_type != 4)
            {
                $v->operation = '创建信息';
                if($v->is_collect == 2)
                {
                    if($v->release_start_time < time())
                    {
                        $v->type_name = "待发布";
                    }
                    if($v->release_start_time <= time() && $v->release_end_time >= time())
                    {
                        $v->type_name = "发布中";
                        $v->status = "取消发布";
                    }
                    if($v->release_end_time <= time())
                    {
                        $v->type_name = "已发布";
                    }
                }else
                {
                    if($v->release_type == 1)
                        $v->type_name = "发布中";
                    else
                        $v->type_name = "已发布";
                }
            }else
            {
                $v->operation = '取消发布';
                $v->type_name = "已发布";
            }
            $v->release_time = empty($v->release_time) ? '/' : date('Y-m-d H:i:s', $v->release_time);
            $v->created_time = empty($v->created_time) ? '/' : date('Y-m-d H:i:s', $v->created_time);
            $v->release_start_time = empty($v->release_start_time) ? '/' : date('Y-m-d H:i:s', $v->release_start_time);
            $v->release_end_time = empty($v->release_end_time) ? '/' : date('Y-m-d H:i:s', $v->release_end_time);
            $v->valid_time = $v->release_start_time .' -- '. $v->release_end_time;
            $v->operation_time =  empty($v->operation_time) ? '/' : date('Y-m-d H:i:s', $v->operation_time);
            $data['list'][] = $v;
        }
        return  $data;
    }
}

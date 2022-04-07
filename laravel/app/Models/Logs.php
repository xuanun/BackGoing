<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Logs  extends Model
{
    protected $table = "easy_Logs";

    /**
     * 获取日志列表
     * @param $modular
     * @param $user_phone
     * @param $type
     * @param $status
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($modular, $user_phone, $type,  $status, $start_time, $end_time, $page_size)
    {
        $results = DB::table($this->table)
            ->select(DB::raw('id, modular, type, user_phone, user_dept, web_ip, status, created_time'));
        if($modular){
            $results = $results->where('modular', 'like', $modular);
        }
        if($user_phone){
            $results = $results->where('user_phone', 'like', $user_phone);
        }
        if($type){
            $results = $results->where('type',  $type);
        }
        if($status){
            $results = $results->where('status', 'like',$status);
        }
        if($start_time && $end_time){
            $results = $results->whereBetween('created_time', [$start_time, $end_time]);
        }
        $results = $results
            ->orderBy('created_time', 'desc')
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
     * 获取日志列表
     * @param $id
     * @return mixed
     */
    public function getDetail($id)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->first();
    }
    /**
     * @param $type
     * @param $modular
     * @param $path
     * @param $param
     * @param $user_id
     * @param $user_name
     * @param $user_phone
     * @param $user_dept
     * @param $web_ip
     * @param $status
     * 新增操作日志数据
     * @return mixed
     */
    public function addData($type, $modular, $path, $param, $user_id, $user_name, $user_phone, $user_dept, $web_ip, $status)
    {
        try{
            $insertArray = [
                'type' => $type,
                'modular' => $modular,
                'path' => $path,
                'param' => $param,
                'user_id' => $user_id,
                'user_name' => $user_name,
                'user_phone' => $user_phone,
                'user_dept' => $user_dept,
                'web_ip' => $web_ip,
                'status' => $status,
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

}

<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginLogs  extends Model
{
    protected $table = "easy_login_logs";

    /**
     * 获取日志列表
     * @param $web_ip
     * @param $user_name
     * @param $status
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getList($web_ip, $user_name, $status, $start_time, $end_time, $page_size)
    {
        $results = DB::table($this->table)
            ->select(DB::raw('id, type, user_name, account, browser_version, system_version, web_ip, status, created_time'));
        if($web_ip){
            $results = $results->where('web_ip', 'like', $web_ip);
        }
        if($user_name){
            $results = $results->where('user_name', 'like', $user_name);
        }
        if($status){
            $results = $results->where('status', 'like', $status);
        }
        if($start_time && $end_time){
            $results = $results->whereBetween('created_time', [$start_time, $end_time]);
        }
        $results = $results->orderBy('created_time', 'desc')->paginate($page_size);
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
     * @param $type
     * @param $user_id
     * @param $user_name
     * @param $account
     * @param $browser_version
     * @param $system_version
     * @param $web_ip
     * @param $status
     * 新增操作日志数据
     * @return mixed
     */
    public function addData($type, $user_id, $web_ip, $user_name, $account, $status, $browser_version, $system_version)
    {
        try{
            $insertArray = [
                'type' => $type,
                'user_id' => $user_id,
                'user_name' => $user_name,
                'account' => $account,
                'browser_version' => $browser_version,
                'system_version' => $system_version,
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

<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Report extends Model
{
    protected $table = "easy_report";
    /**
     * 获取数据列表
     * @param $address
     * @param $user_name
     * @param $examine_type
     * @param $release_type
     * @param $phone
     * @param $c_start_time
     * @param $c_end_time
     * @param $e_start_time
     * @param $e_end_time
     * @param $type
     * @param $page_size
     * @return mixed
     */
    public function getDataList($address, $examine_type, $release_type, $user_name, $phone, $c_start_time, $c_end_time, $e_start_time, $e_end_time,  $type, $page_size)
    {
        $results =  DB::table('easy_report as report')
            ->select(DB::raw('report.report_id, report.app_user_id, report.type, report.longitude, report.latitude, report.address, report.explain, report.voice_url, report.pictures_url, report.examine_type, report.examine_time, report.examine_web_uid, report.examine_explain, report.release_type, report.release_web_uid, report.release_time, report.release_content, report.release_start_time, report.release_end_time, report.update_time, report.creation_time, e_web_user.user_name as e_user_name, r_web_user.user_name as r_user_name, user.user_name as app_user_name, user.phone'))
            ->leftJoin('easy_app_user as user', 'user.user_id', '=', 'report.app_user_id')
            ->leftJoin('easy_web_user as e_web_user', 'e_web_user.id', '=', 'report.examine_web_uid')
            ->leftJoin('easy_web_user as r_web_user', 'r_web_user.id', '=', 'report.release_web_uid');
        if($address)
            $results = $results->where('report.address', 'like','%'.$address.'%');
        if($examine_type != '')
            $results = $results->where('report.examine_type', $examine_type);
        if($release_type)
            $results = $results->where('report.release_type', $release_type);
        if($phone)
            $results = $results->where('user.phone', 'like','%'.$phone.'%');
        if($user_name)
            $results = $results->where('user.user_name', 'like','%'.$user_name.'%');
        if($c_start_time && $c_end_time){
            $c_end_time = $c_end_time.' 23:59:59';
            $results = $results->whereBetween('report.creation_time', [strtotime($c_start_time), strtotime($c_end_time)]);
        }
        if($e_start_time && $e_end_time){
            $e_end_time = $e_end_time.' 23:59:59';
            $results = $results->whereBetween('report.examine_time', [strtotime($e_start_time), strtotime($e_end_time)]);
        }
        $results= $results
            ->where('report.type', $type)
            ->orderBy('report.report_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        $APP_IMG_URL = env('APP_IMG_URL');
        $APP_VOICE_URL = env('APP_VOICE_URL');
        $array = array();
        foreach($results as $v){
            $v->voice_url = empty($v->voice_url) ? '' : $APP_VOICE_URL.$v->voice_url;
            $img_url = json_decode($v->pictures_url);
            foreach ($img_url as $value)
            {
                $array[] = $APP_IMG_URL.$value;
            }
            $v->pictures_url = $array;
            $v->examine_time =  empty($v->examine_time) ? '/' : date('Y-m-d H:i:s', $v->examine_time);
            $v->creation_time = empty($v->creation_time) ? '/' : date('Y-m-d H:i:s', $v->creation_time);
            $v->release_time =  empty($v->release_time) ? '/' : date('Y-m-d H:i:s', $v->release_time);
            $v->release_start_time = empty($v->release_start_time) ? '/' : date('Y-m-d H:i:s', $v->release_start_time);
            $v->release_end_time = empty($v->release_end_time) ? '/' : date('Y-m-d H:i:s', $v->release_end_time);
            $v->update_time = empty($v->update_time) ? '/' : date('Y-m-d H:i:s', $v->update_time);
            $v->type_name = '';
            if($v->type == 1)
            {
                $v->type_name = "交通事故";
                $v->issue_content = $v->address. ','.$v->creation_time . ' 发生一起事故，请注意避让!';
            }
            if($v->type == 2)
            {
                $v->type_name = "交通拥堵";
                $v->issue_content = $v->address.  '拥堵, 请注意绕行!';
            }
            if($v->type == 3)
            {
                $v->type_name = "通讯故障";
                $v->issue_content = $v->address. ','.$v->creation_time. ' 信号灯故障，请及时避让!';
            }
            $v->examine_name = '';
            $v->status = '';
            if($v->examine_type == 0)
            {
                $v->examine_name = "审核未通过";
            }
            if($v->examine_type == 1)
            {
                $v->examine_name = "审核通过";
                $v->status = "发布";
            }
            if($v->examine_type == 2)
            {
                $v->examine_name = "审核中";
                $v->status = "审核";
            }
            if($v->release_type == 2 && $v->examine_type == 1)
            {
                $v->examine_name = "待发布";
                $v->status = "发布";
            }
            if($v->release_type == 1 && $v->examine_type == 1)
            {
                $v->examine_name = "发布中";
                $v->status = "取消发布";
            }
            if($v->release_type == 3 && $v->examine_type == 1)
            {
                $v->examine_name = "已发布";
            }
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * 获取数据列表
     * @param $explain
     * @param $examine_type
     * @param $release_type
     * @param $user_name
     * @param $phone
     * @param $c_start_time
     * @param $c_end_time
     * @param $e_start_time
     * @param $e_end_time
     * @param $type
     * @param $page_size
     * @return mixed
     */
    public function getComList($explain, $examine_type, $release_type, $user_name, $phone, $c_start_time, $c_end_time, $e_start_time, $e_end_time, $type, $page_size)
    {
        $results =  DB::table('easy_report as report')
            ->select(DB::raw('report.report_id, report.app_user_id, report.type, report.longitude, report.latitude, report.address, report.explain, report.voice_url, report.pictures_url, report.examine_type, report.examine_time, report.examine_web_uid, report.examine_explain, report.release_type, report.release_web_uid, report.release_time, report.release_content, report.release_start_time, report.release_end_time, report.update_time, report.creation_time, e_web_user.user_name as e_user_name, r_web_user.user_name as r_user_name, user.user_name as app_user_name, user.phone'))
            ->leftJoin('easy_app_user as user', 'user.user_id', '=', 'report.app_user_id')
            ->leftJoin('easy_web_user as e_web_user', 'e_web_user.id', '=', 'report.examine_web_uid')
            ->leftJoin('easy_web_user as r_web_user', 'r_web_user.id', '=', 'report.release_web_uid');
        if($explain)
            $results = $results->where('report.explain', 'like','%'.$explain.'%');
        if($examine_type != '')
            $results = $results->where('report.examine_type', $examine_type);
        if($release_type)
            $results = $results->where('report.release_type', $release_type);
        if($phone)
            $results = $results->where('user.phone', 'like','%'.$phone.'%');
        if($user_name)
            $results = $results->where('user.user_name', 'like','%'.$user_name.'%');
        if($c_start_time && $c_end_time){
            $results = $results->whereBetween('report.creation_time', [strtotime($c_start_time), strtotime($c_end_time)]);
        }
        if($e_start_time && $e_end_time){
            $results = $results->whereBetween('report.examine_time', [strtotime($e_start_time), strtotime($e_end_time)]);
        }
        $results= $results
            ->where('report.type', $type)
            ->orderBy('report.report_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        $APP_IMG_URL = env('APP_IMG_URL');
        $APP_VOICE_URL = env('APP_VOICE_URL');
        $array = array();
        foreach($results as $v){
            $v->voice_url = empty($v->voice_url) ? '' : $APP_VOICE_URL.$v->voice_url;
            $img_url = json_decode($v->pictures_url);
            foreach ($img_url as $value)
            {
                $array[] = $APP_IMG_URL.$value;
            }
            $v->pictures_url = $array;
            $v->examine_time =  empty($v->examine_time) ? '/' : date('Y-m-d H:i:s', $v->examine_time);
            $v->creation_time = empty($v->creation_time) ? '/' : date('Y-m-d H:i:s', $v->creation_time);
            $v->release_time =  empty($v->release_time) ? '/' : date('Y-m-d H:i:s', $v->release_time);
            $v->release_start_time = empty($v->release_start_time) ? '/' : date('Y-m-d H:i:s', $v->release_start_time);
            $v->release_end_time = empty($v->release_end_time) ? '/' : date('Y-m-d H:i:s', $v->release_end_time);
            $v->update_time = empty($v->update_time) ? '/' : date('Y-m-d H:i:s', $v->update_time);
            $v->type_name = '';
            if($v->type == 1)
            {
                $v->type_name = "交通事故";
                $v->issue_content = $v->address.','.$v->creation_time . ' 发生一起事故，请注意避让!';
            }
            if($v->type == 2)
            {
                $v->type_name = "交通拥堵";
                $v->issue_content = $v->address.  '拥堵, 请注意绕行!';
            }
            if($v->type == 3)
            {
                $v->type_name = "通讯故障";
                $v->issue_content = $v->address.','.$v->creation_time . ' 信号灯故障，请及时避让!';
            }
            $v->examine_name = '';
            $v->status = '';
            if($v->examine_type == 0 )
            {
                $v->examine_name = "审核未通过";
            }
            if($v->examine_type == 1)
            {
                $v->examine_name = "审核通过";
                $v->status = "发布";
            }
            if($v->examine_type == 2)
            {
                $v->examine_name = "审核中";
                $v->status = "审核";
            }
            if(($v->examine_type == 1) && ($v->release_type == 2 ))
            {
                $v->examine_name = "待发布";
                $v->status = "发布";
            }
            if(($v->examine_type == 1) && ($v->release_type == 1))
            {
                $v->examine_name = "发布中";
                $v->status = "取消发布";
            }
            if(($v->examine_type == 1) && ($v->release_type == 3))
            {
                $v->examine_name = "已发布";
            }
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * 路况---审核
     * @param $report_id
     * @param $type
     * @param $examine_type
     * @param $examine_web_uid
     * @param $examine_explain
     * @return mixed
     */
    public function examineData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'examine_type' => $examine_type,
                'examine_web_uid' => $examine_web_uid,
                'examine_explain' => $examine_explain,
                'examine_time' => time(),
                'update_time' => time(),
            ];
            $id = DB::table($this->table)->where('report_id', $report_id)->where('type', $type)->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'审核成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'审核失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 路况---审核并发布
     * @param $report_id
     * @param $type
     * @param $examine_type
     * @param $examine_web_uid
     * @param $examine_explain
     * @param $release_type
     * @param $release_web_uid
     * @param $release_content
     * @param $release_start_time
     * @param $release_end_time
     * @return mixed
     */
    public function examineAndReleaseData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain, $release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time)
    {
        $release_start_time = strtotime($release_start_time);
        $release_end_time = strtotime($release_end_time);
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'examine_type' => $examine_type,
                'examine_web_uid' => $examine_web_uid,
                'examine_explain' => $examine_explain,
                'examine_time' => time(),
                'release_type' => $release_type,
                'release_web_uid' => $release_web_uid,
                'release_content' => $release_content,
                'release_start_time' => $release_start_time,
                'release_end_time' => $release_end_time,
                'release_time' => time(),
                'update_time' => time(),
            ];
            $id = DB::table($this->table)->where('report_id', $report_id)->where('type', $type)->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'操作成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'操作失败', 'data'=>[]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 路况---发布
     * @param $report_id
     * @param $type
     * @param $release_type
     * @param $release_web_uid
     * @param $release_content
     * @param $release_start_time
     * @param $release_end_time
     * @return mixed
     */
    public function releaseData($report_id, $type, $release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time)
    {
        $release_start_time = strtotime($release_start_time);
        $release_end_time = strtotime($release_end_time);
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'release_type' => $release_type,
                'release_web_uid' => $release_web_uid,
                'release_content' => $release_content,
                'release_start_time' => $release_start_time,
                'release_end_time' => $release_end_time,
                'release_time' => time(),
                'update_time' => time(),
            ];
            $id = DB::table($this->table)
                ->where('report_id', $report_id)
                ->where('type', $type)
                ->where('examine_type', 1)
                ->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'操作成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'操作失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 查询批量审核 是否全是未审核状态
     * @param $shoot_ids
     * @param $examine_type
     * @return mixed
     */
    public function existExamineType($shoot_ids, $examine_type)
    {
        return  DB::table($this->table)
            ->whereIn('id', $shoot_ids)
            ->where('examine_type', '!=',$examine_type)
            ->exists();
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
     * 查询未审核数据条数
     * @param $type
     * @param $examine_type
     * @return mixed
     */
    public function getReportCount($type, $examine_type)
    {
        return  DB::table($this->table)
            ->where('type',$type)
            ->where('examine_type',$examine_type)
            ->count();
    }
}

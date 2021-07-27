<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShootHandy extends Model
{
    protected $table = "easy_shoot_handy";

    /**
     * 获取数据列表
     * @param $examine_type
     * @param $car_number
     * @param $type
     * @param $user_name
     * @param $phone
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * @return mixed
     */
    public function getDataList($examine_type, $car_number, $type, $user_name, $phone, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_shoot_handy as shoot')
            ->select(DB::raw('shoot.shoot_id, shoot.car_number, shoot.type, shoot.longitude, shoot.latitude, shoot.explain, shoot.voice_url, shoot.pictures_url, shoot.examine_type, shoot.examine_time, shoot.examine_web_uid, examine_explain, user.user_name as app_user_name, user.phone, web_user.user_name as web_user_name, shoot.update_time'))
            ->leftJoin('easy_app_user as user', 'user.user_id', '=', 'shoot.app_user_id')
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'shoot.examine_web_uid');
        if($examine_type !== '')
            $results = $results->where('shoot.examine_type', $examine_type);
        if($car_number !== '')
            $results = $results->where('shoot.car_number', 'like', '%'.$car_number.'%');
        if($type !== '')
            $results = $results->where('shoot.type', $type);
        if($phone !== '')
            $results = $results->where('user.phone', 'like','%'.$phone.'%');
        if($user_name)
            $results = $results->where('user.user_name', 'like','%'.$user_name.'%');
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('shoot.update_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results= $results
            ->orderBy('shoot_id','desc')
            ->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        $APP_IMG_URL = env('APP_IMG_URL');
        $APP_VOICE_URL = env('APP_VOICE_URL');
        foreach($results as $v){
            $array = array();
            $v->voice_url = empty($v->voice_url) ? '' : $APP_VOICE_URL.$v->voice_url;
            $img_url = json_decode($v->pictures_url);
            foreach ($img_url as $value)
            {
                $array[] = $APP_IMG_URL.$value;
            }
            $v->pictures_url = $array;
            $v->update_time = date('Y-m-d H:i:s', $v->update_time);
            $v->examine_time = empty($v->examine_time) ? '' : date('Y-m-d H:i:s', $v->examine_time);
            $v->type_name = '';
            if($v->type == 1)
                $v->type_name = "违停";
            if($v->type == 2)
                $v->type_name = "违法";
            if($v->type == 3)
                $v->type_name = "其他";
            $data['list'][] = $v;
        }
        return  $data;
    }

    /**
     * 随手拍-审核
     * @param $shoot_ids
     * @param $examine_type
     * @param $examine_web_uid
     * @param $examine_explain
     * @return mixed
     */
    public function editData($shoot_ids, $examine_type, $examine_web_uid, $examine_explain)
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
            $id = DB::table($this->table)->whereIn('shoot_id', $shoot_ids)->update($updateArray);
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
     * 查询批量审核 是否全是未审核状态
     * @param $shoot_ids
     * @param $examine_type
     * @return mixed
     */
    public function existExamineType($shoot_ids, $examine_type)
    {
        return  DB::table($this->table)
            ->whereIn('shoot_id', $shoot_ids)
            ->where('examine_type', '!=',$examine_type)
            ->exists();
    }

    /**
     * 查询未审核数据条数
     * @param $examine_type
     * @return mixed
     */
    public function getShootHandyCount($examine_type)
    {
        return  DB::table($this->table)
            ->where('examine_type',$examine_type)
            ->count();
    }

}

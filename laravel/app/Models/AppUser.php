<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppUser extends Model
{
    protected $table = "easy_app_user";
    const INVALID = 0;
    const NORMAL = 1;
    /**
     * @param $user_name
     * @param $phone
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * 查看APP人员列表信息
     * @return mixed
     */
    public function getList($user_name, $phone, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_app_user as user')
            ->select(DB::raw('user.user_id, user.phone, user.user_name, ident.audit_type, user.creation_time as register_time, ident.real_name, ident.gender, ident.id_card_no, ident.front_img, ident.behind_img'))
            ->leftJoin('easy_ident as ident', 'user.user_id', '=', 'ident.app_user_id');
        if($user_name)
            $results = $results->where('user.user_name', 'like','%'.$user_name.'%');
        if($phone)
            $results = $results->where('user.phone', $phone);
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('user.creation_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results =$results
            ->orderBy('user.user_id','asc')
            ->where('user.state', self::NORMAL)
            ->where('user.is_del', self::INVALID)
            ->paginate($page_size);

        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];
        $APP_IMG_URL = env('APP_IMG_URL');
        foreach($results as $v){
            $v->audit_type = empty($v->audit_type) ? 3 :$v->audit_type;
            $v->front_img = empty($v->front_img) ? '' : $APP_IMG_URL.$v->front_img;
            $v->behind_img = empty($v->behind_img) ? '' : $APP_IMG_URL.$v->behind_img;
            $v->gender_name = '';
            if($v->gender == 1)
                $v->gender_name = '男';
            if($v->gender == 2)
                $v->gender_name = '女';

            if($v->audit_type == 0)
            {
                $v->audit_name = '认证失败';
            }
            if($v->audit_type == 1)
            {
                $v->audit_name = '已认证';
            }
            if($v->audit_type == 2)
            {
                $v->audit_name = '认证中';
            }
            if($v->audit_type == 3)
            {
                $v->audit_name = '未提交';
            }
            $data['list'][] = $v;
        }
        return  $data;

    }
}

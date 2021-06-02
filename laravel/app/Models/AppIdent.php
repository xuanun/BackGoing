<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppIdent extends Model
{
    protected $table = "easy_ident";
    const INVALID = 0;
    const NORMAL = 1;
    /**
     * @param $audit_type
     * @param $real_name
     * @param $gender
     * @param $country
     * @param $audit_category
     * @param $start_time
     * @param $end_time
     * @param $user_star
     * @param $id_card_no
     * @param $phone
     * @param $web_user_ids
     * @param $page_size
     *
     * 查看所有人员列表信息（链表查询）
     * @return mixed
     */
    public function getList($audit_type, $real_name, $gender, $country, $audit_category,$start_time, $end_time, $user_star, $id_card_no, $phone, $web_user_ids, $page_size)
    {
        $results =  DB::table('easy_ident as ident')
            ->select(DB::raw('ident.ident_id as ident_id, ident.update_time as update_time, front_img, behind_img, real_name, gender, country, id_card_no, audit_web_user_id, audit_type, audit_remarks, audit_time, phone'))
            ->leftJoin('easy_app_user as user', 'user.user_id', '=', 'ident.app_user_id')
            ->where('ident.state', self::NORMAL);
        if($audit_type)
            $results = $results->where('ident.audit_type', $audit_type);
        if($real_name)
            $results = $results->where('ident.real_name', 'like','%'.$real_name.'%');
        if($gender)
            $results = $results->where('ident.gender', $gender);
        if($country)
            $results = $results->where('ident.country', $country);
        if($audit_category)
            $results = $results->where('ident.audit_category', $audit_category);
        if($start_time && $end_time){
            $results = $results->whereBetween('ident.update_time', [$start_time, $end_time]);
        }
        if($user_star)
            $results = $results->where('ident.user_star', $user_star);
        if($id_card_no)
            $results = $results->where('ident.id_card_no', $id_card_no);
        if($phone)
            $results = $results->where('ident.phone', $phone);
        if($web_user_ids)
            $results = $results->whereIn('ident.audit_web_user_id', $web_user_ids);
        $results = $results->orderBy('ident.update_time')->paginate($page_size);
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
}

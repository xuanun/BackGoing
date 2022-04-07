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
     * @param $audit_start_time
     * @param $audit_end_time
     * @param $page_size
     *
     * 查看所有人员列表信息（链表查询）
     * @return mixed
     */
    public function getList($audit_type, $real_name, $gender, $country, $audit_category,$start_time, $end_time, $user_star, $id_card_no, $phone, $web_user_ids, $audit_start_time, $audit_end_time, $page_size)
    {
        $results =  DB::table('easy_ident as ident')
            ->select(DB::raw('user.user_id, user.user_name, ident.ident_id as ident_id, ident.creation_time as update_time, ident.front_img, ident.behind_img, ident.real_name, ident.gender, ident.country, ident.id_card_no, audit_web_user_id, web_user.user_name as web_user_name, audit_type, audit_remarks, audit_time, user.phone'))
            ->leftJoin('easy_app_user as user', 'user.user_id', '=', 'ident.app_user_id')
            ->leftJoin('easy_web_user as web_user', 'web_user.id', '=', 'ident.audit_web_user_id');
        if($audit_type != '')
            $results = $results->where('ident.audit_type', $audit_type);
        if($real_name)
            $results = $results->where('ident.real_name', 'like','%'.$real_name.'%');
        if($gender !== '')
            $results = $results->where('ident.gender', $gender);
        if($country)
            $results = $results->where('ident.country', $country);
        if($audit_category)
            $results = $results->where('ident.audit_category', $audit_category);
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('ident.creation_time', [strtotime($start_time), strtotime($end_time)]);
        }
        if($audit_start_time && $audit_end_time){
            $audit_end_time = $audit_end_time.' 23:59:59';
            $results = $results->whereBetween('ident.audit_time', [strtotime($audit_start_time), strtotime($audit_end_time)]);
        }
        if($user_star)
            $results = $results->where('ident.user_star', $user_star);
        if($id_card_no)
            $results = $results->where('ident.id_card_no', 'like','%'.$id_card_no.'%');
        if($phone)
            $results = $results->where('ident.phone', $phone);
        if($web_user_ids)
            $results = $results->whereIn('ident.audit_web_user_id', $web_user_ids);
        $results = $results->orderBy('ident.update_time', 'desc')->paginate($page_size);
        $data = [
            'total'=>$results->total(),
            'currentPage'=>$results->currentPage(),
            'pageSize'=>$page_size,
            'list'=>[]
        ];

        $APP_IMG_URL = env('APP_IMG_URL');
        foreach($results as $v){
            $v->front_img = empty($v->front_img) ? '' : $APP_IMG_URL.$v->front_img;
            $v->behind_img = empty($v->behind_img) ? '' : $APP_IMG_URL.$v->behind_img;
            $v->audit_time = empty($v->audit_time) ? '/' : date('Y-m-d H:i:s',$v->audit_time);
            $v->update_time = empty($v->update_time) ? '/' : date('Y-m-d H:i:s',$v->update_time);
            if($v->audit_type == 0)
            {
                $v->audit_name = '未通过';
            }
            if($v->audit_type == 1)
            {
                $v->audit_name = '通过审核';
            }
            if($v->audit_type == 2)
            {
                $v->audit_name = '审核中';
            }
            if($v->audit_type == 3)
            {
                $v->audit_name = '未提交';
            }
            $data['list'][] = $v;

        }
        return  $data;

    }

    /**
     * 实名认证--审核
     * @param $ident_id
     * @param $audit_type
     * @param $audit_remarks
     * @param $audit_web_user_id
     * @return mixed
     */
    public function editIdent($ident_id, $audit_type, $audit_remarks, $audit_web_user_id)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'audit_type' => $audit_type,
                'audit_remarks' => $audit_remarks,
                'audit_web_user_id' => $audit_web_user_id,
                'audit_time' => time(),
                'update_time' => time(),
            ];
            $id = DB::table($this->table)->where('ident_id', $ident_id)->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'修改成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 查询批量审核 是否全是未审核状态
     * @param $ident_ids
     * @param $audit_type
     * @return mixed
     */
    public function existAuditType($ident_ids, $audit_type)
    {
        return  DB::table($this->table)
            ->whereIn('ident_ids', $ident_ids)
            ->where('audit_type', '!=',$audit_type)
            ->exists();
    }

    /**
     * 实名认证--批量审核
     * @param $ident_ids
     * @param $audit_type
     * @param $audit_remarks
     * @param $audit_web_user_id
     * @return mixed
     */
    public function batchEditAudit($ident_ids, $audit_type, $audit_remarks, $audit_web_user_id)
    {
        DB::beginTransaction();
        $return = array();
        try{
            $updateArray = [
                'audit_type' => $audit_type,
                'audit_remarks' => $audit_remarks,
                'audit_web_user_id' => $audit_web_user_id,
                'update_time' => time(),
            ];
            $id = DB::table($this->table)->whereIn('ident_id', $ident_ids)->update($updateArray);
            if($id){
                $return = ['code'=>20000,'msg'=>'修改成功', 'data'=>[]];
            }
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'修改失败', 'data'=>[$e->getMessage()]];
        }
        DB::commit();
        return $return;
    }

    /**
     * 查询未审核数据条数
     * @param $audit_type
     * @return mixed
     */
    public function getAuditCount($audit_type)
    {
        return  DB::table($this->table)
            ->where('audit_type',$audit_type)
            ->count();
    }
}

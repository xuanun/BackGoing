<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Survey extends Model
{
    protected $table = "easy_survey";
    /**
     * @param $user_name
     * @param $phone
     * @param $start_time
     * @param $end_time
     * @param $page_size
     * 网上调查列表
     * @return mixed
     */
    public function getList( $user_name, $phone, $start_time, $end_time, $page_size)
    {
        $results =  DB::table('easy_survey as survey')
            ->select(DB::raw('survey.id,  user.user_name, user.phone, survey.stars, survey.feedback, survey.created_time'))
            ->leftJoin('easy_app_user as user', 'user.user_id', '=', 'survey.app_user_id');
        if($user_name)
            $results = $results->where('user.user_name', 'like','%'.$user_name.'%');
        if($phone)
            $results = $results->where('user.phone', 'like', '%'.$phone.'%');
        if($start_time && $end_time){
            $end_time = $end_time.' 23:59:59';
            $results = $results->whereBetween('survey.created_time', [strtotime($start_time), strtotime($end_time)]);
        }
        $results =$results
            ->orderBy('survey.id','asc')
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
}

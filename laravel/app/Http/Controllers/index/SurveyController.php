<?php


namespace App\Http\Controllers\index;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyController extends Controller
{
    /**
     * 网上调查 --列表
     * @param Request $request
     * @return mixed
     */
    public function surveyList(Request $request)
    {
        $input = $request->all();
        $user_name = isset($input['user_name']) ? $input['user_name'] : ''; //用户名
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;
        $model_survey = new Survey();
        $survey_data = $model_survey->getList($user_name, $phone, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$survey_data];
        return response()->json($return_data);
    }
}

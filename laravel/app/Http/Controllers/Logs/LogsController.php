<?php


namespace App\Http\Controllers\Logs;


use App\Http\Controllers\Controller;
use App\Models\LoginLogs;
use App\Models\Logs;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    /**
     * 操作日志---列表
     * @param Request $request
     * @return mixed
     */
    public function operationList(Request $request)
    {
        $input = $request->all();
        $modular = isset($input['modular']) ? $input['modular'] : '';
        $user_phone = isset($input['user_phone']) ? $input['user_phone'] : '';
        $type = isset($input['type']) ? $input['type'] : '';
        $status = isset($input['status']) ? $input['status'] : '';
        $start_time = isset($input['start_time']) ? strtotime($input['start_time']) : '';
        $end_time = isset($input['end_time']) ? strtotime($input['end_time']) : '';
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;
        if($status == '所有')
            $status = '';

        $model_operation = new Logs();
        $results = $model_operation->getList($modular, $user_phone, $type,  $status, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$results];
        return response()->json($return_data);
    }

    /**
     * 操作日志---详情
     * @param Request $request
     * @return mixed
     */
    public function operationDetail(Request $request)
    {
        $input = $request->all();
        $id = isset($input['id']) ? $input['id'] : '';
        $model_operation = new Logs();
        $result = $model_operation->getDetail($id);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$result];
        return response()->json($return_data);
    }

    /**
     * 操作日志---列表
     * @param Request $request
     * @return mixed
     */
    public function loginList(Request $request)
    {
        $input = $request->all();
        $web_ip = isset($input['web_ip']) ? $input['web_ip'] : '';
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';
        $status = isset($input['status']) ? $input['status'] : '';
        $start_time = isset($input['start_time']) ? strtotime($input['start_time']) : '';
        $end_time = isset($input['end_time']) ? strtotime($input['end_time']) : '';
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;
        if($status == '所有')
            $status = '';
        $model_login = new LoginLogs();
        $results = $model_login->getList($web_ip, $user_name, $status, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$results];
        return response()->json($return_data);
    }

}

<?php


namespace App\Http\Controllers\issue;


use App\Http\Controllers\Controller;
use App\Models\MsgType;
use App\Models\PushMassage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MassageController  extends Controller
{
    /**
     * 推送消息---类型列表
     * @param Request $request
     * @return mixed
     */
    public function MsgTypeList(Request $request)
    {
        $model_msg_type = new MsgType();
        $type_data = $model_msg_type->getAll();
        $return_data = array();
        foreach ($type_data as $v)
        {
            if($v->p_id == 0)
            {
                $array = array();
                $array['id'] = $v->id;
                $array['type_name'] = $v->type_name;
                $array['list'] = [];
                $return_data[] = $array;
            }
        }

        $return_arr = array();
        foreach ($return_data as $p)
        {
            foreach ($type_data as $v)
            {
                if($v->p_id == $p['id'])
                {
                    $array = array();
                    $array['id'] = $v->id;
                    $array['type_name'] = $v->type_name;
                    $p['list'][] = $array;
                }
            }
            $return_arr[] = $p;
        }
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$return_arr];
        return response()->json($return_data);
    }

    /**
     * 推送服务---列表
     * @param Request $request
     * @return mixed
     */
    public function massageList(Request $request)
    {
        $input = $request->all();
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//发布人名字
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : '';//创建时间开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : '';//创建时间结束时间
        $r_start_time = isset($input['r_start_time']) ? $input['r_start_time'] : ''; //发布开始时间
        $r_end_time = isset($input['r_end_time']) ? $input['r_end_time'] : ''; //发布结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_msg = new PushMassage();
        $msg_data = $model_msg->getList($release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size);
        $msg_array = array();
        $model_msg_type = new MsgType();
        foreach($msg_data['list'] as $v)
        {
            $v->type_name = '';
            if($v->second_type_id == 0)
                $v->type_name = $model_msg_type->getTypeName($v->first_type_id);
            else
                $v->type_name = $model_msg_type->getTypeName($v->second_type_id);
            $msg_array[] = $v;
        }
        $msg_data['list'] = $msg_array;
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$msg_data];
        return response()->json($return_data);
    }

    /**
     * 推送消息---新增
     * @param Request $request
     * @return mixed
     */
    public function addMassage(Request $request)
    {
        $input = $request->all();
        $first_type_id = isset($input['first_type_id']) ? $input['first_type_id'] : ''; //一级类型ID
        $second_type_id = isset($input['second_type_id']) ? $input['second_type_id'] : ''; //二级类型ID
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : ''; //内容
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $valid_start_time = isset($input['valid_start_time']) ? $input['valid_start_time'] : ''; //有效时间开始时间
        $valid_end_time = isset($input['valid_end_time']) ? $input['valid_end_time'] : ''; //有效时间结束时间
        if(empty($valid_start_time) || empty($valid_end_time)) return response()->json(['code' => 40000, 'msg' => '有效时间不能为空', 'data' => []]);
        $model_msg = new PushMassage();
        $return_data = $model_msg->addData( $first_type_id, $second_type_id, $title, $content, $release_web_uid, $valid_start_time, $valid_end_time);
        return response()->json($return_data);
    }
}

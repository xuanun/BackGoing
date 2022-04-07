<?php


namespace App\Http\Controllers\issue;

use App\Http\Controllers\Controller;
use App\Models\Dynamic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
{
    /**
     * 交通管制---列表
     * @param Request $request
     * @return mixed
     */
    public function trafficList(Request $request)
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

        $model_dynamic = new Dynamic();
        $category = 1; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
//        $update_data = $model_dynamic->updateStatus();
//        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $dynamic_data = $model_dynamic->getList($category, $release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$dynamic_data];
        return response()->json($return_data);
    }

    /**
     * 交通管制---新增
     * @param Request $request
     * @return mixed
     */
    public function addTraffic(Request $request)
    {
        $input = $request->all();
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : ''; //内容
        $release_type = 2; //发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //有效时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //有效时间结束时间
        if(empty($release_start_time) || empty($release_end_time)) return response()->json(['code' => 40000, 'msg' => '有效时间不能为空', 'data' => []]);
        $model_dynamic = new Dynamic();
        $category = 1; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->addData( $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 交通管制---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function trafficRelease(Request $request)
    {
        $input = $request->all();
        $dynamic_id = isset($input['dynamic_id']) ? $input['dynamic_id'] : ''; //数据ID
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : 0; //内容
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布 4:取消发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($dynamic_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $model_dynamic = new Dynamic();
        $category = 1; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->releaseData($dynamic_id, $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 占道施工---列表
     * @param Request $request
     * @return mixed
     */
    public function jeevesList(Request $request)
    {
        $input = $request->all();
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        //$release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : '';//发布人ID
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//发布人名字
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : '';//创建时间开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : '';//创建时间结束时间
        $r_start_time = isset($input['r_start_time']) ? $input['r_start_time'] : ''; //发布开始时间
        $r_end_time = isset($input['r_end_time']) ? $input['r_end_time'] : ''; //发布结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_dynamic = new Dynamic();
        $category = 2; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
//        $update_data = $model_dynamic->updateStatus();
//        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $dynamic_data = $model_dynamic->getList($category, $release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$dynamic_data];
        return response()->json($return_data);
    }

    /**
     * 占道施工---新增
     * @param Request $request
     * @return mixed
     */
    public function addJeeves(Request $request)
    {
        $input = $request->all();
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : ''; //内容
        $release_type = 2; //发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //有效时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //有效时间
        if(empty($release_start_time) || empty($release_end_time)) return response()->json(['code' => 40000, 'msg' => '有效时间不能为空', 'data' => []]);
        $model_dynamic = new Dynamic();
        $category = 2; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->addData( $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 占道施工---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function jeevesRelease(Request $request)
    {
        $input = $request->all();
        $dynamic_id = isset($input['dynamic_id']) ? $input['dynamic_id'] : ''; //数据ID
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : 0; //内容
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布 4:取消发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($dynamic_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $model_dynamic = new Dynamic();
        $category = 2; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->releaseData($dynamic_id, $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 限行限号---列表
     * @param Request $request
     * @return mixed
     */
    public function restrictList(Request $request)
    {
        $input = $request->all();
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布 4:取消发布
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        //$release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : '';//发布人ID
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//发布人名字
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : '';//创建时间开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : '';//创建时间结束时间
        $r_start_time = isset($input['r_start_time']) ? $input['r_start_time'] : ''; //发布开始时间
        $r_end_time = isset($input['r_end_time']) ? $input['r_end_time'] : ''; //发布结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_dynamic = new Dynamic();
        $category = 3; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
//        $update_data = $model_dynamic->updateStatus();
//        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $dynamic_data = $model_dynamic->getList($category, $release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$dynamic_data];
        return response()->json($return_data);
    }

    /**
     * 限行限号---新增
     * @param Request $request
     * @return mixed
     */
    public function addRestrict(Request $request)
    {
        $input = $request->all();
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : ''; //内容
        $release_type = 2; //发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //有效时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //有效时间
        if(empty($release_start_time) || empty($release_end_time)) return response()->json(['code' => 40000, 'msg' => '有效时间不能为空', 'data' => []]);
        $model_dynamic = new Dynamic();
        $category = 3; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->addData( $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }
    /**
     * 限行限号---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function restrictRelease(Request $request)
    {
        $input = $request->all();
        $dynamic_id = isset($input['dynamic_id']) ? $input['dynamic_id'] : ''; //数据ID
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : 0; //内容
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布 4:取消发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($dynamic_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $model_dynamic = new Dynamic();
        $category = 3; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->releaseData($dynamic_id, $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 气象信息---列表
     * @param Request $request
     * @return mixed
     */
    public function weatherList(Request $request)
    {
        $input = $request->all();
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        //$release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : '';//发布人ID
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//发布人名字
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : '';//创建时间开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : '';//创建时间结束时间
        $r_start_time = isset($input['r_start_time']) ? $input['r_start_time'] : ''; //发布开始时间
        $r_end_time = isset($input['r_end_time']) ? $input['r_end_time'] : ''; //发布结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_dynamic = new Dynamic();
        $category = 4; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
//        $update_data = $model_dynamic->updateStatus();
//        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $dynamic_data = $model_dynamic->getWeatherList($category, $release_type, $title, $user_name, $c_start_time, $c_end_time, $r_start_time, $r_end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$dynamic_data];
        return response()->json($return_data);
    }

    /**
     * 气象信息---新增
     * @param Request $request
     * @return mixed
     */
    public function addWeather(Request $request)
    {
        $input = $request->all();
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : ''; //内容
        $release_type = 2; //发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //有效时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //有效时间
        if(empty($release_start_time) || empty($release_end_time)) return response()->json(['code' => 40000, 'msg' => '有效时间不能为空', 'data' => []]);
        $model_dynamic = new Dynamic();
        $category = 4; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->addData( $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 气象信息---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function weatherRelease(Request $request)
    {
        $input = $request->all();
        $dynamic_id = isset($input['dynamic_id']) ? $input['dynamic_id'] : ''; //数据ID
        $title = isset($input['title']) ? $input['title'] : ''; //标题
        $content = isset($input['content']) ? $input['content'] : 0; //内容
        $release_type = isset($input['release_type']) ? $input['release_type'] : ''; //发布状态 1:发布中 2:待发布 3:已发布 4:取消发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($dynamic_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $model_dynamic = new Dynamic();
        $category = 4; //类别 1:交通管制 2:施工占道 3:限行限号 4:气象预警
        $return_data = $model_dynamic->releaseData($dynamic_id, $title, $content, $release_type, $release_web_uid, $release_start_time, $release_end_time, $category);
        return response()->json($return_data);
    }

    /**
     * 上传图片
     * @param Request $request
     * @return mixed
     */
    public function uploadImg(Request $request)
    {
        if ($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
            $tmp = $request->file('file');
            if ($tmp->isValid()) { //判断文件上传是否有效
                $FileType = $tmp->getClientOriginalExtension(); //获取文件后缀

                $FilePath = $tmp->getRealPath(); //获取文件临时存放位置

                $FileName = date('Ymd') . uniqid() . '.' . $FileType; //定义文件名

                Storage::disk('img')->put($FileName, file_get_contents($FilePath)); //存储文件
                $IMAGE_URL = env('IMAGE_URL');
                $IMG_URL = env('IMG_URL');
                $obj['url'] = $IMAGE_URL.$IMG_URL. $FileName;
                $data['code'] = 20000;
                $data['data'] = $obj;
                $data['file_name'] = $IMG_URL.$FileName;
                $data['msg'] = "";
                $data['time'] = time();
                return response()->json($data);
            }
        }
    }
}

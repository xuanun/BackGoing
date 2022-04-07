<?php


namespace App\Http\Controllers\video;

use App\Http\Controllers\Controller;
use App\Models\Publicity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PublicityController extends Controller
{
    /**
     * 安全宣传---列表
     * @param Request $request
     * @return mixed
     */
    public function publicityList(Request $request)
    {
        $input = $request->all();
        $title = isset($input['title']) ? $input['title'] : ''; //视频标题
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_publicity = new Publicity();
        $publicity_data = $model_publicity->getList($title, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$publicity_data];
        return response()->json($return_data);
    }

    /**
     * 安全宣传---新增数据
     * @param Request $request
     * @return mixed
     */
    public function addPublicity(Request $request)
    {
        $input = $request->all();
        $video_url = isset($input['video_url']) ? $input['video_url'] : 0;//视频地址
        if(empty($video_url)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadVideo($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $video_url = $upload_data['file_name'];
        }
        $title = isset($input['title']) ? $input['title'] : ''; //视频标题
        $user_id = isset($input['user_id']) ? $input['user_id'] : 0;//上传人员ID
        $status = isset($input['status']) ? $input['status'] : 0;//状态 1:正常 0:异常
        if(empty($video_url) || empty($title) || empty($user_id)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        $model_publicity = new Publicity();
        $publicity_data = $model_publicity->addData($title, $video_url, $status, $user_id);
        return response()->json($publicity_data);
    }

    /**
     * 安全宣传---编辑数据
     * @param Request $request
     * @return mixed
     */
    public function editPublicity(Request $request)
    {
        $input = $request->all();
        $id = isset($input['id']) ? $input['id'] : 0;//id
        $video_url = isset($input['video_url']) ? $input['video_url'] : 0;//视频地址
        if(empty($video_url)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadVideo($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $video_url = $upload_data['file_name'];
        }
        $title = isset($input['title']) ? $input['title'] : ''; //视频标题
        $user_id = isset($input['user_id']) ? $input['user_id'] : 0;//上传人员ID
        $status = isset($input['status']) ? $input['status'] : 0;//状态 1:正常 0:异常
        if(empty($video_url) || empty($title) || empty($user_id)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        $model_publicity = new Publicity();
        $publicity_data = $model_publicity->editData($id, $title, $video_url, $status, $user_id);
        return response()->json($publicity_data);
    }


    /**
     * 安全宣传---批量删除数据
     * @param Request $request
     * @return mixed
     */
    public function batchDelPublicity(Request $request)
    {
        $input = $request->all();
        $ids = isset($input['ids']) ? $input['ids'] : []; //数据ID集合
        $model_publicity = new Publicity();
        $return_data = $model_publicity->delIds($ids);
        return response()->json($return_data);
    }

    /**
     * 安全宣传---上传视频
     * @param $tmp
     * @return mixed
     */
    public function uploadVideo($tmp)
    {
        if(empty($tmp)) return ['code'=>40000,'msg'=>'文件流不存在', 'data'=>[]];
        if ($tmp->isValid())
        { //判断文件上传是否有效
            $FileType = $tmp->getClientOriginalExtension(); //获取文件后缀
            $FilePath = $tmp->getRealPath(); //获取文件临时存放位置
            $FileName = date('Ymd') . uniqid() . '.' . $FileType; //定义文件名
            Storage::disk('video')->put($FileName, file_get_contents($FilePath)); //存储文件
            $VIDEO_URL= env('VIDEO_URL');
            $VIDEOS_URL= env('VIDEOS_URL');
            $data['url'] = $VIDEO_URL.$VIDEOS_URL. $FileName;
            $data['code'] = 20000;
            $data['file_name'] = $VIDEOS_URL.$FileName;
            return $data;
        }
        return ['code'=>40000,'msg'=>'文件不存在', 'data'=>[]];
    }


}

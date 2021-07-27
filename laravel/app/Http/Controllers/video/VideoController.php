<?php


namespace App\Http\Controllers\video;


use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\VideoPicture;
use App\Models\VpRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{

    /**
     * 诱导屏、视频 ---全部列表
     * @param Request $request
     * @return mixed
     */
    public function allDataList(Request $request)
    {
        $input = $request->all();
        $model_video = new VideoPicture();
        $list_data = $model_video->getAllList();
        foreach ($list_data as $v)
        {
            $URL = '';
            if($v->type == 1)
                $URL= env('VIDEO_URL');
            if($v->type == 2)
                $URL= env('IMAGE_URL');
            $v->vp_url = $URL.$v->vp_url;
        }
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$list_data];
        return response()->json($return_data);
    }

    /**
     * 诱导屏---列表
     * @param Request $request
     * @return mixed
     */
    public function pictureList(Request $request)
    {
        $input = $request->all();
        $vp_code = isset($input['vp_code']) ? $input['vp_code'] : ''; //诱导屏code
        $vp_name = isset($input['vp_name']) ? $input['vp_name'] : ''; //诱导屏名称
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_video = new VideoPicture();
        $type = 2; //类别 1:视频 2:诱导屏
        $picture_data = $model_video->getList($type, $vp_code, $vp_name, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$picture_data];
        return response()->json($return_data);
    }

    /**
     * 诱导屏---新增数据
     * @param Request $request
     * @return mixed
     */
    public function addPicture(Request $request)
    {
        $input = $request->all();
        $vp_url = isset($input['vp_url']) ? $input['vp_url'] : 0;//视频图片地址
        if(empty($vp_url)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadImage($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $vp_url = $upload_data['file_name'];
        }
        $vp_code = isset($input['vp_code']) ? $input['vp_code'] : 0;//视频图片code
        $vp_name = isset($input['vp_name']) ? $input['vp_name'] : 0;//视频图片名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $user_id = isset($input['user_id']) ? $input['user_id'] : 0;//上传人员ID
        $state = isset($input['state']) ? $input['state'] : 0;//状态 1:正常 0:异常
        if(empty($vp_url) || empty($vp_code) || empty($vp_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
//        $model_vr = new VpRegion();
//        $del_data = $model_vr->delIds([$region_id]);
        $type = 2;//类型 1:视频 2:诱导图
        DB::beginTransaction();
        $model_video = new VideoPicture();
        $video_data = $model_video->addData($vp_code,  $type, $vp_name, $longitude, $latitude, $vp_url, $state, $user_id);
        if($video_data['code'] != 20000) return response()->json( $video_data);
        //判断新加的点在不在已存在的区域内 如果存在增加关联关系
        $model_region = new Region();
        $model_vp_region = new VpRegion();
        $area_list = $model_region->getAllList();
        $return_data = $video_data;
        foreach ($area_list as $v)
        {
            $GIs_array = json_decode($v->GIs);
            $in_area = $this->pointInArea($longitude,$latitude, $GIs_array);
            //如果在区域内 添加数据
            if($in_area)
            {
                $exists_status = $model_vp_region->existsData($v->region_id, $video_data['data']['vp_id']);
                if(empty($exists_status))
                {
                    $return_data = $model_vp_region->addData($v->region_id, $video_data['data']['vp_id']);
                }
            }
        }
        DB::commit();
        return response()->json($return_data);
    }

    /**
     * 诱导屏---编辑数据
     * @param Request $request
     * @return mixed
     */
    public function editPicture(Request $request)
    {
        $input = $request->all();
        $vp_id = isset($input['vp_id']) ? $input['vp_id'] : 0;//数据ID
        $vp_url = isset($input['vp_url']) ? $input['vp_url'] : 0;//视频图片地址
        if(empty($vp_url)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadImage($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $vp_url = $upload_data['file_name'];
        }
        $vp_code = isset($input['vp_code']) ? $input['vp_code'] : 0;//视频图片code
        $vp_name = isset($input['vp_name']) ? $input['vp_name'] : 0;//视频图片名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $user_id = isset($input['user_id']) ? $input['user_id'] : 0;//上传人员ID
        $state = isset($input['state']) ? $input['state'] : 0;//状态 1:正常 0:异常
        if(empty($vp_url) || empty($vp_code) || empty($vp_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        $IMAGE_URL = env('IMAGE_URL');
        $vp_url = str_replace($IMAGE_URL,'',$vp_url);
        DB::beginTransaction();
        $model_vr = new VpRegion();
        $del_data = $model_vr->delVpId($vp_id);
        if($del_data['code'] != 20000) return response()->json( $del_data);
        $type = 2;//类型 1:视频 2:诱导图
        $model_video = new VideoPicture();
        $video_data =  $model_video->editData($vp_id, $vp_code, $vp_name, $type,$longitude, $latitude, $vp_url, $state, $user_id);
        if($video_data['code'] != 20000) return response()->json( $video_data);
        //判断新加的点在不在已存在的区域内 如果存在增加关联关系
        $model_region = new Region();
        $model_vp_region = new VpRegion();
        $area_list = $model_region->getAllList();
        $return_data = ['code'=>40000,'msg'=>'操作失败', 'data'=>[]];
        foreach ($area_list as $v)
        {
            if($v->GIs)
            {
                $GIs_array = json_decode($v->GIs);
                $in_area = $this->pointInArea($longitude,$latitude, $GIs_array);
                //如果在区域内 添加数据
                if($in_area)
                {
                    $exists_status = $model_vp_region->existsData($v->region_id, $vp_id);
                    if(empty($exists_status))
                    {
                        $return_data = $model_vp_region->addData($v->region_id, $vp_id);
                    }
                }
            }

        }
        if($return_data['code'] == 20000)
            $return_data['msg'] = '修改成功';
        else
            $return_data['msg'] = '修改失败';
        DB::commit();
        return response()->json($return_data);
    }


    /**
     * 诱导屏---批量删除数据
     * @param Request $request
     * @return mixed
     */
    public function batchDelPicture(Request $request)
    {
        $input = $request->all();
        $vp_ids = isset($input['vp_ids']) ? $input['vp_ids'] : []; //数据ID集合
        $type = 2;//类型 1:视频 2:诱导图
        $model_video = new VideoPicture();
        $exist_type = $model_video->existType($vp_ids, $type);
        if($exist_type) return response()->json(['code'=>40000,'msg'=>'所选列表中类型不一致', 'data'=>[]]);
        $return_data = $model_video->delIds($vp_ids);
        return response()->json($return_data);
    }

    /**
     * 上传图片
     * @param $tmp
     * @return mixed
     */
    public function uploadImage($tmp)
    {
        if(empty($tmp)) return ['code'=>40000,'msg'=>'文件流不存在', 'data'=>[]];
        if ($tmp->isValid())
        { //判断文件上传是否有效
            $FileType = $tmp->getClientOriginalExtension(); //获取文件后缀
            $FilePath = $tmp->getRealPath(); //获取文件临时存放位置
            $FileName = date('Ymd') . uniqid() . '.' . $FileType; //定义文件名
            Storage::disk('images')->put($FileName, file_get_contents($FilePath)); //存储文件
            $IMAGE_URL = env('IMAGE_URL');
            $IMAGES_URL= env('IMAGES_URL');
            $data['url'] = $IMAGE_URL.$IMAGES_URL. $FileName;
            $data['code'] = 20000;
            $data['file_name'] = $IMAGES_URL.$FileName;
            return $data;
        }
        return ['code'=>40000,'msg'=>'文件不存在', 'data'=>[]];
    }


    /**
     * 视频---列表
     * @param Request $request
     * @return mixed
     */
    public function videoList(Request $request)
    {
        $input = $request->all();
        $vp_code = isset($input['vp_code']) ? $input['vp_code'] : ''; //诱导屏code
        $vp_name = isset($input['vp_name']) ? $input['vp_name'] : ''; //诱导屏名称
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;//每页条数
        $page =  isset($input['page']) ? $input['page'] : 1;//当前页

        $model_video = new VideoPicture();
        $type = 1; //类别 1:视频 2:诱导屏
        $picture_data = $model_video->getList($type, $vp_code, $vp_name, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$picture_data];
        return response()->json($return_data);
    }

    /**
     * 视频---新增数据
     * @param Request $request
     * @return mixed
     */
    public function addVideo(Request $request)
    {
        $input = $request->all();
        $vp_url = isset($input['vp_url']) ? $input['vp_url'] : 0;//视频图片地址
        if(empty($avatar)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadVideo($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $vp_url = $upload_data['file_name'];
        }
        $vp_code = isset($input['vp_code']) ? $input['vp_code'] : 0;//视频图片code
        $vp_name = isset($input['vp_name']) ? $input['vp_name'] : 0;//视频图片名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $user_id = isset($input['user_id']) ? $input['user_id'] : 0;//上传人员ID
        $state = isset($input['state']) ? $input['state'] : 0;//状态 1:正常 0:异常
        if(empty($vp_url) || empty($vp_code) || empty($vp_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        $type = 1;//类型 1:视频 2:诱导图
        DB::beginTransaction();
        $model_video = new VideoPicture();
        $video_data = $model_video->addData($vp_code,  $type, $vp_name, $longitude, $latitude, $vp_url, $state, $user_id);
        if($video_data['code'] != 20000) return response()->json( $video_data);
        //判断新加的点在不在已存在的区域内 如果存在增加关联关系
        $model_region = new Region();
        $model_vp_region = new VpRegion();
        $area_list = $model_region->getAllList();
        $return_data = $video_data;
        foreach ($area_list as $v)
        {
            $GIs_array = json_decode($v->GIs);
            $in_area = $this->pointInArea($longitude,$latitude, $GIs_array);
            //如果在区域内 添加数据
            if($in_area)
            {
                $exists_status = $model_vp_region->existsData($v->region_id, $video_data['data']['vp_id']);
                if(empty($exists_status))
                {
                    $return_data = $model_vp_region->addData($v->region_id, $video_data['data']['vp_id']);
                }
            }
        }
        DB::commit();
        return response()->json($return_data);
    }

    /**
     * 视频---编辑数据
     * @param Request $request
     * @return mixed
     */
    public function editVideo(Request $request)
    {
        $input = $request->all();
        $vp_id = isset($input['vp_id']) ? $input['vp_id'] : 0;//数据ID
        $vp_url = isset($input['vp_url']) ? $input['vp_url'] : 0;//视频图片地址
        if(empty($vp_url)){
            $tmp = $request->file('file');
            if($request->isMethod('POST')) { //判断文件是否是 POST的方式上传
                $upload_data = $this->uploadVideo($tmp);
            }else{
                return response()->json(['code'=>40000,'msg'=>'上传方式错误', 'data'=>[]]);
            }
            if($upload_data['code'] != 20000) return response()->json($upload_data);
            $vp_url = $upload_data['file_name'];
        }
        $vp_code = isset($input['vp_code']) ? $input['vp_code'] : 0;//视频图片code
        $vp_name = isset($input['vp_name']) ? $input['vp_name'] : 0;//视频图片名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $user_id = isset($input['user_id']) ? $input['user_id'] : 0;//上传人员ID
        $state = isset($input['state']) ? $input['state'] : 0;//状态 1:正常 0:异常
        if(empty($vp_url) || empty($vp_code) || empty($vp_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        $VIDEO_URL = env('VIDEO_URL');
        $vp_url = str_replace($VIDEO_URL,'',$vp_url);
        DB::beginTransaction();
        $model_vr = new VpRegion();
        $del_data = $model_vr->delVpId($vp_id);
        if($del_data['code'] != 20000) return response()->json( $del_data);
        $type = 1;//类型 1:视频 2:诱导图
        $model_video = new VideoPicture();
        $video_data =  $model_video->editData($vp_id, $vp_code, $vp_name, $type,$longitude, $latitude, $vp_url, $state, $user_id);
        if($video_data['code'] != 20000) return response()->json( $video_data);
        //判断新加的点在不在已存在的区域内 如果存在增加关联关系
        $model_region = new Region();
        $model_vp_region = new VpRegion();
        $area_list = $model_region->getAllList();
        $return_data = ['code'=>20000,'msg'=>'操作失败', 'data'=>[]];
        foreach ($area_list as $v)
        {
            if($v->GIs)
            {
                $GIs_array = json_decode($v->GIs);
                $in_area = $this->pointInArea($longitude,$latitude, $GIs_array);
                //如果在区域内 添加数据
                if($in_area)
                {
                    $exists_status = $model_vp_region->existsData($v->region_id, $vp_id);
                    if(empty($exists_status))
                    {
                        $return_data = $model_vp_region->addData($v->region_id, $vp_id);
                    }
                }
            }
        }
        if($return_data['code'] == 20000)
            $return_data['msg'] = '修改成功';
        else
            $return_data['msg'] = '修改失败';
        DB::commit();
        return response()->json($return_data);
    }


    /**
     * 视频---批量删除数据
     * @param Request $request
     * @return mixed
     */
    public function batchDelVideo(Request $request)
    {
        $input = $request->all();
        $vp_ids = isset($input['vp_ids']) ? $input['vp_ids'] : []; //数据ID集合
        $type = 1;//类型 1:视频 2:诱导图
        $model_video = new VideoPicture();
        $exist_type = $model_video->existType($vp_ids, $type);
        if($exist_type) return response()->json(['code'=>40000,'msg'=>'所选列表中类型不一致', 'data'=>[]]);
        $return_data = $model_video->delIds($vp_ids);
        return response()->json($return_data);
    }

    /**
     * 上传视频
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

    /**
     * 高点视频区域---列表
     * @param Request $request
     * @return mixed
     */
    public function areaList(Request $request)
    {
        $input = $request->all();
        $area_name = isset($input['area_name']) ? $input['area_name'] : ''; //区域名
        $user_name = isset($input['user_name']) ? $input['user_name'] : ''; //操作人名字
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;//每页条数
        $page =  isset($input['page']) ? $input['page'] : 1;//当前页

        $model_region = new Region();
        $region_data = $model_region->getList($area_name, $user_name, $start_time, $end_time, $page_size);
        $list = array();
        $model_vr = new  VpRegion();
        foreach ($region_data['list'] as $v)
        {
            $v->video_amount = $model_vr->getCount($v->region_id, 1);
            $v->picture_amount = $model_vr->getCount($v->region_id, 2);
            $list[] = $v;
        }
        $region_data['list'] = $list;
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$region_data];
        return response()->json($return_data);
    }

    /**
     * 高点视频区域---新增数据
     * @param Request $request
     * @return mixed
     */
    public function addArea(Request $request)
    {
        $input = $request->all();
        $area_name = isset($input['area_name']) ? $input['area_name'] : ''; //区域名字
        $explain = isset($input['explain']) ? $input['explain'] : ''; //区域说明
        $GIs = isset($input['GIs']) ? $input['GIs'] : 0;//地理信息(json格式经纬度，顶点坐标集合)
        $user_id = isset($input['user_id']) ? $input['user_id'] : ''; //操作人ID
        //$vp_ids = isset($input['vp_ids']) ? $input['vp_ids'] : [];//诱导屏图片，高点视频ID 集合
        $model_video_picture = new VideoPicture();
        $all_list = $model_video_picture->getAllList();
        $vp_ids = array();
        foreach ($all_list as $v)
        {
            $vp_id = $v->vp_id;
            $lng = $v->longitude;
            $lat = $v->latitude;
            $in_area = $this->pointInArea($lng, $lat, json_decode($GIs));
            if($in_area)
                $vp_ids[] = $vp_id;
        }
        $model_region = new Region();
        DB::beginTransaction();
        $region_data = $model_region->addData($area_name,  $explain, $GIs, $user_id);
        if($region_data['code'] != 20000) return response()->json( $region_data);
        $model_vr = new VpRegion();
        $vp_data = ['code'=>20000,'msg'=>'新增区域成功, 区域内无高点视频和诱导屏', 'data'=>[]];
        foreach ($vp_ids as $v)
        {
            $vp_data = $model_vr->addData($region_data['data']['region_id'], $v);
        }
        DB::commit();
        return response()->json($vp_data);
    }

    /**
     * 高点视频区域---编辑数据
     * @param Request $request
     * @return mixed
     */
    public function editArea(Request $request)
    {
        $input = $request->all();
        $region_id = isset($input['region_id']) ? $input['region_id'] : ''; //区域ID
        $area_name = isset($input['area_name']) ? $input['area_name'] : ''; //区域名字
        $explain = isset($input['explain']) ? $input['explain'] : ''; //区域说明
        $GIs = isset($input['GIs']) ? $input['GIs'] : 0;//地理信息(json格式经纬度，顶点坐标集合)
        $user_id = isset($input['user_id']) ? $input['user_id'] : '';  //操作人ID
        //$vp_ids = isset($input['vp_ids']) ? $input['vp_ids'] : [];//诱导屏图片，高点视频ID 集合
        $model_video_picture = new VideoPicture();
        $all_list = $model_video_picture->getAllList();
        $vp_ids = array();
        foreach ($all_list as $v)
        {
            $vp_id = $v->vp_id;
            $lng = $v->longitude;
            $lat = $v->latitude;
            $in_area = $this->pointInArea($lng, $lat, json_decode($GIs));
            if($in_area)
                $vp_ids[] = $vp_id;
        }
        DB::beginTransaction();
        $model_region = new Region();
        $model_vr = new VpRegion();
        $vp_data = ['code'=>20000,'msg'=>'编辑区域成功, 区域内无新增高点视频和诱导屏', 'data'=>[]];
        $region_data = $model_region->editData($region_id, $area_name,  $explain, $GIs, $user_id);
        if($region_data['code'] != 20000) return response()->json( $region_data);
        $del_data = $model_vr->delIds([$region_id]);
        if($del_data['code'] != 20000) return response()->json( $del_data);
        foreach ($vp_ids as $v)
        {
            $vp_data = $model_vr->addData($region_id, $v);
        }
        if($vp_data['code'] == 20000)
            $vp_data['msg'] = '修改成功';
        else
            $vp_data['msg'] = '修改失败';
        DB::commit();
        return response()->json($vp_data);
    }

    /**
     * 高点视频区域---批量删除数据
     * @param Request $request
     * @return mixed
     */
    public function batchDelArea(Request $request)
    {
        $input = $request->all();
        $region_ids = isset($input['region_ids']) ? $input['region_ids'] : []; //数据ID集合
        $model_region = new Region();
        $return_data = $model_region->delIds($region_ids);
        return response()->json($return_data);
    }

    /**
     * @param $lng
     * @param $lat
     * @param $pointList 区域地点多边形点的顺序需根据顺时针或逆时针，不能乱
     * @return bool|int
     */
    public function pointInArea($lng, $lat, $pointList)
    {
        $iSum = 0;
        $iCount = count($pointList);
        if ($iCount < 3) {
            return false;
        }
        foreach ($pointList as $key => $row) {
            $pLon1 = $row->lng;
            $pLat1 = $row->lat;
            if ($key === $iCount - 1) {
                $pLon2 = $pointList[0]->lng;
                $pLat2 = $pointList[0]->lat;
            } else {
                $pLon2 = $pointList[$key + 1]->lng;
                $pLat2 = $pointList[$key + 1]->lat;
            }
            if ((($lat >= $pLat1) && ($lat < $pLat2)) || (($lat >= $pLat2) && ($lat < $pLat1))) {
                if (abs($pLat1 - $pLat2) > 0) {
                    $pLon = $pLon1 - (($pLon1 - $pLon2) * ($pLat1 - $lat)) / ($pLat1 - $pLat2);
                    if ($pLon < $lng) {
                        $iSum += 1;
                    }
                }
            }
        }
        if ($iSum % 2 != 0) {
            return true;
        } else {
            return false;
        }
    }

}

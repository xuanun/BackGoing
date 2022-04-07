<?php


namespace App\Http\Controllers\audit;


use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ShootHandy;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * 随手拍---列表
     * @param Request $request
     * @return mixed
     */
    public function shootHandyList(Request $request)
    {
        $input = $request->all();
        //$examine_type = isset($input['examine_type']) ? $input['examine_type'] : ''; //审核状态
        $car_number = isset($input['car_number']) ? $input['car_number'] : ''; //车牌
        $type = isset($input['type']) ? $input['type'] : '';//违章类型
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : '';//审核状态
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//昵称
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $examine_start_time = isset($input['examine_start_time']) ? $input['examine_start_time'] : ''; //审核开始时间
        $examine_end_time = isset($input['examine_end_time']) ? $input['examine_end_time'] : ''; //审核结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_shoot_handy = new ShootHandy();
        $shoot_data = $model_shoot_handy->getDataList($examine_type, $car_number, $type, $user_name, $phone, $start_time, $end_time, $examine_start_time,  $examine_end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$shoot_data];
        return response()->json($return_data);
    }

    /**
     * 随手拍---审核
     * @param Request $request
     * @return mixed
     */
    public function editShootHandy(Request $request)
    {
        $input = $request->all();
        $shoot_ids = isset($input['shoot_ids']) ? $input['shoot_ids'] : []; //随手拍ID集合
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : ''; //审核状态
        $examine_web_uid = isset($input['examine_web_uid']) ? $input['examine_web_uid'] : ''; //审核人
        $examine_explain = isset($input['examine_explain']) ? $input['examine_explain'] : ''; //审核说明
        if(empty($shoot_ids)) return response()->json(['code'=>40000,'msg'=>'ID列表不能为空', 'data'=>[]]);
//        //判断审核不通过时 审核说明是否为空
//        if($examine_type == 0){
//            if(empty($examine_explain)) return response()->json(['code'=>40000,'msg'=>'操作失败，审核说明不能为空', 'data'=>[]]);
//        }
        $model_shoot_handy = new ShootHandy();
        $exist_type = $model_shoot_handy->existExamineType($shoot_ids, 2);
        if($exist_type) return response()->json(['code'=>40000,'msg'=>'所选列表中审核状态不全为未审核', 'data'=>[]]);
        $return_data = $model_shoot_handy->editData($shoot_ids, $examine_type, $examine_web_uid, $examine_explain);
        return response()->json($return_data);
    }

    /**
     * 路况审核---列表
     * @param Request $request
     * @return mixed
     */
    public function roadStatusList(Request $request)
    {
        $input = $request->all();
        $address = isset($input['address']) ? $input['address'] : ''; //路段名
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : '';//审核状态
        $release_type = isset($input['release_type']) ? $input['release_type'] : '';//发布状态
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//昵称
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : ''; //上报开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : ''; //上报结束时间
        $e_start_time = isset($input['e_start_time']) ? $input['e_start_time'] : ''; //审核开始时间
        $e_end_time = isset($input['e_end_time']) ? $input['e_end_time'] : ''; //审核结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_report = new Report();
        $type = 2 ; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
        $update_data = $model_report->updateStatus();
        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $report_data = $model_report->getDataList($address, $examine_type, $release_type, $user_name, $phone, $c_start_time, $c_end_time, $e_start_time, $e_end_time, $type,$page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$report_data];
        return response()->json($return_data);
    }

    /**
     * 路况---审核
     * @param Request $request
     * @return mixed
     */
    public function editRoadExamine(Request $request)
    {
        $input = $request->all();
        $report_id = isset($input['report_id']) ? $input['report_id'] : ''; //路况ID
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : ''; //审核状态  1：审核通过 0:审核未通过  5：审核通过并发布
        $examine_web_uid = isset($input['examine_web_uid']) ? $input['examine_web_uid'] : ''; //审核人ID
        $examine_explain = isset($input['examine_explain']) ? $input['examine_explain'] : ''; //审核说明
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_content = isset($input['release_content']) ? $input['release_content'] : ''; //发布内容
        $release_start_time = isset($input['release_start_time']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($report_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $type = 2 ;
        $model_report = new Report();
        if($examine_type == 1 || $examine_type == 0){
            $return_data = $model_report->examineData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain);
        }
        elseif($examine_type == 5)
        {
            $examine_type = 1; //审核状态  1：审核通过 0:审核未通过 2:审核中 5：审核通过并发布
            $release_type = 1;//发布状态 1:发布中 2:待发布 3:已发布
            $return_data = $model_report->examineAndReleaseData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain,$release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time);
        }else{
            $return_data = ['code'=>60000,'msg'=>'参数不正确', 'data'=>[]];
        }

        return response()->json($return_data);
    }

    /**
     * 路况---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function editRoadRelease(Request $request)
    {
        $input = $request->all();
        $report_id = isset($input['report_id']) ? $input['report_id'] : ''; //路况ID
        $release_type = isset($input['release_type']) ? $input['release_type'] : 0; //发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_content = isset($input['release_content']) ? $input['release_content'] : ''; //发布内容
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($report_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $model_report = new Report();
        $type = 2; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
        $return_data = $model_report->releaseData($report_id, $type,$release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time);
        return response()->json($return_data);
    }

    /**
     * 交通事故---列表
     * @param Request $request
     * @return mixed
     */
    public function accidentList(Request $request)
    {
        $input = $request->all();
        $explain = isset($input['explain']) ? $input['explain'] : ''; //路段名
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : '';//审核状态
        $release_type = isset($input['release_type']) ? $input['release_type'] : '';//发布状态
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//昵称
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : ''; //上报开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : ''; //上报结束时间
        $e_start_time = isset($input['e_start_time']) ? $input['e_start_time'] : ''; //审核开始时间
        $e_end_time = isset($input['e_end_time']) ? $input['e_end_time'] : ''; //审核结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_report = new Report();
        $type = 1  ; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
        $update_data = $model_report->updateStatus();
        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $report_data = $model_report->getComList($explain, $examine_type, $release_type, $user_name, $phone, $c_start_time, $c_end_time, $e_start_time, $e_end_time, $type, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$report_data];
        return response()->json($return_data);
    }
    /**
     * 交通事故---审核
     * @param Request $request
     * @return mixed
     */
    public function accidentExamine(Request $request)
    {
        $input = $request->all();
        $report_id = isset($input['report_id']) ? $input['report_id'] : ''; //路况ID
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : ''; //审核状态  1：审核通过 0:审核未通过  5：审核通过并发布
        $examine_web_uid = isset($input['examine_web_uid']) ? $input['examine_web_uid'] : ''; //审核人ID
        $examine_explain = isset($input['examine_explain']) ? $input['examine_explain'] : ''; //审核说明
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_content = isset($input['release_content']) ? $input['release_content'] : ''; //发布内容
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($report_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $type = 1 ;
        $model_report = new Report();
        if($examine_type == 1 || $examine_type == 0){
            $return_data = $model_report->examineData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain);
        }
        elseif($examine_type == 5)
        {
            $examine_type = 1; //审核状态  1：审核通过 0:审核未通过 2:审核中 5：审核通过并发布
            $release_type = 1;//发布状态 1:发布中 2:待发布 3:已发布
            $return_data = $model_report->examineAndReleaseData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain,$release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time);
        }else{
            $return_data = ['code'=>60000,'msg'=>'参数不正确', 'data'=>[]];
        }

        return response()->json($return_data);
    }

    /**
     * 交通事故---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function accidentRelease(Request $request)
    {
        $input = $request->all();
        $report_id = isset($input['report_id']) ? $input['report_id'] : ''; //路况ID
        $release_type = isset($input['release_type']) ? $input['release_type'] : 0; ////发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_content = isset($input['release_content']) ? $input['release_content'] : ''; //发布内容
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($report_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $type = 1 ;
        $model_report = new Report();
        $return_data = $model_report->releaseData($report_id, $type, $release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time);
        return response()->json($return_data);
    }

    /**
     * 通讯故障---列表
     * @param Request $request
     * @return mixed
     */
    public function communicateList(Request $request)
    {
        $input = $request->all();
        $explain = isset($input['explain']) ? $input['explain'] : ''; //路段名
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : '';//审核状态
        $release_type = isset($input['release_type']) ? $input['release_type'] : '';//发布状态
        $user_name = isset($input['user_name']) ? $input['user_name'] : '';//昵称
        $phone = isset($input['phone']) ? $input['phone'] : '';//手机号
        $c_start_time = isset($input['c_start_time']) ? $input['c_start_time'] : ''; //上报开始时间
        $c_end_time = isset($input['c_end_time']) ? $input['c_end_time'] : ''; //上报结束时间
        $e_start_time = isset($input['e_start_time']) ? $input['e_start_time'] : ''; //审核开始时间
        $e_end_time = isset($input['e_end_time']) ? $input['e_end_time'] : ''; //审核结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;
        $model_report = new Report();
        $type = 3  ; //上报类型 1:交通事故 2:交通拥堵 3:通讯故障
        $update_data = $model_report->updateStatus();
        if($update_data['code'] != 20000 ) return response()->json(['code'=>40000,'msg'=>'更新数据失败', 'data'=>[]]);
        $report_data = $model_report->getComList($explain, $examine_type, $release_type, $user_name, $phone, $c_start_time, $c_end_time, $e_start_time, $e_end_time, $type, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$report_data];
        return response()->json($return_data);
    }

    /**
     * 通讯故障---审核
     * @param Request $request
     * @return mixed
     */
    public function communicateExamine(Request $request)
    {
        $input = $request->all();
        $report_id = isset($input['report_id']) ? $input['report_id'] : ''; //路况ID
        $examine_type = isset($input['examine_type']) ? $input['examine_type'] : ''; //审核状态  1：审核通过 0:审核未通过  5：审核通过并发布
        $examine_web_uid = isset($input['examine_web_uid']) ? $input['examine_web_uid'] : ''; //审核人ID
        $examine_explain = isset($input['examine_explain']) ? $input['examine_explain'] : ''; //审核说明
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_content = isset($input['release_content']) ? $input['release_content'] : ''; //发布内容
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($report_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $type = 3 ;
        $model_report = new Report();
        if($examine_type == 1 || $examine_type == 0){
            $return_data = $model_report->examineData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain);
        }
        elseif($examine_type == 5)
        {
            $examine_type = 1; //审核状态  1：审核通过 0:审核未通过 2:审核中 5：审核通过并发布
            $release_type = 1;//发布状态 1:发布中 2:待发布 3:已发布
            $return_data = $model_report->examineAndReleaseData($report_id, $type, $examine_type, $examine_web_uid, $examine_explain,$release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time);
        }else{
            $return_data = ['code'=>60000,'msg'=>'参数不正确', 'data'=>[]];
        }

        return response()->json($return_data);
    }

    /**
     * 通讯故障---发布 取消发布
     * @param Request $request
     * @return mixed
     */
    public function communicateRelease(Request $request)
    {
        $input = $request->all();
        $report_id = isset($input['report_id']) ? $input['report_id'] : ''; //路况ID
        $release_type = isset($input['release_type']) ? $input['release_type'] : 0; //发布状态 1:发布中 2:待发布 3:已发布
        $release_web_uid = isset($input['release_web_uid']) ? $input['release_web_uid'] : ''; //发布人ID
        $release_content = isset($input['release_content']) ? $input['release_content'] : ''; //发布内容
        $release_start_time = isset($input['release_web_uid']) ? $input['release_start_time'] : ''; //发布开始时间
        $release_end_time = isset($input['release_end_time']) ? $input['release_end_time'] : ''; //发布结束时间
        if(empty($report_id)) return response()->json(['code'=>40000,'msg'=>'ID不能为空', 'data'=>[]]);
        $type = 3 ;
        $model_report = new Report();
        $return_data = $model_report->releaseData($report_id, $type, $release_type, $release_web_uid, $release_content, $release_start_time, $release_end_time);
        return response()->json($return_data);
    }
}

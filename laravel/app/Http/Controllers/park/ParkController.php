<?php


namespace App\Http\Controllers\park;

use App\Http\Controllers\Controller;
use App\Models\Park;
use Illuminate\Http\Request;

class ParkController extends Controller
{
    /**
     * 停车场---列表
     * @param Request $request
     * @return mixed
     */
    public function parkList(Request $request)
    {
        $input = $request->all();
        $park_code = isset($input['park_code']) ? $input['park_name'] : ''; //停车场code
        $park_name = isset($input['park_name']) ? $input['park_name'] : ''; //停车场名称
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_park = new Park();
        $park_data = $model_park->getList($park_code, $park_name, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$park_data];
        return response()->json($return_data);
    }

    /**
     * 停车场---新增数据
     * @param Request $request
     * @return mixed
     */
    public function addPark(Request $request)
    {
        $input = $request->all();
        $park_code = isset($input['park_code']) ? $input['park_name'] : ''; //停车场code
        $park_name = isset($input['park_name']) ? $input['park_name'] : ''; //停车场名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $sum_berth = isset($input['sum_berth']) ? $input['sum_berth'] : 0;//总停泊数
        $surplus_berth = isset($input['surplus_berth']) ? $input['surplus_berth'] : 0;//剩余停泊数
        $status = isset($input['status']) ? $input['status'] : 0;//状态 1:正常 0:异常
        if(empty($park_code) || empty($park_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        if($surplus_berth > $sum_berth) return response()->json(['code'=>40000,'msg'=>'剩余停泊数不能大于总停泊数', 'data'=>[]]);
        $model_park = new Park();
        $park_data = $model_park->addData($park_code, $park_name, $longitude, $latitude, $sum_berth, $surplus_berth, $status);
        return response()->json($park_data);
    }

    /**
     * 停车场---编辑数据
     * @param Request $request
     * @return mixed
     */
    public function editPark(Request $request)
    {
        $input = $request->all();
        $id = isset($input['id']) ? $input['id'] : 0;//数据ID
        $park_code = isset($input['park_code']) ? $input['park_name'] : ''; //停车场code
        $park_name = isset($input['park_name']) ? $input['park_name'] : ''; //停车场名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $sum_berth = isset($input['sum_berth']) ? $input['sum_berth'] : 0;//总停泊数
        $surplus_berth = isset($input['surplus_berth']) ? $input['surplus_berth'] : 0;//剩余停泊数
        $status = isset($input['status']) ? $input['status'] : 0;//状态 1:正常 0:异常
        if(empty($park_code) || empty($park_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        if($surplus_berth > $sum_berth) return response()->json(['code'=>40000,'msg'=>'剩余停泊数不能大于总停泊数', 'data'=>[]]);
        $model_park = new Park();
        $park_data =  $model_park->editData($id, $park_code, $park_name, $longitude, $latitude, $sum_berth, $surplus_berth, $status);
        return response()->json($park_data);
    }

    /**
     * 停车场---批量删除数据
     * @param Request $request
     * @return mixed
     */
    public function batchDelPark(Request $request)
    {
        $input = $request->all();
        $ids = isset($input['ids']) ? $input['ids'] : []; //数据ID集合
        $model_park = new Park();
        $return_data = $model_park->delIds($ids);
        return response()->json($return_data);
    }
}

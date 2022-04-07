<?php


namespace App\Http\Controllers\park;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use Illuminate\Http\Request;

class BicycleController extends Controller
{
    /**
     * 自行车站点---列表
     * @param Request $request
     * @return mixed
     */
    public function bikeList(Request $request)
    {
        $input = $request->all();
        $site_code = isset($input['site_code']) ? $input['site_code'] : ''; //自行车站点code
        $site_name = isset($input['site_name']) ? $input['site_name'] : ''; //自行车站点名称
        $start_time = isset($input['start_time']) ? $input['start_time'] : ''; //开始时间
        $end_time = isset($input['end_time']) ? $input['end_time'] : ''; //结束时间
        $page_size = isset($input['page_size']) ? $input['page_size'] : 1;
        $page =  isset($input['page']) ? $input['page'] : 1;

        $model_bike = new Bicycle();
        $bike_data = $model_bike->getList($site_code, $site_name, $start_time, $end_time, $page_size);
        $return_data = ['code'=>20000,'msg'=>'', 'data'=>$bike_data];
        return response()->json($return_data);
    }

    /**
     * 自行车站点---新增数据
     * @param Request $request
     * @return mixed
     */
    public function addBike(Request $request)
    {
        $input = $request->all();
        $site_code = isset($input['site_code']) ? $input['site_code'] : ''; //自行车站点code
        $site_name = isset($input['site_name']) ? $input['site_name'] : ''; //自行车站点名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $sum_bike_vehicle = isset($input['sum_bike_vehicle']) ? $input['sum_bike_vehicle'] : 0;//自行车总车辆数
        $surplus_bike_vehicle = isset($input['surplus_bike_vehicle']) ? $input['surplus_bike_vehicle'] : 0;//自行车站点剩余可借数
        $status = isset($input['status']) ? $input['status'] : 0;//状态 1:正常 0:异常
        if(empty($site_code) || empty($site_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        if($surplus_bike_vehicle > $sum_bike_vehicle) return response()->json(['code'=>40000,'msg'=>'剩余停泊数不能大于总停泊数', 'data'=>[]]);
        $model_bike = new Bicycle();
        $bike_data = $model_bike->addData($site_code, $site_name, $longitude, $latitude, $sum_bike_vehicle, $surplus_bike_vehicle, $status);
        return response()->json($bike_data);
    }

    /**
     * 自行车站点---编辑数据
     * @param Request $request
     * @return mixed
     */
    public function editBike(Request $request)
    {
        $input = $request->all();
        $id = isset($input['id']) ? $input['id'] : 0;//数据ID
        $site_code = isset($input['site_code']) ? $input['site_code'] : ''; //自行车站点code
        $site_name = isset($input['site_name']) ? $input['site_name'] : ''; //自行车站点名称
        $longitude = isset($input['longitude']) ? $input['longitude'] : 0;//经度
        $latitude = isset($input['latitude']) ? $input['latitude'] : 0;//纬度
        $sum_bike_vehicle = isset($input['sum_bike_vehicle']) ? $input['sum_bike_vehicle'] : 0;//自行车总车辆数
        $surplus_bike_vehicle = isset($input['surplus_bike_vehicle']) ? $input['surplus_bike_vehicle'] : 0;//自行车站点剩余可借数
        $status = isset($input['status']) ? $input['status'] : 0;//状态 1:正常 0:异常
        if(empty($site_code) || empty($site_name)){
            return response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        }
        if($surplus_bike_vehicle > $sum_bike_vehicle) return response()->json(['code'=>40000,'msg'=>'剩余停泊数不能大于总停泊数', 'data'=>[]]);
        $model_bike = new Bicycle();
        $bike_data =  $model_bike->editData($id, $site_code, $site_name, $longitude, $latitude, $sum_bike_vehicle, $surplus_bike_vehicle, $status);
        return response()->json($bike_data);
    }

    /**
     * 自行车站点---批量删除数据
     * @param Request $request
     * @return mixed
     */
    public function batchDelBike(Request $request)
    {
        $input = $request->all();
        $ids = isset($input['ids']) ? $input['ids'] : []; //数据ID集合
        $model_bike = new Bicycle();
        $return_data = $model_bike->delIds($ids);
        return response()->json($return_data);
    }
}

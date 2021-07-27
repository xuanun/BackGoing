<?php


namespace App\Http\Controllers\organization;


use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;


class OrganizationController extends Controller
{
    /**
     * 组织结构全部数据
     * @param Request $request
     * @return mixed
     */
    public function allData(Request $request)
    {
        $input = $request->all();
        $org_model = new Organization();
        //查询 A级菜单
        $p_id = 0;
        $zero_data = $org_model->getListByPid($p_id);
        $array = array();
        foreach ($zero_data as $v)
        {
            //查询 B级菜单
            $one_data = $org_model->getListByPid($v->id);
            $one_array = array();
            foreach ($one_data as $x)
            {
                //查询 C级菜单
                $two_data = $org_model->getListByPid($x->id);
                $x->list = $two_data;
                $one_array[] = $x;
            }
            $v->list = $one_array;
            $array[] = $v;
        }
        return  response()->json(['code'=>20000,'msg'=>'', 'data'=>$array]);
    }

    /**
     * 查询数据
     * @param Request $request
     * @return mixed
     */
    public function getOrg(Request $request)
    {
        $input = $request->all();
        $id = isset($input['id']) ? $input['id'] : '';
        $org_name = isset($input['org_name']) ? $input['org_name'] : '';
        $start_time = isset($input['start_time']) ? $input['start_time'] : '';
        $end_time = isset($input['end_time']) ? $input['end_time'] : '';
        $page_size = isset($input['page_size']) ? $input['page_size'] : 10;
        $page = isset($input['page']) ? $input['page'] : 1;
        $org_model = new Organization();
        //查询数据
        $zero_data = $org_model->getDataList($id, $org_name, $start_time, $end_time, $page_size);
        return  response()->json(['code'=>20000,'msg'=>'', 'data'=>$zero_data]);
    }

    /**
     * 添加数据
     * @param Request $request
     * @return mixed
     */
    public function addOrg(Request $request)
    {
        $input = $request->all();
        $p_id = isset($input['p_id']) ? $input['p_id'] : '';
        $name = isset($input['name']) ? $input['name'] : '';
        $functionary = isset($input['functionary']) ? $input['functionary'] : '';
        $sort = isset($input['sort']) ? $input['sort'] : '';
        $phone = isset($input['phone']) ? $input['phone'] : '';
        $data_status = isset($input['data_status']) ? $input['data_status'] : 0;
        if($p_id === '' || empty($name) || empty($functionary) ||  empty($phone))
            return  response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        $org_model = new Organization();
        $zero_data = $org_model->addData($p_id, $name, $functionary, $sort, $phone, $data_status);
        return  response()->json($zero_data);
    }

    /**
     * 修改数据
     * @param Request $request
     * @return mixed
     */
    public function editOrg(Request $request)
    {
        $input = $request->all();
        $id = isset($input['id']) ? $input['id'] : '';
        $p_id = isset($input['p_id']) ? $input['p_id'] : '';
        $name = isset($input['name']) ? $input['name'] : '';
        $functionary = isset($input['functionary']) ? $input['functionary'] : '';
        $sort = isset($input['sort']) ? $input['sort'] : '';
        $phone = isset($input['phone']) ? $input['phone'] : '';
        $data_status = isset($input['data_status']) ? $input['data_status'] : 0;
        if($p_id === '' || empty($id) || empty($name) || empty($functionary) ||  empty($phone))
            return  response()->json(['code'=>60000,'msg'=>'缺少参数', 'data'=>[]]);
        $org_model = new Organization();
        $zero_data = $org_model->editData($id, $p_id, $name, $functionary, $sort, $phone, $data_status);
        return  response()->json($zero_data);
    }

    /**
     * 批量删除组织机构
     * @param Request $request
     * @return mixed
     */
    public function batchDelOrg(Request $request)
    {
        //接收并校验参数
        $input = $request->all();
        $ids = isset($input['ids']) ? $input['ids'] : [];
        if(empty($ids)) return  response()->json(['code'=>60000,'msg'=>'参数错误', 'data'=>[]]);
        $org_model = new Organization();
        $data = $org_model->delRole($ids);
        return  response()->json($data);
    }
}

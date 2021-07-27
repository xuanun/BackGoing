<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//用户路由 登录及退出
$router->group(['prefix' => 'account'], function () use ($router) {
    $router->post("/login", 'account\AccountController@login');//用户登录
    $router->group(['middleware'=>['authToken']],function () use ($router) {
        $router->post("/logout", 'account\AccountController@logout');//用户退出登录
        $router->post("/edit_user_info", 'account\AccountController@editUserInfo');//用户修改资料
        $router->post("/edit_password", 'account\AccountController@editPassword');//用户修改密码
        //公共接口
        $router->post("/get_all_dept", 'account\AccountController@getAllDept');//获取所有部门
        $router->post("/get_all_violation", 'account\AccountController@getAllViolation');//获取所有违章类型
        $router->post("/get_all_examine", 'account\AccountController@getAllExamine');//获取所有审核状态
        $router->post("/msg_amount", 'account\AccountController@MessageAmount');//获取消息条数
    });
});
//用户路由  角色管理
$router->group(['prefix' => 'role'], function () use ($router) {
    $router->post("/all_roles", 'power\DoveRolesController@roleList');//所有角色列表
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/role_per_list", 'power\DoveRolesController@rolePerMenu'); //权限菜单列表
        $router->post("/del_role", 'power\DoveRolesController@batchDelRole'); //删除角色
        $router->post("/add_role", 'power\DoveRolesController@addRole'); //新增角色
        $router->post("/edit_role", 'power\DoveRolesController@editRole'); //编辑角色
        $router->post("/add_per", 'power\DoveRolesController@rolePermissions'); //角色分配权限
        $router->post("/role_per", 'power\DoveRolesController@rolePermissionsMenu'); //角色权限菜单
    });
});

//用户路由 组织机构
$router->group(['prefix' => 'org'], function () use ($router) {
    $router->post("/all_data", 'organization\OrganizationController@allData');// 组织机构全部三级数据
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/get_data", 'organization\OrganizationController@getOrg');// 查询数据列表
        $router->post("/add_org", 'organization\OrganizationController@addOrg');// 添加数据
        $router->post("/edit_org", 'organization\OrganizationController@editOrg');// 修改数据
        $router->post("/del_org", 'organization\OrganizationController@batchDelOrg');// 删除数据
    });
});

//用户路由 用户管理
$router->group(['prefix' => 'index'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/web_user_list", 'index\IndexController@webUserList');// WEB用户列表
        $router->post("/add_web_user", 'index\IndexController@addWebUser');// WEB用户添加
        $router->post("/edit_web_user", 'index\IndexController@editWebUser');// WEB用户修改
        $router->post("/del_web_user", 'index\IndexController@batchDelWebUser');// WEB用户删除
        $router->post("/app_user_list", 'index\IndexController@getAppUserList');// APP用户列表
    });
});

//用户路由 实名认证
$router->group(['prefix' => 'aut'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/app_user_list", 'account\AccountController@getAppUserList');//用户认证列表
        $router->post("/edit_ident", 'account\AccountController@editIdent');//用户认证审核
        $router->post("/batch_ident", 'account\AccountController@batchEditIdent');//批量认证审核
    });
});

//用户路由 审核
$router->group(['prefix' => 'audit'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/shoot_handy_list", 'audit\AuditController@shootHandyList');//随手拍查看
        $router->post("/edit_shoot_handy", 'audit\AuditController@editShootHandy');//随手拍审核
        $router->post("/road_status_list", 'audit\AuditController@roadStatusList');//路况查看
        $router->post("/edit_road_examine", 'audit\AuditController@editRoadExamine');//路况审核
        $router->post("/edit_road_release", 'audit\AuditController@editRoadRelease');//路况---发布 取消发布
        $router->post("/accident_list", 'audit\AuditController@accidentList');//交通事故---列表
        $router->post("/accident_examine", 'audit\AuditController@accidentExamine');//交通事故---审核
        $router->post("/accident_release", 'audit\AuditController@accidentRelease');//交通事故---发布 取消发布
        $router->post("/communicate_list", 'audit\AuditController@communicateList');//通讯故障---列表
        $router->post("/communicate_examine", 'audit\AuditController@communicateExamine');//通讯故障---审核
        $router->post("/communicate_release", 'audit\AuditController@communicateRelease');//通讯故障---发布 取消发布
    });
});

//用户路由 发布
$router->group(['prefix' => 'issue'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/traffic_list", 'issue\IssueController@trafficList');//交通管制---列表
        $router->post("/add_traffic", 'issue\IssueController@addTraffic');//交通管制---新增
        $router->post("/traffic_release", 'issue\IssueController@trafficRelease');//交通管制---发布
        $router->post("/jeeves_list", 'issue\IssueController@jeevesList');//占道施工---列表
        $router->post("/add_jeeves", 'issue\IssueController@addJeeves');//占道施工---新增
        $router->post("/jeeves_release", 'issue\IssueController@jeevesRelease');//占道施工---发布
        $router->post("/restrict_list", 'issue\IssueController@restrictList');//限行限号---列表
        $router->post("/add_restrict", 'issue\IssueController@addRestrict');//限行限号--新增
        $router->post("/restrict_release", 'issue\IssueController@restrictRelease');//限行限号---发布
        $router->post("/weather_list", 'issue\IssueController@weatherList');//气象信息---列表
        $router->post("/add_weather", 'issue\IssueController@addWeather');//气象信息---新增
        $router->post("/weather_release", 'issue\IssueController@weatherRelease');//气象信息---发布
        $router->post("/upload_image", 'issue\IssueController@uploadImg');//上传图片
    });
});

//用户路由 视频
$router->group(['prefix' => 'video'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/all_List", 'video\VideoController@allDataList');//诱导屏、视频 ---全部列表
        $router->post("/video_list", 'video\VideoController@videoList');//视频---列表
        $router->post("/add_video", 'video\VideoController@addVideo');//视频---新增数据
        $router->post("/edit_video", 'video\VideoController@editVideo');//视频---编辑数据
        $router->post("/batch_del_video", 'video\VideoController@batchDelVideo');//视频---批量删除数据
        $router->post("/picture_list", 'video\VideoController@pictureList');//诱导屏---列表
        $router->post("/add_picture", 'video\VideoController@addPicture');//诱导屏---新增数据
        $router->post("/edit_picture", 'video\VideoController@editPicture');//诱导屏---编辑数据
        $router->post("/batch_del_picture", 'video\VideoController@batchDelPicture');//诱导屏---批量删除数据
    });
});

//用户路由 停车场
$router->group(['prefix' => 'park'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/park_list", 'park\ParkController@parkList');//停车场---列表
        $router->post("/add_park", 'park\ParkController@addPark');//停车场---新增数据
        $router->post("/edit_park", 'park\ParkController@editPark');//停车场---编辑数据
        $router->post("/batch_del_park", 'park\ParkController@batchDelPark');//停车场---批量删除数据
    });
});

//用户路由 区域
$router->group(['prefix' => 'area'], function () use ($router) {
    $router->group(['middleware'=>['authToken', 'authPermissions']],function () use ($router) {
        $router->post("/area_list", 'video\VideoController@areaList');//高点视频区域---列表
        $router->post("/add_area", 'video\VideoController@addArea');//高点视频区域---新增数据
        $router->post("/edit_area", 'video\VideoController@editArea');//高点视频区域---编辑数据
        $router->post("/batch_del_area", 'video\VideoController@batchDelArea');//高点视频区域---批量删除数据
    });
});


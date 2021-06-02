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
        $router->post("/get_all_dept", 'account\AccountController@getAllDept');//获取所有部门
        $router->post("/edit_user_info", 'account\AccountController@editUserInfo');//用户修改资料
        $router->post("/edit_password", 'account\AccountController@editPassword');//用户修改密码
        $router->post("/app_user_list", 'account\AccountController@getAppUserList');//用户认证列表
    });
    $router->post("/test", 'index\IndexController@test');//测试接口
});
//用户路由  权限
$router->group(['prefix' => 'role'], function () use ($router) {
    $router->post("/all_roles", 'user\UserController@getAllRoles');//所有角色
    $router->post("/role_list", 'power\DoveRolesController@roleList'); //管理员角色列表
    $router->post("/role_per_list", 'power\DoveRolesController@rolePerMenu'); //权限菜单列表
    $router->post("/del_role", 'power\DoveRolesController@delRole'); //删除角色
    $router->post("/add_role", 'power\DoveRolesController@addRole'); //新增角色
    $router->post("/edit_role", 'power\DoveRolesController@editRole'); //编辑角色
    $router->post("/add_per", 'power\DoveRolesController@rolePermissions'); //角色分配权限
});


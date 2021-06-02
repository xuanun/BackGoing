<?php


namespace app\Http\Controllers\index;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    //测试接口
    public function test()
    {
        return response()->json(['code'=>20000,'msg'=>env('VERIFY_TOKEN').'****这是一个测试接口',  'data'=>[]]);
    }


}

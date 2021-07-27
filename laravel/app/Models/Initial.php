<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Initial  extends Model
{
    protected $table = "easy_web_initial";
    const INVALID = 0;
    const NORMAL = 1;
    /**
     * 查询初始密码
     * @return mixed
     */
    public function getInitial()
    {
        $results = DB::table($this->table)
            ->select(DB::raw('initial_password'))
            ->where('data_status', self::NORMAL)
            ->first();
        return $results->initial_password;
    }

}

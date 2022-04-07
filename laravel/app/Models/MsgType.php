<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MsgType extends Model
{
    protected $table = "easy_msg_type";

    /**
     * 获取全部数据
     * @return mixed
     */
    public function getAll()
    {
        return $result = DB::table($this->table)
            ->select(DB::raw('id, p_id, type_name'))
            ->get();
    }

    /**
     * 根据ID获取数据
     * @param $id
     * @return mixed
     */
    public function getTypeName($id)
    {
        $result = DB::table($this->table)
            ->select(DB::raw('type_name'))
            ->where('id', $id)
            ->first();
        return empty($result->type_name) ? '' : $result->type_name;
    }
}

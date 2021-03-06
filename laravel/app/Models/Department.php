<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    protected $table = "easy_web_department";
    /**
     * 获取全部部门
     * @return mixed
     */
    public function getAll()
    {
        return $result = DB::table($this->table)
            ->select(DB::raw('id, dept_name, intro'))
            ->get();
    }
    /**
     * 通过ID获取部门
     * @param $id
     * @return mixed
     */
    public function getInfo($id)
    {
        return $result = DB::table($this->table)
            ->select(DB::raw('id, dept_name, intro'))
            ->where('id', $id)
            ->first();
    }
}

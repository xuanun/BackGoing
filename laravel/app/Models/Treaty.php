<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Treaty extends Model
{
    protected $table = "easy_app_treaty";
    /**
     * 查看协议列表
     * @return mixed
     */
    public function getList()
    {
        return $results =  DB::table($this->table)
            ->select(DB::raw('treaty_id, as_name, content'))
            ->orderBy('treaty_id','ASC')
            ->get();
    }

    /**
     * @param $treaty_id
     * @param $content
     * 修改协议内容
     * @return mixed
     */
    public function editContent($treaty_id, $content)
    {
        try{
            $UpdateArray = [
                'content' => $content,
                'update_time' => time(),
            ];
            DB::table($this->table)
                ->where('treaty_id', $treaty_id)
                ->update($UpdateArray);
            $return = ['code'=>20000,'msg'=>'编辑成功', 'data'=>[]];
        }catch(\Exception $e){
            DB::rollBack();
            $return = ['code'=>40000,'msg'=>'编辑失败', 'data'=>[$e->getMessage()]];
        }
        return $return;
    }
}

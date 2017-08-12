<?php
namespace app\index\controller;

use app\index\model\Hotel as hotelModel;
use think\Request;

class Hotel extends BaseController
{
    public function getAllName(Request $request)
    {
        $addrId = $request->post('id');
        $data = hotelModel::where(['addr' => $addrId])->select();
        if ($data) {
            foreach ($data as $k => $v) {
                $return[$k]['id'] = $v->id;
                $return[$k]['value'] = $v->name;
            }
            return json($return);
        }
        return json('查询失败');

    }

}

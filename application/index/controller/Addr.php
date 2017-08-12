<?php
namespace app\index\controller;
use app\index\model\Addr as addrModel;
use think\Request;

class Addr extends BaseController
{
    public function getAllName(Request $request)
    {
        $countryId = $request->post('id');
        $data = addrModel::where(['country'=>$countryId])->select();
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

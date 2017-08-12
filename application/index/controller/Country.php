<?php
namespace app\index\controller;
use app\index\model\Country as CountryModel;

class Country extends BaseController
{
    public function getAllName()
    {
        $data =  CountryModel::all();
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

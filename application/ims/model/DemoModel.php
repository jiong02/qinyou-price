<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-05-01
 * Time: 22:56
 */

namespace app\ims\model;


class DemoModel extends BaseModel
{
    protected $table = 'ims_vehicle';
    protected $connection = 'ims_new';
    protected $resultSetType = 'collection';
    protected $append = ['waste_hour'];
    protected function getWasteHourAttr($value,$data)
    {
        if(!empty($data['transfer_take_time'])){
            return explode(',',$data['transfer_take_time'])[0];
        }
        return '';
    }

    public function getModelName()
    {
        return $this->name;
    }

}
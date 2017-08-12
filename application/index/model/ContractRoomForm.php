<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-10
 * Time: 20:48
 */

namespace app\index\model;


class ContractRoomForm extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'contract_room_form';

    protected function getMealNewAttr($value,$data)
    {
        $return['breakfast'] = false;
        $return['lunch'] = false;
        $return['afternoon_tea'] = false;
        $return['dinner'] = false;
        if ($data['meal'] != '0'){
            $mealArr = $data['meal'];
            foreach (json_decode($mealArr) as $items) {
                switch (strtoupper($items)) {
                    case 'B':
                        $return['breakfast'] = true;
                        break;
                    case 'L':
                        $return['lunch'] = true;
                        break;
                    case 'AFT':
                        $return['afternoon_tea'] = true;
                        break;
                    case 'D':
                        $return['dinner'] = true;
                        break;
                }
            }
        }
        return $return;
    }

    protected function getActIdAttr($value,$data)
    {
        $act = $data['act'] == '0' ? [] : json_decode($data['act'],true);
        $idArray = [];
        foreach ($act as $index => $item) {
            $idArray[] = $item['id'];
        }
        return $idArray;
    }

    protected function getFacIdAttr($value,$data)
    {
        $fac = $data['fac'] == '0' ? [] : json_decode($data['fac'],true);
        $idArray = [];
        foreach ($fac as $index => $item) {
            $idArray[] = $item['id'];
        }
        return $idArray;
    }
}
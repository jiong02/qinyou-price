<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-25
 * Time: 15:49
 */

namespace app\index\model;


class Contract extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'contract';



    //获得淡旺季信息
    public function getConInfo($hotelId)
    {
        $hotelInfo = array();

        $selfModel = new self();
        $hotelInfo = $selfModel
            ->field('hotel_id,con_id,type,name,dates,color')
            ->where("hotel_id = $hotelId")
            ->whereNotNull('name')
            ->join('contract_date','contract.id = contract_date.con_id')
            ->join('internal_management_system.ims_color_record','name = en_name')
            ->select();

        return json_decode(json_encode($hotelInfo),true);

    }






}
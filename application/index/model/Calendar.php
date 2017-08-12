<?php
/**
 * Created by PhpStorm.
 * User: zfx
 * Date: 2017/3/7
 * Time: 18:35
 */
namespace app\index\model;

class Calendar extends BaseModel
{
    protected $table = 'ims_calendar';

    //获得节日信息
    public function getCaleInfo($countId,$addressId,$year,$month)
    {
        $selfModel = new self();
        $caleInfo = array();
        $dateTime = $year . '-' . $month;

        $caleInfo = $selfModel
            ->field('ims_calendar.*,color')
            ->where("country_id = $countId AND address_id = $addressId AND is_delete = '否' AND start_time like '$dateTime%'")
            ->join('ims_color_record', 'type = ims_color_record.cn_name', 'left')
            ->select();
        $caleInfo = json_decode(json_encode($caleInfo), true);

        return $caleInfo;

    }

    //获得当天的节日信息
    public function getNowCaleINfo($countId,$addressId,$dateTime)
    {
        $selfModel = new self();
        $caleInfo = array();

        $caleInfo = $selfModel
            ->field('ims_calendar.*,color')
            ->where("country_id = $countId AND address_id = $addressId AND is_delete = '否' AND start_time = '$dateTime'")
            ->join('ims_color_record', 'type = ims_color_record.cn_name', 'left')
            ->select();

        $caleInfo = json_decode(json_encode($caleInfo), true);
/*
        return $selfModel
            ->field('ims_calendar.*,color')
            ->where("country_id = $countId AND address_id = $addressId AND is_delete = '否' AND start_time = '$dateTime'")
            ->join('ims_color_record', 'type = ims_color_record.cn_name', 'left')->buildSql();*/

        return $caleInfo;




    }






}














?>
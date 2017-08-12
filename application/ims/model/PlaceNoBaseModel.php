<?php
namespace app\ims\model;

class PlaceNoBaseModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_place';

    public static function getIslandCountByCountryId($countryId)
    {
        return self::where('country_id',$countryId)->count();
    }

    public function getBaseDataAttr($value,$data)
    {
        $return['name'] = $data['island_name'];
        $return['src'] = $data[''];
        $return['eng_name'] = $data[''];
        $return['amount'] = $data[''];
        $return['id'] = $data[''];
    }




}
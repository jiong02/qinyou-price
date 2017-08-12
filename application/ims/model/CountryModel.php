<?php
namespace app\ims\model;

class CountryModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_country';

    public function getBaseDataAttr($value,$data)
    {
        $return['id'] = $data['id'];
        $return['name'] = $data['country_name'];
        $return['eng_name'] = $data['country_ename'];
        $return['src'] = $data['national_flag'];
        $return['amount'] = PlaceModel::getIslandCountByCountryId($data['id']);
        return $return;
    }

    public function getAllDataAttr($value,$data)
    {
        $return['id'] = $data['id'];
        $return['name'] = $data['country_name'];
        $return['eng_name'] = $data['country_ename'];
        $return['src'] = $data['national_flag'];
        $return['continent'] = $data['continent'];
        $return['language'] = $data['official_language'];
        $return['desc'] = $data['country_description'];
        $return['amount'] = PlaceModel::getIslandCountByCountryId($data['id']);
        return $return;
    }
}
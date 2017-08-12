<?php
namespace app\ims\model;

class HotelModel extends BaseModel
{
    protected $table = 'ims_hotel';
    protected $connection = 'ims_new';

    public function exchange()
    {
        return $this->belongsTo('ExchangeModel','exchange_id');
    }

    public function country()
    {
        return $this->belongsTo('CountryModel','country_id');
    }

    public function place()
    {
        return $this->belongsTo('PlaceModel','place_id');
    }

    public function room()
    {
        return $this->hasMany('HotelRoomModel','hotel_id');
    }

    public function contract()
    {
        return $this->hasMany('ContractModel', 'hotel_id');
    }

    public function getFormatAgeRangeAttr($value,$data)
    {
        $adultAgeRange = explode(',',$data['adult_age_range']);
        $childAgeRange = explode(',',$data['child_age_range']);
        $infantAgeRange = explode(',',$data['infant_age_range']);
        $return['min_adult_age'] = isset($adultAgeRange[0]) ? $adultAgeRange[0] : 0;
        $return['max_adult_age'] = isset($adultAgeRange[1]) ? $adultAgeRange[1] : 0;
        $return['min_child_age'] = isset($childAgeRange[0]) ? $childAgeRange[0] : 0;
        $return['max_child_age'] = isset($childAgeRange[1]) ? $childAgeRange[1] : 0;
        $return['min_infant_age'] = isset($infantAgeRange[0]) ? $infantAgeRange[0] : 0;
        $return['max_infant_age'] = isset($infantAgeRange[1]) ? $infantAgeRange[1] : 0;
        $return['infant_age_unit'] = $data['infant_age_unit'];
        return $return;
    }

    public function getAgeRangeAttr($value,$data)
    {
        $return['infant_age_unit'] = $data['infant_age_unit'];
        $return['adult_age_range'] = $data['adult_age_range'];
        $return['child_age_range'] = $data['child_age_range'];
        $return['infant_age_range'] = $data['infant_age_range'];
        return $return;
    }
}
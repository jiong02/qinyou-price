<?php
namespace app\ims\model;

class HotelRoomModel extends BaseModel
{
    protected $table = 'ims_hotel_room';
    protected $connection = 'ims_new';

    public function hotel()
    {
        return $this->belongsTo('hotelModel','hotel_id');
    }
}
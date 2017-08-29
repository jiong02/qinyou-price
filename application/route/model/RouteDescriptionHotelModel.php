<?php
namespace app\route\model;

use think\Model;

class RouteDescriptionHotelModel extends Model
{
    public $table = 'ims_route.ims_route_description_hotel';

    public $rule = [
        'description_id|描述' => 'number|require',
        'route_hotel_id|线路酒店' => 'number|require',
        'hotel_id|酒店' => 'number|require',
        'hotel_name|酒店名称' => 'require',
        'hotel_breakfast|早餐' => 'require',
        'hotel_lunch|午餐' => 'require',
        'hotel_dinner|晚餐' => 'require',
        'image_show_status|图片状态' => 'require',

        ];
    public $baseHidden = ['create_time','modify_time'];

    public function __construct($data = [])
    {
        $this->hidden = array_merge($this->hidden, $this->baseHidden);
        parent::__construct($data);
    }

}


?>
<?php
namespace app\test\model;

class TemplateRouteModel extends BaseModel
{
    public $table = 'cheeru_template_route';

    public $rules = [
            'temp_id|模板' => 'number|require',
            'place_id|海岛' => 'number|require',
            'room_id|房间' => 'number|require',
            'place_name|海岛名称' => 'reuqire',
            'route_id|线路' => 'require|number',
            'route_name|线路名称' => 'require',
            'is_carousel_banner|是否轮播' => 'number|require',


    ];


}

?>
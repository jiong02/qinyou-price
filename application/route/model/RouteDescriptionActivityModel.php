<?php
namespace app\route\model;

use think\Model;

class RouteDescriptionActivityModel extends Model
{
    public $table = 'ims_route_description_activity';

    public $rule = [
        'description_id|描述' => 'require|number',
        'activity_id|活动' => 'require|number',
        'route_activity_id|线路活动' => 'require|number',
        'activity_name|活动名称' => 'require|number',
        'activity_image_status' => 'require',

    ];


}


?>
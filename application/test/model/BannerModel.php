<?php
namespace app\test\model;

class BannerModel extends BaseModel
{
    public $table = 'cheeru_banner';

    public $rules = [
        'banner_place_id|海岛' => 'require|number',
        'banner_place_name|海岛名称' => 'require',
        'banner_route_id|线路' => 'require|number',
        'banner_route_name|线路名称' => 'require',
        'banner_image_uniqid|图片ID' => 'require',
        'banner_image_path|图片地址' => 'require',
        'banner_sort|排序' => 'number',
    ];










}
?>
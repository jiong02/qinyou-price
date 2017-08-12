<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/14
 * Time: 11:18
 */

namespace app\test\model;


class ImageSettingModel extends BaseModel
{
    public $baseHidden = ['create_time','modify_time','image_type'];
    protected $table = 'cheeru_image_setting';
}
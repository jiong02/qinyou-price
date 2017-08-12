<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-24
 * Time: 16:17
 */

namespace app\ims\model;


class ImageModel extends BaseModel
{
    protected $table = 'ims_image';
    protected $connection = 'ims_new';

    public function getImagePathValueByUniqid($uniqid)
    {
        return $this->where('image_uniqid',$uniqid)->value('image_path');
    }

    public function getImagePathByUniqid($uniqid)
    {
        return $this->where('image_uniqid',$uniqid)->field('image_path')->select();
    }
}
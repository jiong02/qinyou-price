<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/12
 * Time: 16:54
 */

namespace app\test\model;


use think\Model;

class BaseModel extends Model
{
    public $baseHidden = ['create_time','modify_time'];

    public function __construct($data = [])
    {
        $this->hidden = array_merge($this->hidden, $this->baseHidden);
        parent::__construct($data);
    }
}
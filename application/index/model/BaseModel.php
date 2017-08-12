<?php

namespace app\index\model;

use app\index\controller\Data;
use think\Model;

class BaseModel extends Model
{
    public function getValue($column, $where = [])
    {
        return $this->where($this->getWhere($where))->value($column);
    }

    public function getWhere($where){

        if (is_numeric($where) || is_string($where)) {

            return ['id'=> $where];

        }else{

            return $where;
        }
    }

}

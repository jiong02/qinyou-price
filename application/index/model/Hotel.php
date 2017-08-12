<?php

namespace app\index\model;

class Hotel extends BaseModel
{
    protected $table = 'hotel';
    protected $connection = 'test_input';

    protected function getStartCityAttr($value)
    {
        return json_decode($value);
    }


}

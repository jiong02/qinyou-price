<?php
namespace app\index\model;

class Titles extends BaseModel
{
    protected $table = 'ims_titles';

    protected function title()
    {
        return $this->hasOne('Titles','title_id');
    }
}
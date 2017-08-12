<?php

namespace app\index\model;

class TrfBase extends BaseModel
{
    protected $table = 'traffic_base';
    protected $connection = 'test_input';

    public function single()
    {
        return $this->hasMany('Trf','base_id');
    }

    public function getAdultDiffAttr($adultDiff)
    {
        return json_decode($adultDiff);
    }

    public function getBabyDiffAttr($babyDiff)
    {
        return json_decode($babyDiff);
    }

    public function getKidsDiffAttr($kidsDiff)
    {
        return json_decode($kidsDiff);
    }

    public function getAdultDiffNewAttr($value,$data)
    {
        $ret = [];
        foreach (json_decode($data['adult_diff'],true) as $key => $value) {
            $ret[$key]['value'] = ($key + 1).'人差';
            $ret[$key]['label'] = $value;
        }
        return $ret;
    }

    public function getBabyDiffNewAttr($value,$data)
    {
        $ret = [];
        foreach (json_decode($data['baby_diff'],true) as $key => $value) {
            $ret[$key]['value'] = ($key + 1).'人差';
            $ret[$key]['label'] = $value;
        }
        return $ret;
    }

    public function getKidsDiffNewAttr($value,$data)
    {
        $ret = [];
        foreach (json_decode($data['kids_diff'],true) as $key => $value) {
            $ret[$key]['value'] = ($key + 1).'人差';
            $ret[$key]['label'] = $value;
        }
        return $ret;
    }
}

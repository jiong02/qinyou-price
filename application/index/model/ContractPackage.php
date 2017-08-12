<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-27
 * Time: 17:01
 */

namespace app\index\model;


class ContractPackage extends BaseModel
{
    protected $table = 'contract_package';
    protected $connection = 'test_input';

    protected function getTypeAttr($value)
    {
        switch ($value) {
            case 'fix':
                return '固定套餐';

            case 'free':
                return '自由套餐';
        }

    }

    protected function getTypeNewAttr($value,$data)
    {
        return $data['type'];
    }

    protected function getContentNewAttr($value,$data)
    {
        if (stripos($data['content'],'-')) {
            $str = explode('-', $data['content']);
            return $str[0].'D'.$str[1].'N';
        }
        return $data['content'].'D';
    }
}
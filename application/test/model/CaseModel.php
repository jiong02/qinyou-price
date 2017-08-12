<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/12
 * Time: 16:53
 */

namespace app\test\model;


class CaseModel extends BaseModel
{
    protected $table = 'cheeru_case';

    public $rule = [
        'case_title|案例标题' => 'require',
        'case_description|案例描述' => 'require',
        'case_content|案例内容' => 'require',
    ];
}
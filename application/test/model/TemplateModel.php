<?php
namespace app\test\model;

class TemplateModel extends BaseModel
{
    public $table = 'cheeru_template';

    public $rules = [
        'template_name|模板名称' => 'require',
        'template_show_status|模板展示类型' => 'require|number',
    ];



}

?>
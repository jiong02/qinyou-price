<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 刘志淳 <chun@engineer.com>
// +----------------------------------------------------------------------

namespace app\common\command\create;

use app\common\command\Create;
use app\common\components\Database;
use think\console\input\Option;

class Model extends Create
{
    protected $type = "Model";

    protected function configure()
    {
        parent::configure();
        $this->setName('create:model')
            ->addOption('plain', null, Option::VALUE_NONE, 'Generate an empty model class.')
            ->setDescription('Create a new model class');
    }

    protected function getStub()
    {
        if ($this->input->getOption('plain')) {
            return __DIR__ . '/stubs/model.plain.stub';
        }
        return __DIR__ . '/stubs/model.stub';
    }

    protected function getNamespace($appNamespace, $module)
    {
        return parent::getNamespace($appNamespace, $module) . '\model';
    }

    protected function getTable($Model)
    {
        $tableName = str_replace($this->type, '', $Model);
        $tableName = hump_to_under_score($tableName);
        $table = Database::setTableName($tableName);
        return $table;
    }

    protected function getConnection($table)
    {
        $connection = $table->getConnect();
        return $connection;
    }

    protected function getTableName($table)
    {
        $tableName = $table->getTable();
        return $tableName;
    }

    protected function getModelFields($table)
    {
        $fieldsArray = $table->getColumnNameAndColumnComment();
        $fields = "/**\n* 模型字段列表\n";
        foreach ($fieldsArray as  $field) {
            if(!$table->checkHiddenFields($field['name'])){
                $fields .= "* @param ";
                $fields .= $field['name'];
                $fields .= " '".$field["comment"]."'";
                $fields .= "\n";
            }

        }
        return $fields .' */';
    }
}

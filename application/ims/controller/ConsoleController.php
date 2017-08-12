<?php

namespace app\ims\controller;

use think\Console;
use think\Request;

class ConsoleController extends PrivilegeController
{
    public function create(Request $request, $type, $name, $module = '')
    {
        $module = $module ? $module : $request->module();
        switch ($type) {
            case 'Controller':
                $output = $this->createController($name,$module);
                break;
            case 'Model':
                $output = $this->createModel($name,$module);
                break;
        }
        return $output->fetch();
    }

    public function createController($name, $module)
    {
        $output = Console::call('create:controller',[ $module . '/' .$name]);
        return $output;
    }

    public function createModel($name, $module)
    {
        $output = Console::call('create:model',[ $module . '/' .$name]);
        return $output;

    }
}

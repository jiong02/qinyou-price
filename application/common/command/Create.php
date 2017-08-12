<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-04-15
 * Time: 23:10
 */

namespace app\common\command;

use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Db;

abstract class Create extends Command
{

    protected $type;

    abstract protected function getStub();

    protected function configure()
    {
        $this->addArgument('name', Argument::REQUIRED, "The name of the class");
    }

    protected function execute(Input $input, Output $output)
    {

        $name = trim($input->getArgument('name'));

        $classname = $this->getClassName($name);

        $pathname = $this->getPathName($classname);

        if (is_file($pathname)) {
            $output->writeln('<error>' . $this->type . ' already exists!</error>');
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(strtolower(dirname($pathname)), 0755, true);
        }

        file_put_contents($pathname, $this->buildClass($classname));

        $output->writeln('<info>' . $this->type . ' created successfully.</info>');

    }

    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);

        $search = ['{%className%}', '{%namespace%}', '{%app_namespace%}'];
        $replace = [$class, $namespace, Config::get('app_namespace')];
        if($this->type == 'Model'){
            $table = $this->getTable($class);
            $fields =  $this->getModelFields($table);
            $connection = $this->getConnection($table);
            $table = $this->getTableName($table);
            $search = array_merge($search,['{%fields%}','{%connection%}','{%table%}']);
            $replace = array_merge($replace,[$fields, $connection, $table]);
        }
        return str_replace($search,$replace, $stub);
    }

    protected function getPathName($name)
    {
        $name = str_replace(Config::get('app_namespace') . '\\', '', $name);

        return APP_PATH . str_replace('\\', '/', $name) . '.php';
    }

    protected function getClassName($name)
    {
        $appNamespace = Config::get('app_namespace');

        if (strpos($name, $appNamespace . '\\') === 0) {
            return $name;
        }

        if (Config::get('app_multi_module')) {
            if (strpos($name, '/')) {
                list($module, $name) = explode('/', $name, 2);
            } else {
                $module = 'common';
            }
        } else {
            $module = null;
        }

        if (strpos($name, '/') !== false) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->getNamespace($appNamespace, $module) . '\\' . $name;
    }

    protected function getNamespace($appNamespace, $module)
    {
        return $module ? ($appNamespace . '\\' . $module) : $appNamespace;
    }

}

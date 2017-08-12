<?php

namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Request;
class BaseController extends Controller
{
    public function post($name, $default = null)
    {
        $request = Request::instance();
        return $request->post($name, $default);
    }

    public function getSucc($msg)
    {
        return json(['status' => 1, 'msg' => $msg]);
    }

    public function getErr($msg)
    {
        return json(['status' => 0, 'msg' => $msg]);
    }

    //打印数据并停止已下的输出
    public function dumpExit($data){
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        exit;
    }

    //获得session信息
    public function getSession()
    {
        if(empty($se = Session::get('emp_info'))){
            echo '你未登录';
            exit;
        }
        $info = array();
        $info = json_decode(json_encode($se),true);
        return $info;

    }







}

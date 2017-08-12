<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-23
 * Time: 11:59
 */
namespace app\components;
use \Exception;
use think\exception\Handle;
use think\exception\HttpException;

class Http extends Handle
{
//    public function render(Exception $e)
//    {
//        if ($e instanceof HttpException) {
//            $statusCode = $e->getStatusCode();
//            if ($statusCode == 200){
//                return json(Response::Error([], $e->getMessage()));
//            }
//            return $this->renderHttpException($e);
//        } else {
//            return $this->convertExceptionToResponse($e);
//        }
//    }
    public function render(Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            if ($statusCode == 200){
                return json(Response::Error($e->getMessage()));
            }
            return $this->renderHttpException($e);
        } else {
            return $this->convertExceptionToResponse($e);
        }
    }

}

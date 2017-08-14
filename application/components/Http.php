<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-23
 * Time: 11:59
 */
namespace app\common\exception;
use \Exception;
use think\exception\Handle;
use think\exception\HttpException;

class Http extends Handle
{
    public function render(Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            if ($statusCode == 404){
                $result = [
                    'status'=>0,
                    'code' => $statusCode,
                    'msg'  => $e->getMessage(),
                    'time' => $_SERVER['REQUEST_TIME'],
                ];
                return json($result, $statusCode);
            }
            return $this->renderHttpException($e);
        } else {
            return $this->convertExceptionToResponse($e);
        }
    }

}
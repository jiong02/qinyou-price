<?php
namespace app\test\model;
use think\Model;

class TestAccount extends BaseModel
{
    public $table = 'test_account';

    public function checkAccountId($accountId, $accessToken)
    {
        $result = $this->where('id', $accountId)->where('access_token',$accessToken)->count();
        if ($result == 1){
            return true;
        }
        throw new \think\Exception('当前用户不存在');
    }
}

?>
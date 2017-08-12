<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-09
 * Time: 17:22
 */

namespace app\index\controller;


use app\index\model\OrderOther;

class Other extends BaseController
{
    public function deleteOtherInfo()
    {
        $otherId = $this->post('other_id');
        if($otherModel = OrderOther::get($otherId)){
            if($otherModel->delete()){
                return getSucc('删除成功！');
            }
        }
        return getErr('删除失败！');
    }

    public function getAllOtherInfo($itinId = '',$return = [])
    {
        $itinId = $this->post('itin_id',$itinId);
        $otherInfo = OrderOther::where('itin_id',$itinId)->select();
        foreach ($otherInfo as $key => $value) {
            $return[$key] = $this->formatReturnData($value);
        }
        return $return;
    }

    public function addOrUpdateOtherInfo()
    {
        $otherId = $this->post('other_id');
        $otherName = $this->post('name');
        $buyer = $this->post('purchaser');
        $amount = $this->post('amount');
        $remark = $this->post('remark');
        $otherPrice = $this->post('exchange');
        $itinId = $this->post('itin_id');
        $orderId = $this->post('order_id');

        $input['other_name'] = $otherName;
        $input['other_price'] = $otherPrice;
        $input['amount'] = $amount;
        $input['buyer'] = $buyer;
        $input['itin_id'] = $itinId;
        $input['order_id'] = $orderId;
        $input['remark'] = $remark;
        if ($otherId){
            return $this->updateOtherInfo($otherId,$input);
        }else{
            return $this->addOtherInfo($input);
        }
    }

    public function getOtherInfo()
    {
        $otherId = $this->post('other_id');
        if ($result = OrderOther::get($otherId)){
            $return = $this->formatReturnData($result);
            return getSucc($return);
        }else{
            return getErr('获取失败！');
        }
    }

    /**
     * 添加其他费用信息
     * @param $input
     * @return \think\response\Json
     */
    public function addOtherInfo($input)
    {

        if($result = OrderOther::create($input))
        {
            $return = $this->formatReturnData($result);
            return getSucc($return);
        }else{
            return getErr('添加失败');

        }
    }

    /**
     * 更新其他费用信息
     * @param $input
     * @return \think\response\Json
     */
    public function updateOtherInfo($otherId,$input)
    {
        $input['id'] = $otherId;
        $result = OrderOther::update($input);
        if($result)
        {
            $return = $this->formatReturnData($result);
            return getSucc($return);
        }else{
            return getErr('添加失败');

        }
    }

    public function formatReturnData($result)
    {
        $return['type'] = 'other';
        $return['other_id'] = $result->id;
        $return['name'] = $result->other_name;
        $return['amount'] = $result->amount;
        $return['exchange'] = $result->other_price;
        $return['purchaser'] = $result->buyer;
        $return['remark'] = $result->remark;
        return $return;
    }
}
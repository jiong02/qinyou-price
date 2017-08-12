<?php
namespace app\index\controller;

use app\index\model\Cust as CustModel;
use app\index\model\Order as OrderModel;
use app\index\model\OrderItin;
use app\index\model\TrfBase;
use think\Request;

class Cust extends BaseController
{

    public function getCustInfo()
    {
        $id = $this->request->post('id');
        if ($itin = OrderItinModel::get($id)) {
            $state = 1;
            $count = Cust::where(['itin_id'=>$id])->count();
            if ($itin->cust_num != 0 || $count != $itin->cust_num || $itin->departure == '' || $itin->destination == '')
            {
                $state = 0;
            }
            $return['basic']['state'] = $state;
            $return['basic']['persons'] = $itin->cust_num;
            $return['basic']['startCity'] = $itin->departure;
            $return['basic']['backCity'] = $itin->destination;
            $return['basic']['list'] = [];
        }
        return getErr('不存在该线路！');
    }

    public function checkCustInfo($id)
    {
        $id = $this->request->post('id',$id);
        if($order = OrderModel::get($id)) {
            $count = CustModel::where(['order_id'=>$id])->count();
            if ($count == $order->cust_num)
            {
                if (!CustModel::where('order_id',$id)->where(function($query){
                    $query->whereOr('passport_no','')->whereOr('birth_date','=','0000-00-00');
                })->find())
                {
                    return '资料已完善';
                }
                return '资料完善中';
            }else{

                return '尚未完善资料';

            }
        }
        return false;
    }

    public function getSingleInfo(Request $request)
    {
        if ($id = $request->post('id',1)) {
            $ret =  CustModel::where(['id'=>$id])->field('cust_name,gender,passport_no,birth_date')->find();
            return getSucc($ret);
        }
        return getErr('数据非法');

    }
    public function delete(Request $request)
    {
        if ($id = $request->post('id')) {

            if ($cust = CustModel::get($id)) {

                if ($cust->delete()) {

                    $order = OrderModel::get($cust->order_id);
                    $order ->cust_num --;
                    if ($order->save()) {
                        return getSucc('删除成功！');
                    };
                }

                return getErr('删除失败！');
            }
            return getErr('当前用户不存在');
        }
        return getErr('数据非法');
    }

    public function addOrUpdate(Request $request)
    {
        if ($request->has('id') && $request->has('name')) {

            $custId = $request->post('cust_id');
            $input['order_id'] = $orderId = $request->post('id');
            $orderModel = OrderModel::get($orderId);
            $input['cust_name'] = $request->post('name');
            $input['gender'] = $request->post('sex','男');
            $input['passport_no'] = $request->post('passport','');
            $input['birth_date'] = $request->post('birth','');
            $input['age'] = Date::getAge($input['birth_date']);
            if ($custId = $request->has('cust_id')) {
                if (CustModel::where(['id'=>$custId])->update($input)) {
                    $cust = CustModel::get($custId);
                    $ret['state'] = $cust->id;
                    $ret['name'] = $cust->name.$cust->sex;
                    $ret['age'] = $cust->age;
                    return getSucc($ret);
                };
                return getErr('数据修改失败');
            }else{
                $custModel =  new CustModel;
                $custCount = $custModel->where('order_id',$orderId)->count();
                if ($custCount < $orderModel->cust_num){
                    if ($cust = CustModel::create($input)) {
                        $ret['state'] = $cust->id;
                        $ret['name'] = $cust->name.$cust->sex;
                        $ret['age'] = $cust->age;
                        return getSucc($ret);
                    }
                }
                return getErr('数据新增失败！');
            }

        }

        return getErr('数据非法！');
    }

    public function getCustBaseInfo(Request $request)
    {
        $id = $request->post('id');
        if ($order = OrderModel::get($id)) {
            for ($i=0; $i < $order->cust_num ; $i++) {
                if (array_key_exists($i, $order->cust)) {
                    $cust = $order->cust[$i];
                    $ret[$i]['state'] = $cust->id;
                    $ret[$i]['name'] = $cust->name.$cust->sex;
                    $ret[$i]['age'] = $cust->age;
                }else{
                    $ret[$i]['state'] = 0;
                    $ret[$i]['name'] = '';
                    $ret[$i]['age'] = '';
                }
            }

            return getSucc($ret);
        }else{

            return getErr('当前订单不存在');
        }

    }
}

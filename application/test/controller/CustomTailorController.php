<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/12
 * Time: 19:15
 */

namespace app\test\controller;

use app\components\Response;
use app\test\model\CustomTailorModel;
use think\Request;

class CustomTailorController extends BaseController
{
    public function addCustomerData(Request $request)
    {
        $customerName = $data['customer_name'] = $request->param('name');
        $customerPhone = $data['customer_phone'] = $request->param('phone');
        $data['itinerary_days'] = $request->param('days');
        $data['departure_of_date'] = $request->param('date');
        $data['remark'] = $request->param('remark');
        $data['customer_gender'] = $request->param('sex');
        $params = [
            'name'=>[$customerName , 'require'],
            'phone'=>[$customerPhone , 'require'],
        ];
        $this->checkAllParam($params);
        $customTailorModel = new CustomTailorModel();
        $result = $customTailorModel->where('customer_phone',  $customerPhone)->count();
        if (!$result) {
            $result =$customTailorModel->insert($data);
            if ($result) {
                return Response::Success('提交成功,我们的客服稍后会联系您');
            }
            return Response::Success('提交失败,请重新提交');
        }
        return Response::Error('手机已存在,请勿重复提交!');
    }

    public function getCustomerData(Request $request)
    {
        $offset = $request->param('offset', 1);
        $length = $request->param('length',5);
        $params = [
            'offset'=>[$offset , 'require|integer'],
            'length'=>[$length , 'require|integer'],
        ];
        $this->checkAllParam($params);
        $customerModel = new CustomTailorModel();
        $customerData = $customerModel->limit($offset, $length)->field('remark, customer_name as name, customer_gender as sex, customer_phone as phone, itinerary_days as days, departure_of_date as date')->select();
        if ($customerData){
            if (!$customerData->isEmpty()){
                return Response::Success('定制信息获取成功',$customerData->toArray());
            }else{
                return Response::Success('当前无定制信息');
            }
        }
        return Response::Error('定制信息获取失败');
    }

    public function getCustomerDataByCustomerId(Request $request)
    {
        $customerId = $request->param('customer_id');
        $customerModel = new CustomTailorModel();
        $customerCount = $customerModel->where('id',$customerId)->count();
        if ($customerCount){
            $customerData = $customerModel->field('remark, customer_name as name, customer_gender as sex, customer_phone as phone, itinerary_days as days, departure_of_date as date')->find();
            if ($customerData){
                return Response::Success('定制信息获取成功',$customerData->toArray());
            }
            return Response::Error('定制信息获取失败');
        }
        return Response::Error('当前定制信息不存在');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/12
 * Time: 19:15
 */

namespace app\test\controller;

use app\components\Response;
use app\components\wechat\WechatEnterpriseSendMessage;
use app\ims\model\EmployeeModel;
use app\test\model\CustomTailorAssignModel;
use app\ims\model\CustomTailorModel;
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
                $this->sendNotifyMessage($customerName);
                return Response::Success('提交成功,我们的客服稍后会联系您');
            }
            return Response::Success('提交失败,请重新提交');
        }
        return Response::Error('手机已存在,请勿重复提交!');
    }

    public function sendNotifyMessage($customerName)
    {
        $customTailorAssignModel = new CustomTailorAssignModel();
        $accountNameSet = $customTailorAssignModel->where('permissions',10)->column('employee_account_name');
        $sendMessage = new WechatEnterpriseSendMessage();
        $textContent = '客户'.$customerName.'已提交定制表，请尽快登录后台处理。';
        $sendMessage->sendTextMessage($accountNameSet,$textContent);
    }

    public function getCustomerData(Request $request)
    {
        $employeeId = $request->param('employee_id');
        $employeeToken = $request->param('employee_token');
        $offset = $request->param('offset', 1);
        $length = $request->param('length',5);
        $params = [
            'offset'=>[$offset , 'require|integer'],
            'employee_id'=>[$employeeId , 'require|integer'],
            'employee_token'=>[$offset , 'require'],
            'length'=>[$length , 'require|integer'],
        ];
        $this->checkAllParam($params);
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->checkExist($employeeId, $employeeToken);
        $employeeModel = $employeeModel->get($employeeId);
        if ($result){
            $customTailorAssignModel = new CustomTailorAssignModel();
            $employeeCount = $customTailorAssignModel->where('employee_account_name', $employeeModel->account_name)->where('permissions',10)->count();
            if ($employeeCount > 0){
                $customerModel = new CustomTailorModel();
                $customerData = $customerModel->limit($offset, $length)->field('id, follow_up_employee_id as follow_up_employee_name,customer_name as name, customer_gender as sex, customer_phone as phone')->select();
            }else{
                $customerData = $employeeModel->customTailor()->field('id, follow_up_employee_id as follow_up_employee_name,customer_name as name, customer_gender as sex, customer_phone as phone')->select();
            }
            foreach ($customerData as $key => $customerDatum) {
                if ($customerDatum['follow_up_employee_name'] == 0){
                    $customerData[$key]['follow_up_employee_name'] = '未分配';
                }else{
                    $customerData[$key]['follow_up_employee_name'] = $employeeModel->get($customerDatum['follow_up_employee_name'])->employee_name;
                }
            }
            $customerData = $customerData->toArray();
            if ($customerData){
                return getSuccess($customerData);
            }else{
                return getError('当前无定制信息');
            }

        }
        return getError('定制信息获取失败');

    }

    public function getCustomerDataByCustomerId(Request $request)
    {
        $customerId = $request->param('customer_id');
        $customerModel = new CustomTailorModel();
        $customerCount = $customerModel->where('id',$customerId)->count();
        if ($customerCount){
            $customerData = $customerModel->field('record, remark, customer_name as name, customer_gender as sex, customer_phone as phone, itinerary_days as days, departure_of_date as date')->find();
            if ($customerData){
                return Response::Success('定制信息获取成功',$customerData->toArray());
            }
            return Response::Error('定制信息获取失败');
        }
        return Response::Error('当前定制信息不存在');
    }

    public function modifyFollowUpRecordByCustomerId(Request $request)
    {
        $employeeId = $request->param('employee_id');
        $employeeToken = $request->param('employee_token');
        $customerId = $request->param('customer_id');
        $followUpRecord = $request->param('record');
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->checkExist($employeeId, $employeeToken);
        if ($result){
            $customTailorModel = CustomTailorModel::get($customerId);
            if($customTailorModel){
                if($customTailorModel->follow_up_employee_id == $employeeId){
                    $customTailorModel->follow_up_record = $followUpRecord;
                    if($customTailorModel->save()){
                        return getSuccess('跟进记录修改成功');
                    }
                    return getError('跟进记录修改失败');
                }
                return getError('请将改定制表的跟进人修改为您！');
            }
            return getError('当前定制列表不存在！');
        }
        return getError('当前用户不存在！');
    }

    public function modifyFollowUpEmployeeIdByCustomerId(Request $request)
    {
        $employeeId = $request->param('employee_id');
        $employeeToken = $request->param('employee_token');
        $customerId = $request->param('customer_id');
        $followUpEmployee = $request->param('follow_up_employee');
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->checkExist($employeeId, $employeeToken);
        if ($result){
            $assignEmployeeModel = $employeeModel->get($employeeId);
            $customTailorAssignModel = new CustomTailorAssignModel();
            $employeeCount = $customTailorAssignModel->where('employee_account_name', $assignEmployeeModel->account_name)->where('permissions',10)->count();
            if ($employeeCount > 0){
                $customTailorModel = CustomTailorModel::get($customerId);
                if ($customTailorModel){
                    $customTailorModel->follow_up_employee_id = $followUpEmployee;
                    if($customTailorModel->save()){
                        $followUpEmployeeModel = $employeeModel->get($followUpEmployee);
                        $sendMessage = new WechatEnterpriseSendMessage();
                        $userId = $followUpEmployeeModel->account_name;
                        $textContent =
                            $assignEmployeeModel->employee_ename .
                            '已将'.$customTailorModel->customer_name.
                            '的定制表交由您跟进，请尽快登录后台处理';
                        $sendMessage->sendTextMessage($userId,$textContent);
                        return getSuccess('跟进人修改成功');
                    }
                    return getError('跟进人修改失败');
                }
                return getError('当前定制列表不存在！');
            }
            return getError('您没有修改权限！');
        }
        return getError('当前用户不存在！');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-25
 * Time: 14:28
 */

namespace app\index\controller;

use app\index\model\Contract;
use app\index\model\ContractPackage;
use app\index\model\ContractRoomFav;
use app\index\model\ContractRoomForm;
use app\index\model\Cust;
use app\index\model\OrderItin as OrderItinModel;
use app\index\model\HotelAct;
use app\index\model\HotelApt;
use app\index\model\OrderActivity;
use app\index\model\OrderActivityCust;
use app\index\model\OrderRoomCust;
use app\index\model\OrderRoomFavorable;
use app\index\model\OrderRoomList;
use app\index\model\OrderRoomPackage;
use app\index\model\Room as RoomModel;
use app\index\model\OrderRoom;
use app\index\model\ContractDate;
use app\index\model\Order;
use think\Db;
use think\Exception;

class Room extends BaseController
{
    public function addActivityCustInfo()
    {
        $cust = $this->post('cust');
        $actId =  $this->post('act_id');
        $itinId =  $this->post('itin_id');
        $orderActivityId =  $this->post('order_act_id');
        $orderActivityCustModel = new OrderActivityCust();
        if (stripos(',',$cust)){
            $cust =  explode(',',$cust);
        }
        $result = $orderActivityCustModel
            ->where('activity_id',$actId)
            ->where('itin_id',$itinId)
            ->where('cust_id','IN',$cust)
            ->update(['order_activity_id'=>$orderActivityId]);
        if ($result){
            return getSucc('新增成功！');
        }
        return getErr('新增失败');

    }

    public function updateActivityCustInfo()
    {
        $cust = $this->post('cust');
        $actId =  $this->post('act_id');
        $itinId =  $this->post('itin_id');
        $orderActivityCustModel = new OrderActivityCust();
        if (stripos(',',$cust)){
            $cust =  explode(',',$cust);
        }
        $result = $orderActivityCustModel
            ->where('activity_id',$actId)
            ->where('itin_id',$itinId)
            ->where('cust_id','IN',$cust)
            ->update(['order_activity_id'=>0]);
        if ($result){
            return getSucc('新增成功！');
        }
        return getErr('新增失败');
    }

    public function getFreeActivityCustInfo($return = array())
    {
        $itinId = $this->post('itin_id',1);
        $activityId = $this->post('act_id',211);
        $orderActivityCust = new OrderActivityCust();
        $orderActivityInfo = $orderActivityCust
                                ->where('itin_id',$itinId)
                                ->where('activity_id',$activityId)
                                ->where('order_activity_id',0)
                                ->select();
        foreach ($orderActivityInfo as $index => $item) {
            $return[] = $this->formatReturnActivityCustInfo($item->cust);
        }
        return getSucc($return);
    }

    public function getModiedActivityCustInfo($return = array())
    {
        $orderActivityId = $this->post('order_act_id');
        $orderActivityCust = new OrderActivityCust();
        $orderActivityInfo = $orderActivityCust
            ->where('order_activity_id',$orderActivityId)
            ->select();
        foreach ($orderActivityInfo as $index => $item) {
            $return[] = $this->formatReturnActivityCustInfo($item->cust);
        }
        return getSucc($return);
    }

    public function formatReturnActivityCustInfo($custModel)
    {
        $ret['id'] = $custModel->id;
        $ret['name'] = $custModel->cust_name;
        $ret['age'] = $custModel->age;
        $ret['sex'] = $custModel->gender;
        $ret['passport'] = $custModel->passport_no;
        return $ret;
    }

    public function formatActivityDateInfo($activityModel)
    {
        $activityDate = '';
        if($activityModel->activity_date != '0000-00-00')
        {
            $activityDate = $activityModel->activity_date;
        }
        $return['acttime_value'] = $activityModel->activity_time_arrange == 0 ? '' : $activityModel->activity_time_arrange;
        $return['start_value'] = $activityDate;
        $return['arrange']['name'] = $activityModel->activity_name;
        $detail = [];
        $amount = 0;
        $allocation = $activityModel->activity_allocation;
        if(is_array($allocation)){
            $amount = count($allocation);
            foreach ($allocation as $index => $item) {
                $detail[$index]['value'] = $item;
            }
        }
        $return['arrange']['detail'] = $detail;
        $return['arrange']['amount'] = $amount;
        return $return;
    }

    public function getActivityDateInfo()
    {
        $orderActId = $this->post('order_act_id');
        $activityModel = OrderActivity::get($orderActId);
        $return =  $this->formatActivityDateInfo($activityModel);
        return getSucc($return);
    }

    public function updateActivityDate()
    {
        $input['id'] = $this->post('order_act_id');
        $input['activity_time_arrange'] = $this->post('acttime_value');
        $input['activity_date'] = $this->post('start_value');
        $inputAllocation = json_decode($this->post('arrange'),true);
        $allocation = [];
        if (count($inputAllocation['detail']) != 0){
            $allocation = array_map(function($v){return $v['value'];}, $inputAllocation['detail']);
        }
        $input['activity_allocation'] = json_encode($allocation);
        $orderActivity = new OrderActivity();
        $orderActivity->update($input);
        return getSucc('更新成功!');
    }
    
    public function delActivityDate()
    {
        $orderActId = $this->post('order_act_id');
        $orderActModel = OrderActivity::get($orderActId);
        if ($orderActModel->delete()){
            return getSucc('删除成功！');
        }else{
            return getErr('删除失败!');
        }
    }

    public function modifyActivityStatus()
    {
        $itinId = $this->post('itin_id');
        $actId = $this->post('act_id');
        $orderActivityCustModel = new OrderActivityCust();
        $status = $orderActivityCustModel
            ->where('activity_id','IN',$actId)
            ->where('itin_id',$itinId)
            ->update(['activity_status'=>'未完善']);
        if ($status){
            return getSucc('状态修改成功！');
        }
        return getErr('状态修改失败！');
    }

    public function addActivityDate()
    {
        $input['itin_id'] = $itinId = $this->post('itin_id');
        $input['activity_id'] = $actId = $this->post('act_id');
        $input['activity_name'] = $actId = $this->post('act_name');
        $input['order_id'] = OrderItinModel::get($itinId)->order_id;
        $orderActivity = new OrderActivity();
        $act = $orderActivity->create($input);
        return getSucc($act->id);
    }
    public function getActivityInfo()
    {
        $itinId = $this->post('itin_id');
        $actId = $this->post('act_id');
        $hotelId = $this->post('hotel_id');
        $orderId = OrderItinModel::get($itinId)->order_id;
        $hotelActModel = HotelAct::get($actId);
        $orderRoomModel = OrderRoom::where(['hotel_id'=>$hotelId, 'itin_id'=>$itinId])->find();
        $checkInTime = $orderRoomModel->check_in_time;
        $checkOutTime = $orderRoomModel->check_out_time;
        $return['name'] = $hotelActModel->name."(".$hotelActModel->new_act_time['type'].")";
        $orderActivityModel = new OrderActivity();
        $actIdArr = $orderActivityModel->where('itin_id',$itinId)->where('activity_id',$actId)->column('id');
        if (count($actIdArr) == 0){
            $input['itin_id'] = $itinId;
            $input['order_id'] = $orderId;
            $input['activity_id'] = $actId;
            $input['activity_name'] = $hotelActModel->name;
            $act = $orderActivityModel->create($input);
            $return['date'][] = ['id'=>$act->id];

        }else{
            foreach ($actIdArr as $index => $item) {
                $return['date'][$index]['id'] = $item;
            }
        }
        $return['limit'] = $hotelActModel->new_act_time['time']['arrange'];
        $return['actStart'] = $this->getActivityDate($checkInTime, $checkOutTime, $hotelActModel->act_day);
        $return['start_value'] = '';
        $return['acttime_value'] = '';
        $return['actTime'][0]['label'] = $hotelActModel->new_act_time['time']['arrange'];
        $return['actTime'][0]['value'] = $hotelActModel->new_act_time['time']['arrange'];
        $return['person']['adult'] = 0;
        $return['person']['child'] = 0;
        $return['person']['infant'] = 0;
        $return['person']['standard'] = 0;
        $return['person']['min'] = 0;
        $return['arrange']['name'] = $hotelActModel->name;
        $return['arrange']['amount'] = 0;
        $return['arrange']['detail'] = [];
        return getSucc($return);
    }

    public function getActivityDate($checkInTime,$checkOutTime,$day)
    {
        $return = [];
        $actDate = Date::getDateFromRange($checkInTime,$checkOutTime);
        foreach ($actDate as $index => $item) {
            $week = Date::getDateWeek(Date::getWeekByDate($item));
            if (in_array($week,$day)){
                $ret['label'] = $item;
                $ret['value'] = $item;
                array_push($return,$ret);
            }
        }
        return $return;
    }

    public function getActAmount($itinId)
    {
        $orderActivityCust = new OrderActivityCust();
        return $orderActivityCust
            ->where('itin_id',$itinId)
            ->where('activity_status','未完善')
            ->where('activity_type','<>','meal')
            ->group('activity_id')
            ->count();
    }

    public function formatActivityInfo($itinId)
    {
        $return['type'] = 'activity';
        $return['act'] = [];
        $orderActivityCust = new OrderActivityCust();
        $activityInfo = $orderActivityCust
            ->where('itin_id',$itinId)
            ->where('activity_status','完善中')
            ->where('activity_type','<>','meal')
            ->field('*')
            ->field('count(activity_id) as amount')
            ->group('activity_id')
            ->select();
        foreach ($activityInfo as $index => $item) {
            $actData['id'] = $item->activity_id;
            $actData['state'] = $item->state;
            $actData['name'] = $item->activity_name;
            array_push($return['act'],$actData);
        }
        return $return;
    }

    public function getActivityBaseInfo()
    {
        $itinId = $this->post('itin_id');
        $activityInfo = $this->formatActivityInfo($itinId);
        if (count($activityInfo) >0){
            $orderActivityCust = new OrderActivityCust();
            $orderActivityCust
                ->where('itin_id',$itinId)
                ->update(['activity_status'=>'完善中']);
        }
        return getSucc($activityInfo);
    }


    public function getRoomCustInfo()
    {
        $ret = [];
        $orderRoomId = $this->post('order_room_id');
        $roomId = $this->post('room_id');
        $type = $this->post('type');
        $custInfo = OrderRoomCust::where(['cost_type'=>$type,'order_room_id'=>$orderRoomId,'room_id'=>$roomId])->select();
        foreach ($custInfo as $index => $item) {
            $ret[$index] = $this->formatReturnCustInfo($item->cust);
        }
        return getSucc($ret);
    }

    public function formatReturnCustInfo($custModel)
    {
        $return['id'] = $custModel->id;
        $return['name'] = $custModel->cust_name;
        $return['age'] = $custModel->age;
        $return['sex'] = $custModel->gender;
        $return['passport'] = $custModel->passport_no;
        return $return;
    }

    public function getIdArr($formData)
    {
        $actIdArr = $formData->act_id;
        $facIdArr = $formData->fac_id;
        return array_merge($actIdArr,$facIdArr);
    }

    public function addRoomCustInfo()
    {
        $adult = json_decode($this->post('adult'),true);
        $child = json_decode($this->post('child'),true);
        $custArr = array_merge($adult,$child);
        $baby = json_decode($this->post('baby'),true);
        $info = [];
        $orderRoomCust = new OrderRoomCust();
        $orderActivityCust = new OrderActivityCust();
        foreach ($custArr as $index => $item) {
            $orderRoomCust = OrderRoomCust::get($item['order_room_cust_id']);
            $orderActivityCust->where('order_room_cust_id',$item['order_room_cust_id'])->delete();
            $itinId = $orderRoomCust->itin_id;
            $custId = $orderRoomCust->cust_id;
            foreach ($item['act_select'] as $key => $value) {
                foreach ($item['act_info'] as $k => $v) {
                    if($item['act_select'][$key] == $v['value']){
                        $actData['order_room_cust_id'] = $item['order_room_cust_id'];
                        $actData['cust_id'] = $item['order_room_cust_id'];
                        $actData['activity_id'] = $v['value'];
                        $actData['activity_name'] = $v['label'];
                        $actData['activity_type'] = 'act';
                        $actData['activity_amount'] = $v['amount'];
                        $actData['itin_id'] = $itinId;
                        $actData['cust_id'] = $custId;
                        array_push($info,$actData);
                    }
                }
            }
            foreach ($item['overlap'] as $key => $value) {
                $actModel = HotelAct::get($value);
                $otherData['order_room_cust_id'] = $item['order_room_cust_id'];
                $otherData['activity_id'] = $actModel->id;
                $otherData['activity_name'] = $actModel->name;
                $otherData['activity_type'] = 'other';
                $otherData['activity_amount'] = 1;
                $otherData['itin_id'] = $itinId;
                $otherData['cust_id'] = $custId;
                array_push($info,$otherData);
            }
            foreach ($item['cater'] as $key => $value) {
                $mealData['order_room_cust_id'] = $item['order_room_cust_id'];
                $mealData['activity_id'] = 0;
                $mealData['activity_name'] = $value;
                $mealData['activity_type'] = 'meal';
                $mealData['activity_amount'] = 1;
                $mealData['itin_id'] = $itinId;
                $mealData['cust_id'] = $custId;
                array_push($info,$mealData);
            }
        }
        if (count($info) != 0 ){
            $orderActivityCust = new OrderActivityCust();
            $result = $orderActivityCust->saveAll($info);
            if ($result){
                $activityCount = $this->getActAmount($itinId);
                return getSucc($activityCount);
            }
        }

        return getErr('数据出错！');
    }

    public function formatRoomCustInfo($custModel,$act = [],$return=[])
    {
        $orderActivityCust = new OrderActivityCust();
        foreach ($custModel as $k => $v) {
            $return[$k]['order_room_cust_id'] = $v->id;
            $return[$k]['cust_id'] = $v->cust->id;
            $return[$k]['name'] = $v->cust->cust_name;
            $return[$k]['age'] = $v->cust->age;
            $return[$k]['sex'] = $v->cust->gender;
            $return[$k]['type'] = $v->cost_type;
            $return[$k]['cater'] = $orderActivityCust
                ->where('order_room_cust_id',$v->id)
                ->where('activity_type','meal')
                ->column('activity_name');
            $return[$k]['overlap'] = $orderActivityCust
                ->where('order_room_cust_id',$v->id)
                ->where('activity_type','other')
                ->column('activity_id');
            foreach ($act as $index => $item) {
                $amount = $orderActivityCust
                    ->where('order_room_cust_id',$v->id)
                    ->where('activity_id',$item['value'])
                    ->value('activity_amount');
                if ($amount >1){
                    $act[$index]['amount'] = $amount;
                }
            }
            $return[$k]['act_info'] = $act;
            $return[$k]['act_select'] = $orderActivityCust
                ->where('order_room_cust_id',$v->id)
                ->where('activity_type','act')
                ->column('activity_id');
        }

        return $return;
    }
    /**
     * @param array $ret
     * @return \think\response\Json
     */
    public function getFreeCustInfo($ret = [])
    {
        $itinId = $this->post('itin_id',1);
        $idColumn = OrderRoomCust::where(['itin_id'=>$itinId])->column('cust_id');
        if (empty($idColumn)) {
            $custInfo = Cust::where(['itin_id'=>$itinId])->select();;
        }else{
            $custInfo = Cust::where(['itin_id'=>$itinId])->where('id','NOT IN',$idColumn)->select();
        }
        foreach ($custInfo as $key => $value) {
            $ret[$key] = $this->formatReturnCustInfo($value);
        }

        return getSucc($ret);
    }

    public function addItinCustInfo()
    {
        $orderId = $this->post('order_id');
        $itinId = $this->post('itin_id');
        $orderRoomId = $this->post('order_room_id');
        $orderRoomListId = $this->post('order_room_list_id');
        $roomId = $this->post('room_id');
        $type = $this->post('type');
        $custIdArr = json_decode($this->post('cust'),true);
        if(!count($custIdArr)){
            return getErr('没有选择客户！');
        }
        $hotelId = Order::get($orderId)->hotel_id;
        $contractId = Contract::where(['hotel_id'=>$hotelId])->value('id');
        foreach ($custIdArr as $key => $value) {
            $data[$key]['order_id'] = $orderId;
            $data[$key]['itin_id'] = $itinId;
            $data[$key]['room_id'] = $roomId;
            $data[$key]['cust_id'] = $value['id'];
            $data[$key]['cost_type'] = $type;
            $data[$key]['order_room_list_id'] = $orderRoomListId;
            $data[$key]['order_room_id'] = $orderRoomId;
            if (!$ageGrades = $this->getAgeGrades($value['id'],$contractId)){
                return getErr('该客户年龄过小，不适合该合同');
            }else{
                $data[$key]['age_grades'] = $ageGrades;
            }
        }
        $orderRoomCust = new OrderRoomCust();
        $result = $orderRoomCust->saveAll($data);
        if ($result) {
//            $orderRoomList = OrderRoomList::get($orderRoomListId);
//            $ret['persons'] = $orderRoomList->$type;
//            $ret['amount'] = count($result);
//            $ret['cust_info']=$this->formatRoomCustInfo($result);
            return getSucc('添加成功');
        }
        return getErr('修改失败！');
    }

    /**
     * 通过交通基础id以及客户id获取当前交通年龄阶段
     * @param string $custId
     * @param string $baseId
     * @return bool|string
     */
    public function getAgeGrades($custId = '', $contractId = '')
    {
        $custId = $this->post('cust_id',$custId);
        $contractId = $this->post('contract_id',$contractId);
        $custModel = Cust::get($custId);
        $contractModel = Contract::get($contractId);
        $age = $custModel->age;
        if ($age > $contractModel->meddle_age){
            $ageGrades = '成人';
        }elseif($age < $contractModel->meddle_age && $age > $contractModel->small_age){
            $ageGrades = '儿童';
        }elseif($age > $contractModel->min_age && $age < $contractModel->small_age){
            $ageGrades = '婴儿';
        }else{
            return false;
        }
        return $ageGrades;
    }

    public function formatOverlapActInfo($hotelId = '18', $ret = [])
    {
        $hotelId = $this->post('hotel_Id',$hotelId);
        $actInfo = HotelAct::where(['hotel_id'=>$hotelId])->where('act_type','>','2')->select();
        foreach ($actInfo as $index => $item) {
            $ret[$index]['label'] = $item->name;
            $ret[$index]['value'] = $item->id;
        }
        return $ret;
    }

    public function formatActInfo($hotelId = '18', $formData, $ret = [])
    {
        $hotelId = $this->post('hotel_Id',$hotelId);
        $hotelAct = new HotelAct();
        $idArr = $this->getIdArr($formData);
        $actInfo = $hotelAct->where(['hotel_id'=>$hotelId])->where('act_type','<=','2')->select();
        foreach ($actInfo as $index => $item) {
            $ret[$index]['label'] = $item->name;
            $ret[$index]['value'] = $item->id;
            $ret[$index]['conatin'] = in_array($item->id,$idArr);
            $ret[$index]['amount'] = 1;
        }
        return $ret;
    }

    public function getRoomInfo($roomId = '173',$orderRoomId = '1', $return = [])
    {
        $orderRoomId = $this->post('order_room_id',$orderRoomId);
        $roomId = $this->post('room_id',$roomId);
        $orderRoomListModel = OrderRoomList::where(['order_room_id'=>$orderRoomId,'room_id'=>$roomId])->find();
        $orderRoomListId = $orderRoomListModel->id;
        $hotelId = HotelApt::where(['id'=>$roomId])->value('hotel_id');
        $orderRoomCust = new OrderRoomCust();
        $packageId = OrderRoomPackage::where(['order_room_id'=>$orderRoomId,'room_id'=>$roomId])->value('package_id');
        $contractRoomId = RoomModel::where(['package_id'=>$packageId,'apt_id'=>$roomId])->value('id');
        $formData = ContractRoomForm::where('room_id',$contractRoomId)->select();
        $overlapAct = $this->formatOverlapActInfo($hotelId);
        foreach ($formData as $index => $items) {
            switch ($items->type) {
                case 'std':
                    $type = 'standard_adult';
                    $actInfo = $this->formatActInfo($hotelId,$items);
                    $custInfo = $orderRoomCust
                        ->where(['order_room_list_id'=>$orderRoomListId,'cost_type'=>$type])
                        ->select();
                    $return['adult'] = [
                        'persons' => $orderRoomListModel->$type,
                        'amount' => count($custInfo),
                        'cust_info'=>$this->formatRoomCustInfo($custInfo,$actInfo),
                    ];
                    $return['adult_list'] = [
                        'cater'=>[['value'=>'早餐', 'label'=>'早餐', 'contain'=>$items->meal_new['breakfast'],],
                            ['value'=>'午餐', 'label'=>'午餐', 'contain'=>$items->meal_new['lunch'],],
                            ['value'=>'下午茶', 'label'=>'下午茶', 'contain'=>$items->meal_new['afternoon_tea'],],
                            ['value'=>'晚餐', 'label'=>'晚餐', 'contain'=>$items->meal_new['dinner'],],],
                        'act'=>$actInfo,
                        'overlap_act'=>$overlapAct,
                    ];
                    break;
                case 'adult':
                    $type = 'extra_adult';
                    $actInfo = $this->formatActInfo($hotelId,$items);
                    $custInfo = $orderRoomCust
                        ->where(['order_room_list_id'=>$orderRoomListId,'cost_type'=>$type])
                        ->select();
                    $return['child'] = [
                        'persons' => $orderRoomListModel->$type,
                        'amount' => count($custInfo),
                        'chosen' => [
                            ['type'=>'adult', 'amount'=>0],
                            ['type'=>'child', 'amount'=>0]
                        ],
                        'cust_info'=>[
                            'persons' => 0,
                            'amount' => 0,
                            'cust_info'=>$this->formatRoomCustInfo($custInfo,$actInfo),
                        ]
                    ];
                    $return['child_list'] = [
                        'cater'=>[
                            ['value'=>'早餐', 'label'=>'早餐', 'contain'=>$items->meal_new['breakfast'],],
                            ['value'=>'午餐', 'label'=>'午餐', 'contain'=>$items->meal_new['lunch'],],
                            ['value'=>'下午茶', 'label'=>'下午茶', 'contain'=>$items->meal_new['afternoon_tea'],],
                            ['value'=>'晚餐', 'label'=>'晚餐', 'contain'=>$items->meal_new['dinner'],],
                        ],
                        'act'=>$actInfo,
                        'overlap_act'=>$overlapAct,
                    ];
                    break;
                case 'kids':
                    $type = 'extra_adult';
                    $actInfo = $this->formatActInfo($hotelId,$items);
                    $custInfo = $orderRoomCust->where(['order_room_list_id'=>$orderRoomListId])
                        ->where(function($query){
                            $query->whereOr('cost_type','extra_kids')->whereOr('cost_type','extra_adult');
                        })
                        ->select();
                    $return['child'] = [
                        'persons' => $orderRoomListModel->$type,
                        'amount' => count($custInfo),
                        'chosen' => [
                            ['type'=>'adult', 'amount'=>0],
                            ['type'=>'child', 'amount'=>0]
                        ],
                        'cust_info'=>$this->formatRoomCustInfo($custInfo,$actInfo),
                    ];
                    $return['child_list'] = [
                        'cater'=>[
                            ['value'=>'早餐', 'label'=>'早餐', 'contain'=>$items->meal_new['breakfast'],],
                            ['value'=>'午餐', 'label'=>'午餐', 'contain'=>$items->meal_new['lunch'],],
                            ['value'=>'下午茶', 'label'=>'下午茶', 'contain'=>$items->meal_new['afternoon_tea'],],
                            ['value'=>'晚餐', 'label'=>'晚餐', 'contain'=>$items->meal_new['dinner'],],
                        ],
                        'act'=>$actInfo,
                        'overlap_act'=>$overlapAct,
                    ];
                    break;
                case 'baby':
                    $actInfo = $this->formatActInfo($hotelId,$items);
                    $custInfo = $orderRoomCust->where(['order_room_list_id'=>$orderRoomListId])
                        ->where('cost_type','extra_kids')
                        ->select();
                    $return['infant'] = [
                        'persons' => 0,
                        'amount' => 0,
                        'cust_info'=>$this->formatRoomCustInfo($custInfo,$actInfo),
                    ];
                    $return['infant_list'] = [
                        'cater'=>[
                            ['value'=>'早餐', 'label'=>'早餐', 'contain'=>$items->meal_new['breakfast'],],
                            ['value'=>'午餐', 'label'=>'午餐', 'contain'=>$items->meal_new['lunch'],],
                            ['value'=>'下午茶', 'label'=>'下午茶', 'contain'=>$items->meal_new['afternoon_tea'],],
                            ['value'=>'晚餐', 'label'=>'晚餐', 'contain'=>$items->meal_new['dinner'],],
                        ],
                        'act'=>$actInfo,
                        'overlap_act'=>$overlapAct,
                    ];
                    break;
            }
        }
        return getSucc($return);
    }

    public function addPackageInfo()
    {
        $roomId =$this->post('room_id');
        $listId =$this->post('list_id');
        $orderRoomId =$this->post('order_room_id');
        $packageIdArr = json_decode($this->post('package'),true);
        $unoverlapId = $this->post('unoverlap');
        $overlapIdArr  = json_decode($this->post('overlap'),true);
        if (count($overlapIdArr) != 0 ){
            foreach ($overlapIdArr as $key => $value) {
                $contractRoomFavModel = ContractRoomFav::get($value);
                $favData[$key]['room_id'] = $roomId;
                $favData[$key]['order_room_list_id'] = $listId;
                $favData[$key]['order_room_id'] = $orderRoomId;
                $favData[$key]['is_over'] = '是';
                $favData[$key]['fav_id'] = $contractRoomFavModel->id;
                $favData[$key]['fav_name'] = $contractRoomFavModel->name;
            }
        }else{
            $favData = [];
        }
        if (!empty($unoverlapId)){
            $data[0]['room_id'] = $roomId;
            $data[0]['order_room_list_id'] = $roomId;
            $data[0]['order_room_id'] = $orderRoomId;
            $contractRoomFavModel = ContractRoomFav::get($unoverlapId);
            $data[0]['is_over'] = '否';
            $data[0]['fav_id'] = $contractRoomFavModel->id;
            $data[0]['fav_name'] = $contractRoomFavModel->name;
            $favData = array_merge($favData,$data);
        }
        if (count($favData) != 0){
            $orderRoomFavorableModel = new OrderRoomFavorable;
            Db::startTrans();
            try{
                $orderRoomFavorableModel->where('order_room_id',$orderRoomId)->delete();
                $orderRoomFavorableModel->saveAll($favData);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
            }
        }
        if (count($packageIdArr) != 0) {
            foreach ($packageIdArr as $key => $value) {
                $packageModel = ContractPackage::get($value);
                $packageData[$key]['room_id'] = $roomId;
                $packageData[$key]['order_room_list_id'] = $listId;
                $packageData[$key]['order_room_id'] = $orderRoomId;
                $packageData[$key]['order_room_id'] = $orderRoomId;
                $packageData[$key]['package_id'] = $packageModel->id;
                $packageData[$key]['package_name'] = $packageModel->content_new;
                $packageData[$key]['package_type'] = $packageModel->type_new;
            }
            $orderRoomPackageModel = new OrderRoomPackage();
            Db::startTrans();
            try{
                $orderRoomPackageModel->where('order_room_id',$orderRoomId)->delete();
                $orderRoomPackageModel->saveAll($packageData);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
            }
        }
            return $this->getPackage($listId);
    }
    public function getPackage($listId = '', $orderRoomId = '', $ret = [])
    {
        $listId = $this->post('list_id',$listId);
        $orderRoomId = $this->post('order_room_id',$orderRoomId);
        $roomId = $this->getRoomId($listId);
        $roomModel = HotelApt::get($roomId);
        $ret['room_name'] = $roomModel->name;
        $ret['room_id'] = $roomModel->id;
        $ret['amount'] = OrderRoomPackage::where(['order_room_id'=>$orderRoomId])->count();
        $idInfo = OrderRoomPackage::where(['order_room_id'=>$orderRoomId])->field('package_id')->select();
        if (count($idInfo) != 0 ) {
            foreach ($idInfo as $key => $value) {
                $packageId[$key]['value'] = $value['package_id'];
            }
        }else{
            $packageId = [];
        }
        $ret['package'] = $packageId;
        $ret['is_complete'] = 0;
        $idInfo = OrderRoomFavorable::where(['order_room_id'=>$orderRoomId,'is_over'=>'是'])->field('fav_id')->select();
        if (count($idInfo) != 0 ) {
            foreach ($idInfo as $key => $value) {
                $overlapId[$key] = $value['fav_id'];
            }
        }else{
            $overlapId = [];
        }
        $ret['overlap_id'] = $overlapId;
        $ret['overlap_opts'] = $this->getOverLapInfo($roomId);
        $ret['unoverlap_opts']= $this->getUnoverlapInfo($roomId);
        $unoverlapId = OrderRoomFavorable::where(['order_room_id'=>$orderRoomId,'is_over'=>'否'])->value('fav_id');
        $ret['unoverlap_id']= $unoverlapId ? $unoverlapId : '';
        $ret['package_opts'] = $this->getPackageInfo($listId,$roomId);
        return getSucc($ret);
    }

    //获取套餐信息
    public function getPackageInfo($listId,$roomId,$ret = [])
    {
        $orderRoom = $this->getOrderRoom($listId);
        $hotelId = HotelApt::get($roomId)->hotel_id;
        $constractId = $this->getConstractIdByHotelId($hotelId);
        $seasonId = $this->getSeasonId($constractId,$orderRoom->check_in_time,$orderRoom->check_out_time);
        if ($seasonId === false)
        {
            return getErr('当前出行时间下没有房间！');
        }
        $contractRoom = RoomModel::where('apt_id',$roomId)->where('season_id','in',$seasonId)->select();
        foreach ($contractRoom as $key => $item) {

            $ret[$key]['value'] = $item->package->id;
            $ret[$key]['label'] = $item->package->type . ',' .$item->package->content_new;
        }
        return $ret;
    }

    //通过房间id获取可叠加优惠信息
    public function getOverLapInfo($roomId, $ret = [])
    {
        $data = ContractRoomFav::where('room_id',$roomId)->where('is_over','否')->select();
        if (!empty($data))
        {
            foreach ($data as $k => $value) {
                $ret[$k]['label'] = $value->name. '(' .$value->des .')';
                $ret[$k]['value'] = $value->id;
            }
        }

        return $ret;

    }

    //通过房间id获取不可叠加优惠信息
    public function getUnOverLapInfo($roomId, $ret = [])
    {
        $data = ContractRoomFav::where('room_id',$roomId)->where('is_over','是')->select();
        if (!empty($data))
        {
            foreach ($data as $k => $value) {
                $ret[$k]['label'] = $value->name. '(' .$value->des .')';
                $ret[$k]['value'] = $value->id;
            }
        }

        return $ret;

    }

    public function getOrderRoom($listId)
    {
        return $this->getRoomList($listId)->orderRoom;
    }

    public function getRoomList($id)
    {
        return OrderRoomList::get($id);
    }

    public function getRoomId($listId)
    {
        return $this->getRoomList($listId)->room_id;
    }
    /**
     * 添加基本房间信息
     */
    public function addBaseInfo()
    {
        $input['order_id'] = $orderId = $this->post('order_id',1);
        $input['itin_id'] = $this->post('itin_id',1);
        $input['hotel_id'] = $this->post('hotel_id',1);
        $input['check_in_time'] = $this->post('check_in','2016-11-01');
        $input['check_out_time'] = $this->post('check_out','2017-10-31');
        $input['room_cat'] = $this->post('type','公共住宿');
        $inputRoomList = json_decode($this->post('room_list'),true);
        if (!$orderRoom = OrderRoom::create($input)){
            return getErr('数据添加失败');
        }
        if (!$inputRoomList) {
            return getErr('数据非法');
        }
        $roomList = $this->formatRoomListData(array_values($inputRoomList),$orderRoom->id);
        $orderRoomList = new OrderRoomList();
        if ($result = $orderRoomList->saveAll($roomList)){
            $return = $this->formatRoomInfo($orderId,$orderRoom,$result);
            return getSucc($return);
        }
        return getErr('新增失败！');
    }

    /**
     * 格式化返回的房型信息
     * @param $orderId
     * @param $orderRoom
     * @param $orderRoomList
     * @return array
     */
    public function formatRoomInfo($orderId,$orderRoom,$orderRoomList)
    {
        $return = $this->formatOrderRoom($orderRoom);
        $return['order_room_id'] = $orderRoom->id;
        $return['hotel_name'] = $this->getHotelName($orderId);
        $return['room_amount'] = $this->getRoomAmount($orderRoom->id);
        $return['room'] = $this->formatRoomList($orderRoomList);
        return $return;
    }

    /**
     * 通过格式化订单房间信息
     * @param $orderRoom
     * @param array $return
     * @return array
     */
    public function formatOrderRoom($orderRoom, $return = [])
    {
        $return['type'] = 'room';
        $return['state'] = $orderRoom->room_cat == '公用住宿' ?  1 : 0 ;
        $return['check_in'] = $orderRoom->check_in_time;
        $return['check_out'] = $orderRoom->check_out_time;
        $return['adult'] = ['amount'=>7,'male'=>3,'female'=>4];
        $return['child'] = ['amount'=>7,'male'=>3,'female'=>4];
        return $return;
    }

    /**
     * 通过订单id获取酒店名称
     * @param $orderId
     * @return mixed
     */
    public function getHotelName($orderId)
    {
        return Order::get($orderId)->hotel->name;
    }

    /**
     * 通过订单房间id获取房间总数
     * @param string $orderRoomId
     * @return float|int
     */
    protected function getRoomAmount($orderRoomId = '')
    {
        return OrderRoomList::where(['order_room_id'=>$orderRoomId])->count();
    }

    /**
     * 格式化房间列表信息
     * @param $roomList
     * @param array $return
     * @return array
     */
    public function formatRoomList($roomList, $return = [])
    {
        foreach ($roomList as $key => $value) {
            $return[$key]['id'] = $value->id;
            $return[$key]['room_id'] = $value->room_id;
            $return[$key]['name'] = $value->room_name;
            $return[$key]['amount'] = 1;
        }
        return $return;
    }

    /**
     * @param $roomList
     * @param $orderRoomId
     * @return mixed
     */

    public function formatRoomListData($inputRoomList,$orderRoomId)
    {
        $roomList = array();
        foreach ($inputRoomList as $key => $value) {
            $roomModel = HotelApt::get($value['id']);
            $ret['order_room_id'] = $orderRoomId;
            $ret['room_id'] = $roomModel->id;
            $ret['room_name'] = $roomModel->name;
            $ret['standard_adult'] = $roomModel->standard_adult;
            $ret['extra_adult'] = $roomModel->extra_adult;
            $ret['extra_children'] = $roomModel->extra_child;
            array_push($roomList,$ret);
            if ($value['amount'] >1){
                for ($i = 1;$i < $value['amount'];$i++){
                    $ret['room_name'] = $roomModel->name .$i;
                }
                array_push($roomList,$ret);
            }
        }
        return $roomList;
    }


    public function getBaseInfo($ret = [])
    {
        $orderId = $this->post('order_id');
        $checkInTime = strtotime($this->post('check_in','2016-11-01'));
        $checkOutTime = strtotime($this->post('check_out','2017-10-31'));
        $hotelId = Order::get($orderId)->hotel_id;
        $constractId = $this->getConstractIdByHotelId($hotelId);
        $seasonId = $this->getSeasonId($constractId,$checkInTime,$checkOutTime);
        if ($seasonId === false)
        {
            return getErr('当前出行时间下没有房间！');
        }
        $roomBaseInfo = RoomModel::where('con_id',$hotelId)->where('season_id','in',$seasonId)->select();
        foreach ($roomBaseInfo as $item => $value) {
            $ret[$item]['id'] = $value->room->id;
            $ret[$item]['room_type'] = $value->room->name;
            $ret[$item]['standard_adult'] = $value->room->standard_adult;
            $ret[$item]['extra_adult'] = $value->room->extra_adult;
            $ret[$item]['extra_child'] = $value->room->extra_child;
            $ret[$item]['infant'] = '∞';
        }
        return getSucc($ret);
    }

    public function getConstractIdByHotelId($hotelId)
    {
        return Contract::where('hotel_id', $hotelId)->value('id');
    }

    public function getSeasonId($constractId,$checkIn,$checkOut,$seasonId = '')
    {
        $date = ContractDate::where('con_id',$constractId)->where('type','season')->select();
        foreach ($date as $item) {

            if ($checkIn >= $item->check_in ||  $checkOut <= $item->check_out){
                $seasonId = $item->id;
            }
            elseif($checkIn >= $item->check_in ||  $checkOut <= $item->check_out)
            {
                $seasonId[] = $item->id;
            }
        }
        if (empty($seasonId)){

            return false;

        }
        return $seasonId;
    }


    public function modifyCheckTime()
    {
        $input['order_id'] = $this->post('order_id',1);
        $input['itin_id'] = $this->post('itin_id',1);
        $input['check_out_time'] = $this->post('check_out','1998-09-08');
        $input['check_in_time'] = $this->post('check_in','1999-09-27');
        if ($id = $this->post('order_room_id',1)){
            $result = OrderRoom::where('id',$id)->update($input);
            if ($result){
                return getSucc('更新成功！');
            }
        }else{
            if($result = OrderRoom::create($input)){
                return getSucc($result->id);
            }
        }
        return getErr('操作失败');

    }

    public function modifyItinCustInfo()
    {
        $itinId = $this->post('itin_id');
        $orderRoomId = $this->post('order_room_id');
        $orderRoomListId = $this->post('order_room_list_id');
        $idArr = json_decode($this->post('cust'),true);
        $orderRoomList = OrderRoomList::get($orderRoomListId);
        $type = $this->post('type');
        $custIdArr = array_map(function($v){return $v['id'];},$idArr);
        $res = OrderRoomCust::where(['itin_id'=>$itinId,'order_room_id'=>$orderRoomId])->where('cust_id','IN',$custIdArr)->delete();
        if ($res) {
            $result = OrderRoomCust::where(['itin_id'=>$itinId,'cost_type'=>$type, 'order_room_id'=>$orderRoomId])->select();
            $ret['persons'] = $orderRoomList->$type;
            $ret['amount'] = 0;
            $ret['cust_info'] = [];
            if ($type == 'extra_adult' || $type == 'extra_kids'){
                $ret['chosen'] = [['type'=>'adult', 'amount'=>0], ['type'=>'child', 'amount'=>0],];
            }
            if (!empty($result) || count($result) != 0) {
                $ret['amount'] = count($result);
                $ret['cust_info'] = $this->formatRoomCustInfo($result);
            }
            return getSucc($ret);
        }
        return getErr('修改失败！');
    }

}
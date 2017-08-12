<?php
namespace app\index\controller;

use app\index\model\OrderTrf;
use app\index\model\OrderItin as OrderItinModel;
use app\index\model\OrderRoom;
use app\index\model\Trf as TrfModel;
use app\index\model\Cust as CustModel;
use app\index\model\TrfBase;
use app\index\model\Cust;
use think\Request;

class OrderItin extends BaseController
{
    public function addStartCityAndCust(Request $request)
    {

        $itinId = $this->itinId = $request->post('id',1);
        $cust = $request->post('cust');
        if ($cust != 'none') {
            $cust = json_decode($cust,true);
            if (count($cust) == 0) {
                return getErr('请返回有效数值组合');
            }
            $cust = array_map(function($v)
            {
                if ($v['itin_id'] == 0) {
                    $v['itin_id'] = $v['cust_itin_id'];
                }
                unset($v['cust_itin_id']);
                return $v;
            },$cust);
            $custModel = new CustModel;
            $result = $custModel->where(['itin_id'=>$itinId])->update(['itin_id'=>0]);
            $result = $custModel->saveAll($cust);
            if (!$result) {
                return getErr('客户信息更新失败！');
            }
        }
        $input['departure'] = $request->post('start_city');
        $input['destination'] = $request->post('back_city');
        $input['cust_num'] = $count = Cust::where(['itin_id'=>$itinId])->count();
        $result = OrderItinModel::where(['id'=>$itinId])->update($input);
        if ($itin = OrderItinModel::get($itinId)) {
            return getSucc($this->getItinBaseInfo($itinId,$itin));
        }
        return getErr('当前线路不存在！');
    }
    public function getAllCustInfo($itin,$return = [])
    {
        foreach ($itin->cust as $k => $v) {
            $return[$k]['id'] = $v->id;
            $return[$k]['name'] = $v->cust_name;
            $return[$k]['sex'] = $v->gender;
            $return[$k]['age'] = $v->age;
            $return[$k]['passport'] = $v->passport_no;
        }
        return $return;
    }

    public function getItinBaseInfo($id,$itin,$ret = [])
    {
        $state = 1;
        $count = Cust::where(['itin_id'=>$id])->count();
        if ($itin->cust_num == 0 || $count != $itin->cust_num || $itin->departure == '' || $itin->destination == '')
        {
            $state = 0;
        }
        $ret['basic']['state'] = $state;
        $ret['basic']['persons'] = $itin->cust_num;
        $ret['basic']['startCity'] = $itin->departure;
        $ret['basic']['backCity'] = $itin->destination;
        $ret['basic']['userInfo'] = $this->getAllCustInfo($itin);
        return $ret;
    }

    public function getItinInfo($list = [],$ret = [])
    {
        $itinId = $this->request->post('id');
        $itin = orderItinModel::get($itinId);
        $trf = new Trf;
        $orderId = $itin->order_id;
        $orderTrf = OrderTrf::where(['itin_id'=>$itinId])->select();
        foreach ($orderTrf as $key => $value) {
            if ($value->trf_type == '单程交通') {
                $ret[] = $trf->formatSingleData(TrfModel::get($value->trf_id),$value);
            }elseif($value->trf_type == '联程交通'){
                $ret = $trf->formatConnectData(TrfBase::get($value->trf_id)->single,$value);
            }
        }
        $list = array_merge($list,$ret);
        $ret = $trfId = $roomInfo =  [];
        foreach ($list as $k => $v) {
            if (!in_array($v['trf_id'],$trfId)) {
                $ret[] = $v;
                $trfId[] = $v['trf_id'];
            }
        }
        $room = new Room();
        $return = $this->getItinBaseInfo($itinId,$itin);
        $orderRoom = OrderRoom::where('order_id',$orderId)->select();
        foreach ($orderRoom as $key => $value) {
            $roomInfo[$key] = $room->formatRoomInfo($orderId,$value,$value->roomList);
        }
        $ret = array_merge($ret,$roomInfo);
        $otherController = new Other();
        $otherInfo = $otherController->getAllOtherInfo($itinId);
        $ret = array_merge($ret,$otherInfo);
        $return['act_amount'] = $room->getActAmount($itinId);
        $activityInfo = $room->formatActivityInfo($itinId);
        if (count($activityInfo['act'])){
            array_push($ret,$activityInfo);
        }
        $return['list'] = $ret;
        return getSucc($return);
    }

    public function modifyDepartAndDest()
    {
        $itinId = $this->request->post('id');
        $input['departure'] = $this->request->post('departure');
        $input['destination'] = $this->request->post('destination');
        if ($itin = OrderItinModel::get($itinId)) {
            if ($itin->save($input)) {
                return getSucc('修改成功！');
            };
            return getErr('修改失败！');
        }
        return getErr('当前线路不存在！');
    }

    public function query(Request $request)
    {
        $itinId = $request->param('itin_id',1);
        $trfModel = new trf();
        $trfItin = new TrfItinModel();
        $result = $trfItin->getAllByitinId($itinId);
        $public = $single = [];
        foreach ($result as $key => $value) {
            if ($value->trf_cat == '公共交通') {
                if ($value->trf_type == '联程交通') {

                    $public[] = TrfBase::get($value->trf_id)->single;

                }else{

                    $public[] = $trfModel->get($value->trf_id);
                }
            }else{
                if ($value->trf_type == '联程交通') {

                    $single[] = TrfBase::get($value->trf_id)->single;

                }else{

                    $single[] = $trfModel->get($value->trf_id);
                }
            }
        }
        $this->assign('public', $public);
        $this->assign('single', $single);
        return $this->fetch();
    }
}

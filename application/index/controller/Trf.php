<?php
namespace app\index\controller;

use app\index\model\Trf as TrfModel;
use app\index\model\TrfBase;
use app\index\model\OrderTrf;
use app\index\model\OrderItin as OrderItinModel;
use app\index\model\OrderTrfCust;
use app\index\model\Cust;
use app\index\model\TrfRoute;
use think\Request;
class Trf extends BaseController
{

    public function modifyTrfSupply()
    {
        $supplyArr = ['不代订','代订'];
        $orderTrfId = $this->post('order_trf_id');
        $supply = $this->post('supply','代订');
        if ($orderTrfId && in_array($supply,$supplyArr)){
            $result = OrderTrf::update(['id'=>$orderTrfId,'trf_supply'=>$supply]);
            if ($result) {
                return getSucc(['switch' => array_search($result->trf_supply,$supplyArr)]);
            }
        }
        return getErr('修改失败！');
    }
    
    public function priceOrderTrf($cost = '',$ret = [])
    {
        $type = $this->post('type',0);
        if ($type == 1) {
            $orderTrfId = $this->post('id');
            $orderTrfModel = OrderTrf::get($orderTrfId);
            $trfId = $orderTrfModel -> trf_id;
            $trfModel = TrfModel::get($trfId);
            $baseModel = $trfModel -> single;
            $input['id'] = $orderTrfId;
            $input['adult_price'] = $this->post('adult_price','401.00');
            $input['kids_price'] = $this->post('child_price','400.00');
            $input['baby_price'] = $this->post('infant_price','400.00');
            $input['trf_amount'] = $this->post('trf_amount');
            $trfAllocation = json_decode($this->post('trf_arrange'),true);
            foreach ($trfAllocation as $key => $value) {
                $cost += $input['adult_price']*$value['value'];
                $bill[] = $input['adult_price'].'*'.$value['value'] . '=' . $input['adult_price']*$value['value'];
            }
            $input['bill'] = json_encode($bill);
            $input['cost'] = $cost;
            $input['is_write'] = 1;
            $input['trf_allocation'] = json_encode(array_map(function($v){return $v['value'];},$trfAllocation));
            $res = OrderTrf::get($orderTrfId)->update($input);
            if ($res) {
                $ret = array_map(function($v){
                    return ['message'=>$v];
                }, $res->bill);
                return getSucc($ret);
            }
            return getErr('计价失败');
        }
        elseif($type == 0)
        {
            $data = $this->post('cabin');
            $data = json_decode($data,true);
            foreach ($data as $k => $v) {
                $input[$k]['space_name'] = $v['position'];
                $input[$k]['adult_price'] = $v['adult_price'];
                $input[$k]['kids_price'] = $v['child_price'];
                $input[$k]['baby_price'] = $v['infant_price'];
                $input[$k]['id'] = $v['id'];
                $input[$k]['is_write'] = 1;
                $count = OrderTrfCust::where(['order_traffic_id'=>$v['id']])->count();
                $cost += $count * $v['adult_price'];
                $bill['cabin'] =  $v['position'];
                $bill['message'] =  $count .'*'.$v['adult_price'].'='.$count * $v['adult_price'];
                $input[$k]['cost'] =  $cost;
                $input[$k]['bill'] = json_encode($bill);
            }
            $orderTrf = new orderTrf();
            $res = $orderTrf->saveAll($input);
            if ($res) {
                foreach ($res as $key => $value) {
                    $ret[$key]['cabin'] = $value->bill['cabin'];
                    $ret[$key]['message'] = $value->bill['message'];
                }
                return getSucc($ret);
            }

        }
    }

    /**
     * 通过交通基础id以及客户id获取当前交通年龄阶段
     * @param string $custId
     * @param string $baseId
     * @return bool|string
     */
    public function getAgeGrades($custId = '', $baseId = '')
    {
        $custId = $this->post('cust_id',$custId);
        $baseId = $this->post('base_id',$baseId);
        $custModel = Cust::get($custId);
        $trfBaseModel = TrfBase::get($baseId);
        $age = $custModel->age;
        if ($age > $trfBaseModel->meddle_age){
            $ageGrades = '成人';
        }elseif($age < $trfBaseModel->meddle_age && $age > $trfBaseModel->small_age){
            $ageGrades = '儿童';
        }elseif($age > $trfBaseModel->min_age && $age < $trfBaseModel->small_age){
            $ageGrades = '婴儿';
        }else{
            return false;
        }
        return $ageGrades;
    }

    public function addOrderTrf()
    {
        $orderTrfId = $this->post('order_trf_id',1);
        $newOrder = orderTrf::get($orderTrfId)->hidden(['id','bill','create_time','modify_time'])->toArray();
        if ($data = orderTrf::create($newOrder)) {
            return getSucc($data->id);
        };
        return getErr('创建失败');
    }

    public function delOrderTrf()
    {
        $orderTrfId = $this->post('order_trf_id',1);
        if ($orderTrf = OrderTrf::get($orderTrfId)) {
            $orderTrf->delete();
            OrderTrfCust::where(['order_traffic_id'=>$orderTrfId])->delete();
            return getSucc('仓位删除成功！');
        };
        return getErr('当前交通不存在！');

    }

    public function getFreeCustInfo($ret = [])
    {
        $itinId = $this->post('itin_id',1);
        $idColumn = OrderTrfCust::where(['itin_id'=>$itinId])->column('cust_id');
        if (empty($idColumn)) {
            $custInfo = Cust::where(['itin_id'=>$itinId])->select();;
        }else{
            $custInfo = Cust::where(['itin_id'=>$itinId])->where('id','NOT IN',$idColumn)->select();
        }
            foreach ($custInfo as $key => $value) {
                $ret[$key]['id'] = $value->id;
                $ret[$key]['name'] = $value->cust_name;
                $ret[$key]['age'] = $value->age;
                $ret[$key]['sex'] = $value->gender;
                $ret[$key]['passport'] = $value->passport_no;
            }

        return getSucc($ret);
    }

    public function modifyItinCustInfo()
    {
        $itinId = $this->post('itin_id');
        $orderTrfId = $this->post('space_id');
        $idArr = json_decode($this->post('cust'),true);
        $custIdArr = array_map(function($v){return $v['id'];},$idArr);
        $res = OrderTrfCust::where(['itin_id'=>$itinId,'order_traffic_id'=>$orderTrfId])
            ->where('cust_id','IN',$custIdArr)
            ->delete();
        if ($res) {
            $result = OrderTrfCust::where(['itin_id'=>$itinId,'order_traffic_id'=>$orderTrfId])->select();
            $info = [];
            if (!empty($result)) {
                foreach ($result as $k => $v)
                {
                    $info[$k]['id'] = $v->cust->id;
                    $info[$k]['name'] = $v->cust->name;
                    $info[$k]['sex'] = $v->cust->gender;
                    $info[$k]['age'] = $v->cust->age;
                    $info[$k]['passport'] = $v->cust->passport_no;
                }
            }
            return getSucc($info);
        }
        return getErr('修改失败！');
    }

    public function addItinCustInfo()
    {
        $orderId = $this->post('order_id');
        $itinId = $this->post('itin_id');
        $orderTrfId = $this->post('space_id');
        $trfId = $this->post('trf_id');
        $baseId = TrfModel::get($trfId)->base_id;
        $custIdArr = json_decode($this->post('cust'),true);
        foreach ($custIdArr as $key => $value) {
            $data[$key]['order_id'] = $orderId;
            $data[$key]['itin_id'] = $itinId;
            $data[$key]['trf_id'] = $trfId;
            $data[$key]['cust_id'] = $value['id'];
            $data[$key]['order_traffic_id'] = $orderTrfId;
            if ($ageGrades = $this->getAgeGrades($value['id'],$baseId)){
                return getErr('该客户年龄过小，不适合该交通');
            }else{
                $data[$key]['age_grades'] = $ageGrades;
            }
        }
        $orderTrfCust = new OrderTrfCust();
        $result = $orderTrfCust->saveAll($data);
        if ($result) {
            $result = OrderTrfCust::where(['order_traffic_id'=>$orderTrfId])->select();
            $ret = [];
            foreach ($result as $k => $v) {
                $ret[$k]['id'] = $v->id;
                $ret[$k]['name'] = $v->cust->cust_name;
                $ret[$k]['age'] = $v->cust->age;
                $ret[$k]['sex'] = $v->cust->gender;
                $ret[$k]['passport'] = $v->cust->passport_no;
            }
            return getSucc($ret);
        }
        return getErr('修改失败！');
    }

    public function getPriceInfo()
    {
        $orderTrfId = $this->request->post('id');
        $itinId = $this->request->post('itin_id');
        $trfId = $this->request->post('trf_id');
        $baseId = $this->request->post('base_id');
        $trfBaseModel = TrfBase::get($baseId);
        $pricingNum = $trfBaseModel->std_num;
        $orderItinModel = OrderItinModel::get($itinId);
        $orderTrfModel = OrderTrf::get($orderTrfId);
        $orderTrf = new OrderTrf();
        $ret = [];
        $result = OrderTrf::where(['itin_id'=>$itinId,'trf_id'=>$trfId])->select();
        if ($pricingNum == 1)
        {
            foreach ($result as $key => $value) {
                $info = [];
                $ret['type'] = 0;
                $res = OrderTrfCust::where(['order_traffic_id'=>$value->id])->select();
                if (!empty($res))
                {
                    foreach ($res as $k => $v)
                    {
                        $info[$k]['id'] = $v->cust->id;
                        $info[$k]['name'] = $v->cust->name;
                        $info[$k]['sex'] = $v->cust->gender;
                        $info[$k]['age'] = $v->cust->age;
                        $info[$k]['passport'] = $v->cust->passport_no;
                    }
                }
                if ($value->is_write == '否')
                {
                    $ret['list'][$key]['id'] = $value->id;
                    $ret['list'][$key]['position'] = '';
                    $ret['list'][$key]['amount'] = 0;
                    $ret['list'][$key]['adult'] = $trfBaseModel->adult_price;
                    $ret['list'][$key]['adult_diff'] = 0;
                    $ret['list'][$key]['child'] = $trfBaseModel->kids_price;
                    $ret['list'][$key]['child_diff'] = 0;
                    $ret['list'][$key]['infant'] = $trfBaseModel->baby_price;
                    $ret['list'][$key]['infant_diff'] = 0;
                    $ret['list'][$key]['cust_info'] = $info;
                    $ret['price_info'] = [];
                }
                else
                {
                    $ret['list'][$key]['id'] = $value->id;
                    $ret['list'][$key]['position'] = $value->space_name;
                    $ret['list'][$key]['amount'] = count($res);
                    $ret['list'][$key]['adult'] = $value->adult_price;
                    $ret['list'][$key]['adult_diff'] = 0;
                    $ret['list'][$key]['child'] = $value->kids_price;
                    $ret['list'][$key]['child_diff'] = 0;
                    $ret['list'][$key]['infant'] = $value->baby_price;
                    $ret['list'][$key]['infant_diff'] = 0;
                    $ret['list'][$key]['cust_info'] = $info;
                    $ret['price_info'][$key]['cabin'] = $value->bill['cabin'];
                    $ret['price_info'][$key]['message'] = $value->bill['message'];
                }
            }
        }
        else
        {
            foreach ($result as $key => $value) {
                $ret['type'] = 1;
                $res = OrderTrfCust::where(['order_traffic_id'=>$value->id])->select();
                if ($value->is_write == '否')
                {
                    $ret['id'] = $value->id;
                    $ret['persons'] = $pricingNum;
                    $ret['amount'] = $orderItinModel->cust_num;
                    $ret['adult'] = $trfBaseModel->adult_price;
                    $ret['adult_diff'] = $trfBaseModel->adult_diff_new;
                    $ret['child'] = $trfBaseModel->kids_price;
                    $ret['child_diff'] = $trfBaseModel->kids_diff_new;
                    $ret['infant'] = $trfBaseModel->baby_price;
                    $ret['infant_diff'] = $trfBaseModel->baby_diff_new;
                    $ret['trf_amount'] = 0;
                    $ret['trf_arrange'] = [];
                    $ret['price_info'] = [];
                }
                else
                {
                    $ret['id'] = $value->id;
                    $ret['persons'] = $pricingNum;
                    $ret['amount'] = $orderItinModel->cust_num;
                    $ret['adult'] = $value->adult_price;
                    $ret['adult_diff'] = $trfBaseModel->adult_diff_new;
                    $ret['child'] = $value->kids_price;
                    $ret['child_diff'] = $trfBaseModel->kids_diff_new;
                    $ret['infant'] = $value->baby_price;
                    $ret['infant_diff'] = $trfBaseModel->baby_diff_new;
                    $ret['trf_amount'] = $value->trf_amount;
                    $ret['trf_arrange'] = $value->trf_allocation_new;
                    foreach ($value->bill as $key => $value) {
                        $priceInfo[$key]['message'] = $value;
                    }
                    $ret['price_info'] = $priceInfo;
                }
            }
        }
        return getSucc($ret);
    }

    public function query(Request $request)
    {   $ret = [];
        $type = $request->post('type');
        $input['week'] = Date::getWeek($request->post('week'));
        $trfModel = new TrfModel;
        if ($type == '联程交通') {
            $data =  $trfModel->getConnectData($input);//获取联程信息
            if ($ret = $this->formatQueryConnectData($data)) {
                return getSucc($ret);
            }
            return getErr('当前没有数据！');
        }
        elseif ($type == '定期交通')
        {
            $input['regular'] = '定期交通';
            $input['method'] = $request->post('method');
            $data =  $trfModel->getSingData($input);//获取单程信息
            if ($ret = $this->formatQuerySingleData($data)) {
                return getSucc($ret);
            }
            return getErr('当前没有数据！');
        }
        elseif($type == '接驳交通')
        {
            $input['regular'] = '接驳交通';
            $input['method'] = $request->post('method');
            $data =  $trfModel->getSingData($input);//获取单程信息
            if ($ret = $this->formatQuerySingleData($data)) {
                return getSucc($ret);
            }
            return getErr('当前没有数据！');
        }elseif($type == '模板')
        {
            $input['week'] = Date::getWeek($input['week']);
            $input['city_name'] = $request->post('start_city');
            $data =  $trfModel->getRouteData($input);//获取单程信息
            foreach ($data as $key => $value) {
                $ret[$key]['id'] = $value->id;
                $ret[$key]['waste_day'] = $value->waste_day;
                $ret[$key]['waste_night'] = $value->waste_night;
                $ret[$key]['content'] = $value->content_new;
            }
            return getSucc($ret);
        }
    }

    public function formatQueryConnectData($data,$ret = [])
    {
        foreach ($data as $key => $value) {
            foreach ($value as $k => $v) {
                $ret[$key]['type'] = '联程交通';
                $ret[$key]['id'] = $v->id;
                $ret[$key]['base_id'] = $v->base_id;
                $ret[$key]['list'][$k]['type'] = $v->regular;
                $ret[$key]['list'][$k]['methods'] = $v->method;
                $ret[$key]['list'][$k]['number'] = $v->num;
                $ret[$key]['list'][$k]['startName'] = $v->start_city.''.$v->start_name;
                $ret[$key]['list'][$k]['endName'] = $v->end_city.''.$v->end_name;
                $ret[$key]['list'][$k]['startEname'] = $v->start_ename;
                $ret[$key]['list'][$k]['endEname'] = $v->end_ename;
                $ret[$key]['list'][$k]['inTime'] = $v->in_time."(".$v->in_time_day.")";
                $ret[$key]['list'][$k]['startTime'] = $v->start_time;
                $ret[$key]['list'][$k]['endTime'] = $v->end_time."(".$v->end_time_day.")";
                $ret[$key]['list'][$k]['outTime'] = $v->out_time."(".$v->out_time_day.")";
                $ret[$key]['list'][$k]['wasteIn'] = $v->waste_in;
                $ret[$key]['list'][$k]['waste'] = $v->waste;
                $ret[$key]['list'][$k]['wasteOut'] = $v->wasteOut;
            }
        }
        return $ret;
    }

    public function formatQuerySingleData($data, $ret = [])
    {
        foreach ($data as $k => $v) {
            $ret[$k]['id'] = $v->id;
            $ret[$k]['base_id'] = $v->base_id;
            $ret[$k]['type'] = $v->regular;
            $ret[$k]['methods'] = $v->method;
            $ret[$k]['number'] = $v->num;
            $ret[$k]['startName'] = $v->start_city.''.$v->start_name;
            $ret[$k]['endName'] = $v->end_city.''.$v->end_name;
            $ret[$k]['startEname'] = $v->start_ename;
            $ret[$k]['endEname'] = $v->end_ename;
            $ret[$k]['inTime'] = $v->in_time."(".$v->in_time_day.")";
            $ret[$k]['startTime'] = $v->start_time;
            $ret[$k]['endTime'] = $v->end_time."(".$v->end_time_day.")";
            $ret[$k]['outTime'] = $v->out_time."(".$v->out_time_day.")";
            $ret[$k]['wasteIn'] = $v->waste_in;
            $ret[$k]['waste'] = $v->waste;
            $ret[$k]['wasteOut'] = $v->wasteOut;
        }
        return $ret;
    }

    public function formatSingleData($trf,$orderTrf,$ret = [])
    {
        $ret['type'] = $trf->regular;
        $ret['id'] = $orderTrf->id;
        $ret['trf_id'] = $trf->id;
        $ret['base_id'] = $trf->base_id;
        $ret['state'] = $orderTrf->trf_cat;
        $ret['switch'] = $orderTrf->trf_supply_new;
        $ret['regular']['start'] = $trf->start_city;
        $ret['regular']['end'] = $trf->end_city;
        $ret['regular']['startName'] = $trf->start_name;
        $ret['regular']['endName'] =  $trf->end_name;
        $ret['regular']['method'] = $trf->method;
        $ret['regular']['trafficType'] =  $trf->regular;
        $ret['regular']['number'] =  $trf->num;
        $ret['regular']['inTime'] =  $trf->in_time;
        $ret['regular']['outTime'] =  $trf->out_time;
        $ret['regular']['startTime'] =  $trf->start_time;
        $ret['regular']['endTime'] =  $trf->end_time;
        $ret['regular']['state'] = $orderTrf->is_write == '是' ? '已完善' : '待完善';
        $custNum = OrderItinModel::get($orderTrf->itin_id)->cust_num;
        $ret['regular']['cotain'] =  $orderTrf->trf_supply;
        return $ret;
    }

    public function formatConnectData($trf,$orderTrf,$ret = [])
    {
        foreach ($trf as $k => $v) {
            $ret[$k] = $this->formatSingleData($v,$orderTrf);
        }
        return $ret;
    }
    public function add(Request $request)
    {
        $input['trf_id'] = $trfId = $request->post('trf_id');
        $input['itin_id'] = $request->post('itin_id');
        $input['order_id'] = $request->post('order_id');
        $input['trf_type'] = $type = $request->post('type','联程交通');
        $input['trf_cat'] = $request->post('cat','独立交通');
        $input['is_write'] = '否';
        if ($type == '联程交通') {
            $input['trf_supply'] = $this->shortSupply(TrfModel::getSupplyBybaseId($trfId));
            if ($trf = OrderTrf::create($input)){
                return getSucc($this->formatConnectData(TrfBase::get($trfId)->single,$trf));
            }
        }elseif($type == '模板'){
            $trfRoute = TrfRoute::get($trfId);
            foreach ($trfRoute->route as $k => $v) {
                if ($v->name == '联程') {
                    $input['trf_type'] = '联程交通';
                    $input['trf_supply'] = $this->shortSupply(TrfModel::getSupplyBybaseId($v->id));
                    $input['trf_id'] = $v->id;
                    if ($trf = OrderTrf::create($input)){
                        $ret[] =  $this->formatConnectData(TrfBase::get($v->id)->single,$trf);
                    }else{
                        return $this->getErr('新增失败');
                    }
                }elseif($v->name == '单程'){
                    $input['trf_type'] = '单程交通';
                    $input['trf_supply'] = $this->shortSupply(TrfModel::getSupplyById($v->id));
                    $input['trf_id'] = $v->id;
                    if ($trf = OrderTrf::create($input)){
                        $ret[] =  $this->formatSingleData(TrfModel::get($v->id),$trf);
                    }else{
                        return $this->getErr('新增失败');
                    }
                }
            }
            return $this->getSucc($ret);
        }elseif($type == '定期交通' || $type == '接驳交通'){
            $input['trf_type'] = '单程交通';
            $input['trf_supply'] = $this->shortSupply(trfModel::getSupplyById($trfId));
            if ($trf = OrderTrf::create($input)){
                return getSucc($this->formatSingleData(TrfModel::get($trfId),$trf));
            }
        }
        return $this->getErr('新增失败');


    }

    public function shortSupply($supply)
    {
        switch ($supply) {
            case '通过酒店代订':
                return '代订';
            case '通过供应商代订':
                return '代订';
            case '客人自理':
                return '自理';
                break;
        }
    }
}

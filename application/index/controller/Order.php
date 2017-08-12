<?php
namespace app\index\controller;

use app\index\model\OrderItin;
use app\index\model\Order as OrderModel;
use app\index\model\Hotel as HotelModel;
use app\index\model\OrderItin as ItinModel;
use think\Request;

class Order extends BaseController
{

    public function getAllStartCity()
    {
        $orderId = $this->request->post('id');
        $hotelId = OrderModel::get($orderId)->hotel_id;
        $hotel = HotelModel::get($hotelId);
        if (count($hotel->start_city) >0){
            foreach ($hotel->start_city as $key => $value) {
                $return[$key]['label'] = $value;
                $return[$key]['value'] = $value;
            }
        }else{
            return getErr('当前不存在出发地城市!');
        }
        return getSucc($return);
    }

    public function index()
    {
        if ($empSn = $this->request->post('id')) {
            if ($return = OrderModel::all(['emp_sn' => $empSn])) {
                foreach ($return as $k => $v) {
                    $ret[$k]['id'] = $v->id;
                    $ret[$k]['dest'] = $v->dest_country . '/' . $v->dest_islands;
                    $ret[$k]['number'] = $v->inside_sn;
                    $ret[$k]['date'] = $v->start_date . '/' . $v->end_date;
                    $ret[$k]['name'] = $v->emp->emp_cn_name;
                    $ret[$k]['amount'] = $v->cust_num;
                }
                return getSucc($ret);
            }
        }
        return getErr('数据非法！');
    }

    public function getAllCustInfo()
    {
        $id = $this->request->post('id', 1);
        foreach (OrderModel::get($id)->cust as $k => $v) {
            $return[$k]['id'] = $v->id;
            $return[$k]['name'] = $v->cust_name;
            $return[$k]['sex'] = $v->gender;
            $return[$k]['age'] = $v->age;
            $return[$k]['passport'] = $v->passport_no;
            $return[$k]['itin_id'] = $v->itin_id;
            $return[$k]['itin_name'] = $v->itin_id != 0 ? ItinModel::get($v->itin_id)->itin_name : '';
        }
        $data = uasort($return, function ($a, $b) {
            if ($a['itin_id'] == $b['itin_id']) {
                return 0;
            }
            return ($a['itin_id'] > $b['itin_id']) ? -1 : 1;
        });
        return getSucc(array_values($return));
    }

    public function modifyCustNum($id = null, $type = 'add')
    {
        $request = $this->request;
        $type = $request->post('type', $type);
        if ($id = $request->post('id', $id) && $order = OrderModel::get($id)) {
            switch ($type) {
                case 'add':
                    $order->cust_num++;
                    break;
                case 'reduce':
                    if ($order->cust_num >= 1) {
                        $order->cust_num--;
                    } else {
                        return getErr('客户人数为0');
                    }
                    break;
            }
            if ($order->save()) {
                return getSucc('订单人数修改成功');
            };

            return getErr('订单人数修改失败');
        }
        return getErr('当前订单不存在！');
    }

    public function modifyOrderDate(Request $request)
    {
        $orderId = $request->post('id');
        $startDate = $request->post('set_off_date');
        $endDate = $request->post('back_date');
        if ($orderModel = OrderModel::get($orderId)) {
            $orderModel->itinerary_days = Date::getDays($startDate, $endDate);
            $orderModel->order_name = $orderModel->dest_country . '-' . $orderModel->dest_islands . '-' . $orderModel->itinerary_days . '天行程';
            $orderModel->start_date = $startDate;
            $orderModel->end_date = $endDate;
            if (!$orderModel->save()) {
                return getErr('日期修改失败！');
            }
            $ret['days'] = $orderModel->itinerary_days;
            $date = Date::getDateFromRange($startDate, $endDate);
            foreach ($date as $k => $v) {
                $ret['date'][] = Date::getWeekAndDay($v);
            }
            return getSucc($ret);

        };

        return getErr('当前订单不存在');

    }

    public function modifyRepInfo(Request $request)
    {
        $id = $request->post('id');
        $data['cust_rep_name'] = $request->post('name');
        $data['cust_rep_phone'] = $request->post('phone');
        if (OrderModel::where(['id' => $id])->update($data)) {
            return getSucc('修改成功！');
        }
        return getErr('修改失败');
    }

    public function createOrder(Request $request)
    {
        $data['emp_sn'] = $request->post('emp_sn');
        $data['order_type'] = $request->post('type_value');
        $data['order_source'] = $request->post('source_value');
        $data['dest_country'] = $request->post('country_name');
        $data['dest_islands'] = $request->post('islands_name');
        $data['hotel_id'] = $request->post('hotel_id');
        $data['dest_hotel'] = $request->post('hotel_name');
        $data['start_date'] = $request->post('set_off_date');
        $data['end_date'] = $request->post('back_date');
        $data['itinerary_days'] = Date::getDays($data['start_date'], $data['end_date']);
        $data['cust_num'] = $request->post('amount');
        $data['itinerary_count'] = $request->post('route_amount');
        $data['inside_sn'] = $this->getInsideSn($request->post('country_id'), $request->post('islands_id'), $data['order_type'], $data['order_source']);
        if ($order = OrderModel::create($data)) {
            for ($i = 0; $i < $data['itinerary_count']; $i++) {
                $input[$i]['order_id'] = $order->id;
                $input[$i]['itin_name'] = '线路' . ($i + 1);
            }
            $orderItin = new OrderItin();
            $orderItin->saveAll($input);
            return getSucc($order->id);

        } else {

            return getSucc('新增失败！');
        }
    }

    public function getOrderInfo(Request $request)
    {
        $id = $request->post('id');
        if ($order = OrderModel::get($id)) {
            $date = Date::getDateFromRange($order->start_date, $order->end_date);
            foreach ($date as $k => $v) {
                $return['date'][] = Date::getWeekAndDay($v);
            }
            $cust = new Cust;
            $return['hotel_id'] = $order->hotel_id;
            $return['country'] = $order->dest_country;
            $return['dest'] = $order->dest_islands;
            $return['days'] = $order->itinerary_days;
            $return['persons'] = $order->cust_num;
            $return['message'] = $cust->checkCustInfo($id);
            $return['contact'] = $order->cust_rep_name;
            $return['phone'] = $order->cust_rep_phone;
            foreach ($order->itin as $v) {
                $return['route'][]['id'] = $v->id;
            }
            return getSucc($return);
        }
        return getErr('当前订单不存在！');
    }

    public function getInsideSn($countryId, $islandsId, $type, $source)
    {
        date_default_timezone_set('PRC');
        $count = OrderModel::where('create_time', 'like', date('Y-m-d') . '%')->count();
        $countryId = Data::padZero($countryId, 3);
        $islandsId = Data::padZero($islandsId, 3);
        $count = Data::padZero($count++, 4);
        return $this->getType($type) . $this->getType($source) . $countryId . $islandsId . date('ymd') . $count;
    }

    public function getType($type)
    {
        switch ($type) {
            case '散单':
                return 'A';
            case '团单':
                return 'B';
            case 'OTA':
                return 'C';
            case '市场':
                return 'M';
            case '直客':
                return 'D';
        }
    }

}

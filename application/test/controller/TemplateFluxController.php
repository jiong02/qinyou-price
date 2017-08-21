<?php
namespace app\test\controller;
use app\test\model\CountrySortModel;
use app\test\model\PlaceSortModel;
use app\test\model\RoutePvFluxModel;
use app\test\model\RouteUvFluxModel;
use think\Request;
use app\test\model\TemplateRouteModel;
use app\test\model\OrderModel;


class TemplateFluxController extends BaseController
{
    /**
     * @name 添加数据
     * @auth Sam
     * @return string
     */
    public function addTestPvFlux()
    {
        $tempArr[] = 1;
        $tempArr[] = 2;
        $tempArr[] = 3;

        $routeArr[] = 1;
        $routeArr[] = 2;
        $routeArr[] = 3;
        $routeArr[] = 4;
        $routeArr[] = 5;
        $routeArr[] = 6;

        for($i=0;$i<7;$i++){
            $tempRand = rand(0,2);
            $routeRand = rand(0,5);

            $temp = $tempArr[$tempRand];
            $route = $routeArr[$routeRand];

            $pvFluxModel = new RoutePvFluxModel();

            $pvFluxModel->template_id = $temp;
            $pvFluxModel->route_id = $route;
            $pvFluxModel->click_time = date('Y-m-d H:i:s',mktime(21,30,24,rand(1,12),rand(1,30),2017));

            $pvFluxModel->save();
        }

        return '添加成功';
    }

    /**
     * @name 点击线路记录流量
     * @auth Sam
     * @param Request $request
     * @return bool
     */
    public function addPvUvFlux(Request $request)
    {
        $tempId = $request->param('temp_id',0);
        $routeId = $request->param('route_id',0);
        $tmpRouteId = $request->param('temp_route_id',0);
        $ip = $request->ip();
        $hisTime = date('Y-m-d H:i:s',time());
        $dateTime = date('Y-m-d',time());

        if(empty($tempId) || !is_numeric($tempId)){
            return false;
        }

        if(empty($tmpRouteId) || !is_numeric($tmpRouteId)){
            return false;
        }

        if(empty($routeId) || !is_numeric($routeId)){
            return false;
        }

        if(empty($ip)){
            return false;
        }

        $pvModel = new RoutePvFluxModel();
        $uvModel = new RouteUvFluxModel();

        $pvModel->template_id = $tempId;
        $pvModel->route_id = $routeId;
        $pvModel->ip = $ip;
        $pvModel->click_time = $hisTime;
        $pvModel->template_route_id = $tmpRouteId;

        $pvModel->save();

        $uvInfo = $uvModel->where(['template_id'=>$tempId,'route_id'=>$routeId,'ip'=>$ip])->where("click_time like '$dateTime%'")->find();

        if(empty($uvInfo)){
            $uvModel->template_id = $tempId;
            $uvModel->route_id = $routeId;
            $uvModel->ip = $ip;
            $uvModel->click_time = $hisTime;
            $uvModel->template_route_id = $tmpRouteId;

            $uvModel->save();
        }

    }




    /**
     * @name 获取模板信息
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function getTempInfo(Request $request)
    {
        $timeStart = $request->param('start_time','');
        $timeEnd = $request->param('end_time','');
        $tempId = $request->param('temp_id',0);

        if(empty($tempId) || !is_numeric($tempId)){
            return '数据不完整';
        }

        //指定时间段数据
        if(!empty($timeStart)){
            $returnData = $this->getChooseTimeScopeTemp($timeStart,$timeEnd,$tempId);

        }else{//一周内时间数据
            $returnData = $this->getTodayToLastweekTemp($tempId);

        }

        return $returnData;

    }

    /**
     * @name 获取一周内模板数据
     * @return string
     */
    public function getTodayToLastweekTemp($tempId)
    {
        header("Content-type: text/html; charset=utf-8");
        $nowTime = time();
        $nowTime = mktime(23,59,59,date('m',$nowTime),date('d',$nowTime),date('Y',$nowTime));

        //获取模板下的线路信息
        $tmpRouteModel = new TemplateRouteModel();

        $tmpRouteList = $tmpRouteModel->where('temp_id',$tempId)->select();
        $tmpRouteStr = '';

        if(!empty($tmpRouteList)){
            foreach($tmpRouteList as $k=>$v){
                $tmpRouteStr .= $v['id'].',';
            }

            $tmpRouteStr = trim($tmpRouteStr,',');
        }else{
            $tmpRouteStr = 0;
        }

        $pvModel = new RoutePvFluxModel();
        $uvModel = new RouteUvFluxModel();
        $orderModel = new OrderModel();

        $pvInfo = array();
        $uvInfo = array();

        for($i=0;$i<7;$i++){
            if($i == 0){
                $timeStart = $nowTime;
                $timeEnd = strtotime('- 1 day',$nowTime);
                $timeEnd = strtotime('+ 1 second',$timeEnd);

            }else{
                $timeStart = strtotime('- 1 second',$timeEnd);
                $timeEnd = strtotime('- 1 day',$timeStart);
                $timeEnd = strtotime('+ 1 second',$timeEnd);

            }

            $ymdTimeStart = date('Y-m-d H:i:s',$timeStart);
            $ymdTimeEnd = date('Y-m-d H:i:s',$timeEnd);
            $dateTimeEnd = date('Y-m-d',$timeEnd);

            $pvCount = $pvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();

            $pvInfo['y'][] = $pvCount;

            //PV 模板 点击量
            $pvTmpClick = $pvModel->field('count(*) as count')->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->find();

            $pvInfo['temp_click_count'][] = $pvTmpClick['count'];
            $uvInfo['temp_click_count'][] = $pvTmpClick['count'];

            //PV 模板 订单数
            $pvTmpOrder = $orderModel->field('count(*) as count')->where("temp_id = $tempId AND create_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->find();

            $pvInfo['temp_order_count'][] = $pvTmpOrder['count'];
            $uvInfo['temp_order_count'][] = $pvTmpOrder['count'];

            //PV 支付数
            $pvTmpOrderOk = $orderModel->field('count(*) as count')->where("temp_id = $tempId AND create_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart' AND order_status >= 3")->find();
            $pvInfo['temp_create_order'][] = $pvTmpOrderOk['count'];
            $uvInfo['temp_create_order'][] = $pvTmpOrderOk['count'];

            //PV 转化率
            if(empty($pvTmpOrderOk['count']) && empty($pvCount)){
                $change = 0;
            }else{
                if(empty($pvCount)){
                    $pvCount = 1;
                }

                if(empty($pvTmpOrderOk['count'])){
                    $change = 0;
                }else{
                    $change = round(($pvTmpOrderOk['count'] / $pvCount) * 100);
                }

            }

            $pvInfo['change'][] = $change;

            $pvInfo['x'][] = $dateTimeEnd;

            $uvCount = $uvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();

            $uvInfo['y'][] = $uvCount;

            //UV 模板点击量
            $uvTmpClick = $uvModel->field('count(*) as count')->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->find();

            $uvInfo['temp_click_count'][] = $uvTmpClick['count'];

            $uvTmpClick['count'] = 0;

            //UV 转化率
            if(empty($pvTmpOrderOk['count']) && empty($uvCount)){
                $change = 0;
            }else{
                if(empty($uvCount)){
                    $uvCount = 1;
                }

                if(empty($pvTmpOrderOk['count'])){
                    $change = 0;
                }else{
                    $change = round(($pvTmpOrderOk['count'] / $uvCount) * 100);
                }

            }

            $uvInfo['change'][] = $change;

            $uvInfo['x'][] = $dateTimeEnd;

            $pvCount = 0;
            $pvTmpClick['count'] = 0;
            $pvTmpOrder['count'] = 0;
            $pvTmpOrderOk['count'] = 0;
            $chage = 0;
        }

        $pvInfo['x'] = array_reverse($pvInfo['x']);
        $pvInfo['y'] = array_reverse($pvInfo['y']);

        $uvInfo['x'] = array_reverse($uvInfo['x']);
        $uvInfo['y'] = array_reverse($uvInfo['y']);

        //PV模板 点击量总数
        $pvInfo['temp_click_count'] = array_reverse($pvInfo['temp_click_count']);
        //PV模板订单数
        $pvInfo['temp_order_count'] = array_reverse($pvInfo['temp_order_count']);
        //PV模板 下单书
        $pvInfo['temp_create_order'] = array_reverse($pvInfo['temp_create_order']);
        //PV模板转化率
        $pvInfo['change'] = array_reverse($pvInfo['change']);

        //UV模板 点击量总数
        $uvInfo['temp_click_count'] = array_reverse($uvInfo['temp_click_count']);
        //UV模板订单数
        $uvInfo['temp_order_count'] = array_reverse($uvInfo['temp_order_count']);
        //UV模板 下单书
        $uvInfo['temp_create_order'] = array_reverse($uvInfo['temp_create_order']);
        //PV模板转化率
        $uvInfo['change'] = array_reverse($uvInfo['change']);

        $returnData['pv_data'] = $pvInfo;
        $returnData['uv_data'] = $uvInfo;

        return $returnData;
    }

    /**
     * @name 获取时间段内模板数据
     * @param $start
     * @param $end
     * @return string
     */
    public function getChooseTimeScopeTemp($start,$end,$tempId)
    {
        $start = strtotime($start);
        $end = strtotime($end);

        if($start > $end){
            return '请输入正确的时间';
        }

        $yStart = date('Y',$start);
        $yEnd = date('Y',$end);

        $zStart = date('z',$start);
        $zEnd = date('z',$end);

        //同一年
        if($yStart == $yEnd){
            $forNumber = abs($zEnd - $zStart);
        }else{//不同年份
            return '日期请选择在同一年内';
        }

        $pvModel = new RoutePvFluxModel();
        $uvModel = new RouteUvFluxModel();

        $pvInfo = array();
        $uvInfo = array();

        for($i=0;$i<$forNumber;$i++){
            if($i == 0){
                $start = mktime(0,0,0,date('m',$start),date('d',$start),date('Y',$start));
                $timeStart = $start;
                $timeEnd = strtotime('+ 1 day',$timeStart);
                $timeEnd = strtotime(' - 1 second',$timeEnd);
            }else{
                $timeEnd = strtotime('+ 1 second',$timeEnd);
                $timeStart = $timeEnd;
                $timeEnd = strtotime('+ 1 day',$timeStart);
                $timeEnd = strtotime(' - 1 second',$timeEnd);
            }

            $ymdTimeStart = date('Y-m-d H:i:s',$timeStart);
            $ymdTimeEnd = date('Y-m-d H:i:s',$timeEnd);
            $dateTimeEnd = date('Y-m-d',$timeEnd);

            $pvInfo['y'][] = $pvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();

            $pvInfo['x'][] = $dateTimeEnd;

            $uvInfo['y'][] = $uvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();

            $uvInfo['x'][] = $dateTimeEnd;

        }

        $returnData['pv_data'] = $pvInfo;
        $returnData['uv_data'] = $uvInfo;

        return $returnData;
    }



















}

?>
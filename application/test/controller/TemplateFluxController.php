<?php
namespace app\test\controller;
use app\test\model\CountrySortModel;
use app\test\model\PlaceSortModel;
use app\test\model\RoutePvFluxModel;
use app\test\model\RouteUvFluxModel;
use think\Request;

class TemplateFluxController extends BaseController
{
    public function addPvFlux()
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
        $nowTime = time();
        $nowTime = mktime(23,59,59,date('m',$nowTime),date('d',$nowTime),date('Y',$nowTime));

        $pvModel = new RoutePvFluxModel();
        $uvModel = new RouteUvFluxModel();

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

            $pvInfo['y'][] = $pvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();
/*            echo $pvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->buildSql();
            echo '<br>';*/
            $pvInfo['x'][] = $dateTimeEnd;

            $uvInfo['y'][] = $uvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();
/*            echo $uvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->buildSql();
            echo '<br>';*/
            $uvInfo['x'][] = $dateTimeEnd;
        }

        $pvInfo['x'] = array_reverse($pvInfo['x']);
        $pvInfo['y'] = array_reverse($pvInfo['y']);

        $uvInfo['x'] = array_reverse($uvInfo['x']);
        $uvInfo['y'] = array_reverse($uvInfo['y']);

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
//            echo $pvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->buildSql();
//            echo '<br>';
            $pvInfo['x'][] = $dateTimeEnd;

            $uvInfo['y'][] = $uvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->count();
//            echo $uvModel->where("template_id = $tempId AND click_time BETWEEN '$ymdTimeEnd' AND '$ymdTimeStart'")->buildSql();
//            echo '<br>';
            $uvInfo['x'][] = $dateTimeEnd;

//        echo '开始时间 '. $ymdTimeStart.' 结束时间 '.$ymdTimeEnd.'<br>';

        }
//exit;

        $returnData['pv_data'] = $pvInfo;
        $returnData['uv_data'] = $uvInfo;

        return $returnData;
    }



















}

?>
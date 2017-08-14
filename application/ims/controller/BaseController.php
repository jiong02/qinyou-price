<?php
namespace app\ims\controller;

use think\Controller;
use think\Request;

class BaseController extends Controller
{
    public function post($name = '', $default = null)
    {
        $request = Request::instance();
        return $request->post($name, $default);
    }

    public function dumpExit($data)
    {
        echo '<pre>';
        var_dump($data);
        exit;
    }

    //对象数据格式化为数组
    public function formateData($data)
    {
        if(empty($data)){
            return false;
        }
        return json_decode(json_encode($data),true);

    }

    public function formatData($data)
    {
        return $data;

    }

    //null数据转化为''数据（一维数组）
    public function nullToChange($data)
    {
        if(empty($data)){
            return false;
        }

        $newData = array();
        foreach($data as $k=>$v){
            if($v === null || $v == ''){
                $newData[$k] = '';
            }else{
                $newData[$k] = $v;
            }
        }
        return $newData;
    }

    //null数据转化为''数据（二维数组）
    public function nullToChange2($data)
    {
        if(empty($data)){
            return false;
        }

        $newData = array();
        foreach($data as $k=>$v){
            foreach($v as $m=>$n){
                if($n === null || $v == ''){
                    $newData[$k][$m] = '';
                }else{
                    $newData[$k][$m] = $n;
                }
            }
        }
        return $newData;
    }

    //计算周末日期
    function countWeek($startDate,$endDate,$notWork)
    {
        $weekDayCount = $this->get_weekend_days($startDate,$endDate);

        if(empty($notWork) || !is_array($notWork)){
            return $weekDayCount;
        }

        $notWorkCount = 0;

        $strStartDate = strtotime($startDate);
        $strEndDate = strtotime($endDate);

        foreach($notWork as $k=>$v){
            if(strtotime($v['start_date']) >= $strStartDate && strtotime($v['end_date']) <= $strEndDate){
                $notWorkCount += $this->get_weekend_days($v['start_date'],$v['end_date']);
            }
        }

        return trim($weekDayCount - $notWorkCount,'-');
    }

    //计算周末日期 核心
    function get_weekend_days($start_date,$end_date){

        if (strtotime($start_date) > strtotime($end_date)) list($start_date, $end_date) = array($end_date, $start_date);

        $start_reduce = $end_add = 0;

        $start_N = date('N',strtotime($start_date));
        $start_reduce = ($start_N == 7) ? 1 : 0;

        $end_N = date('N',strtotime($end_date));
        in_array($end_N,array(6,7)) && $end_add = ($end_N == 7) ? 2 : 1;

        $days = abs(strtotime($end_date) - strtotime($start_date))/86400 + 1;

        return floor(($days + $start_N - 1 - $end_N) / 7) * 2 - $start_reduce + $end_add;
    }


    //计算所有日期的天数
    function countAllDate($startDate,$endDate,$notWork)
    {
        $countDay = $this->count_all_day($startDate,$endDate);

        if(empty($notWork) || !is_array($notWork)){
            return $countDay;
        }

        $newCountDay = 0;
        $strStartDate = strtotime($startDate);
        $strEndDate = strtotime($endDate);

        foreach($notWork as $k=>$v){
            if(strtotime($v['start_date']) >= $strStartDate && strtotime($v['end_date']) <= $strEndDate) {
                $newCountDay += $this->count_all_day($v['start_date'], $v['end_date']);
//                echo $v['start_date'];
            }
        }

//        return $countDay .'|||||'.$newCountDay;
        return trim($countDay - $newCountDay,'-');

    }

    //计算所有日期的天数 核心
    function count_all_day($start_date,$end_date)
    {
        $newStart = $start_date;
        $newEnd = $end_date;
        if(strtotime($start_date) > strtotime($end_date)){
            $newStart = $end_date;
            $newEnd = $start_date;
        }

        $startTime = date('z',strtotime($newStart));
        $endTime = date('z',strtotime($newEnd));

        return $endTime - $startTime + 1;
    }

    //计算某段时间或所有时间的工作日
    function getAllDayCount($startDate,$endDate,$notWork)
    {
        if(empty($startDate) || empty($endDate)){
            return false;
        }

        if(empty($notWork) || !is_array($notWork)){
            return false;
        }

        $workCount = $this->get_all_day_count($startDate,$endDate);
        $notWorkCount = 0;

        $strStartDate = strtotime($startDate);
        $strEndDate = strtotime($endDate);

        foreach($notWork as $k=>$v){
            if(strtotime($v['start_date']) >= $strStartDate && strtotime($v['end_date']) <= $strEndDate) {
                $notWorkCount += $this->get_all_day_count($v['start_date'], $v['end_date']);
            }
        }

        return trim($workCount - $notWorkCount,'-');

    }

    //计算某段时间或所有时间的工作日  核心
    function get_all_day_count($start_date,$end_date)
    {
        $stTime = strtotime($start_date);
        $edTime = strtotime($end_date);
        $work = 1;
        $notWork = 1;

        while($stTime <= $edTime){
            $weekDay = date('N',$stTime);

            if($weekDay == 6 || $weekDay == 7){
                $notWork = $notWork + 1;
            }

            $work = $work + 1;
            $stTime = strtotime('+1 day',$stTime);

        }

        return trim($work - $notWork,'-');

    }


}
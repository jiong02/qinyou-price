<?php
namespace app\test\controller;
use app\test\model\TestAccount;
use app\test\model\TestAccountBackVisitModel;
use think\Request;
use think\Cache;

class DataStatisticsController extends BaseController
{
    /**
     * @name 获得总用户数数据统计接口
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function getAllAccountDataTotal(Request $request)
    {
        //$type = today/week/month/year
        $type = $request->param('type','today');

        $accountModel = new TestAccount();

        //总用户数量
        $totalAccount = $accountModel->count();

        switch($type){
            case 'today':
                $data = $this->getAllAccountTodayDataTotal();
            break;

            case 'week':
                $data = $this->getAllAccountWeekDataTotal();
            break;

            case 'month':
                $data = $this->getAllAccountMonthDataTotal();
            break;

            case 'year':
                $data = $this->getAllAccountYearDataTotal();
            break;
        }

        if(empty($data)){
            return '没有数据';
        }

        $data['total_account'] = $totalAccount;

        return $data;

    }

    /**
     * @name 获得当天数据
     * @auth Sam
     * @return array
     */
    public function getAllAccountTodayDataTotal()
    {
        $dayData = Cache::get('day_data');

        if(!empty($dayData)){
            return $dayData;
        }

        $time = date('Y-m-d',time());

        $accountModel = new TestAccount();
        $backVisitModel = new TestAccountBackVisitModel();

        //新增用户
        $addAccount = $accountModel->where("create_time like '$time%'")->count();

        //回访用户数量
        $backVisit = $backVisitModel->where("visit_time like '$time%'")->count();

        $dayReturn['add_account'] = $addAccount;
        $dayReturn['back_visit'] = $backVisit;

        $time = time();

        $start = mktime(0,0,0,date('m',$time),date('d',$time),date('Y',$time));
        $end = mktime(23,59,59,date('m',$time),date('d',$time),date('Y',$time));
        $ymdStart = date('Y-m-d H:i:s',$start);
        $ymdEnd = date('Y-m-d H:i:s',$end);

        for($i=0;$i<24;$i++){
            if($i !== 0){
                $add_01 = strtotime('+ 1 hour',$add_01);
                $add_02 = strtotime('+ 1 hour',$add_01);
            }else{
                $add_01 = $start;
                $add_02 = strtotime('+ 1 hour',$add_01);
            }

            $timeStart = date('Y-m-d H:i:s',$add_01);
            $timeEnd = date('Y-m-d H:i:s',$add_02);

            $accountCount = $accountModel->where("create_time >= '$timeStart' AND create_time <= '$timeEnd'")->count();

            $dayReturn['x'][] = $timeEnd;
            $dayReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        if(!empty($dayReturn)){
            Cache::set('day_data',$dayReturn,3600);
        }

        return $dayReturn;
    }

    /**
     * @name 获得周末所有数据
     * @auth Sam
     * @return array
     */
    public function getAllAccountWeekDataTotal()
    {

        $weekData = Cache::get('week_data');

        if(!empty($weekData)){
            return $weekData;
        }

        $accountModel = new TestAccount();
        $backVisitModel = new TestAccountBackVisitModel();

        //返回的数据
        $returnArr = array();

        $time = time();
        $reduce = date('w',$time);

        if($reduce == 1){
            $start = $reduce;
            $weekStart = $time;
        }if($reduce == 0){
            $weekStart = strtotime('- 6 day',$time);
        }else{
            $start = $reduce - 1;
            $weekStart = strtotime('- '.$start.' day',$time);
        }

        $add = 7 - $reduce;

        $end = strtotime('+ '.$add.' day',$time);

        $weekEnd = $end;

        $weekStart = mktime(0,0,0,date('m',$weekStart),date('d',$weekStart),date('Y',$weekStart));
        $weekEnd = mktime(23,59,59,date('m',$weekEnd),date('d',$weekEnd),date('Y',$weekEnd));

        //新增用户信息
        $addAccount = $accountModel->where("create_time BETWEEN $weekStart AND $weekEnd ")->count();

        //回访用户数量
        $backVisit = $backVisitModel->where("visit_time BETWEEN $weekStart AND $weekEnd")->count();

        for($i=0;$i<7;$i++){
            if($i !== 0){
                $weekStart = strtotime('+ 1 day',$weekStart);
            }

            $ymdWeekStart = date('Y-m-d',$weekStart);

            $accountCount = $accountModel->where("create_time like '$ymdWeekStart%'")->count();

            $weekReturn['x'][] = $ymdWeekStart;
            $weekReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        $weekReturn['add_account'] = $addAccount;
        $weekReturn['back_visit'] = $backVisit;

        if(!empty($weekReturn)){
            Cache::set('week_data',$weekReturn,3600);
        }

        return $weekReturn;

    }

    /**
     * @name 获得月份所有数据
     * @auth Sam
     * @return mixed
     */
    public function getAllAccountMonthDataTotal()
    {
        $monthData = Cache::get('month_data');

        if(!empty($monthData)){
            return $monthData;
        }

        $accountModel = new TestAccount();
        $backVisitModel = new TestAccountBackVisitModel();


        $time = time();
        $time = mktime(0,0,0,date('m',time()),date('d',time()),date('Y',time()));
        $dTime = date('d',$time);
        $tTime = date('t',$time) - 1;

        if($dTime !== 1){
            $dTime -= 1;
            $monthStart = strtotime('- '.$dTime.' day',$time);
        }if($dTime == 1){
        $monthStart = $time;
    }

        $monthEnd = strtotime('+ '.$tTime.' day',$monthStart);
        $monthEnd = mktime(23,59,59,date('m',$monthEnd),date('d',$monthEnd),date('Y',$monthEnd));

        $wTime = date('w',$monthStart);

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        //新增用户
        $addAccount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        //用户回访量
        $backVisit = $backVisitModel->where("visit_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        if($wTime == 0 || $wTime == 6){
            $forNumber = 5;
        }else{
            $forNumber = 4;
        }

        for($i=0;$i<$forNumber;$i++){
            if($i == 0){
                $allWTime = date('w',$monthStart);
                $wTime = 7 - $allWTime;

                if($wTime == 0){
                    $monthStart_01 = $monthStart;
                    $monthStart_02 = $monthStart;
                }else{
                    $monthStart_01 = $monthStart;
                    $monthStart_02 = strtotime('+ '.$wTime.' day',$monthStart_01);
                }

                $monthReturn['x'][] = '第1周';
            }else{
                $monthStart_01 = strtotime('+ 1 day',$monthStart_02);
                $monthStart_02 = strtotime('+ 6 day',$monthStart_01);

                $monthReturn['x'][] = '第'. (count($monthReturn['x']) + 1) .'周';
            }

            $monthStart_01 = mktime(0,0,0,date('m',$monthStart_01),date('d',$monthStart_01),date('Y',$monthStart_01));
            $monthStart_02 = mktime(23,59,59,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

            $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_01);
            $ymdMonthEnd = date('Y-m-d H:i:s',$monthStart_02);

            $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();
            $monthReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        $monthStart_02 = strtotime(' + 1 day',$monthStart_02);
        $monthStart_02 = mktime(0,0,0,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_02);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        $monthReturn['x'][] = '第'.(count($monthReturn['x']) + 1).'周';

        $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $monthReturn['y'][] = $accountCount;
        $monthReturn['add_account'] = $addAccount;
        $monthReturn['back_visit'] = $backVisit;

        if(!empty($monthReturn)){
            Cache::set('month_data',$monthReturn,3600);
        }

        return $monthReturn;

    }

    /**
     * @name 获得当年数据统计
     * @auth Sam
     * @return mixed
     */
    public function getAllAccountYearDataTotal()
    {
        $yearData = Cache::get('year_data');

        if(!empty($yearData)){
            return $yearData;
        }

        $accountModel = new TestAccount();
        $backVisitModel = new TestAccountBackVisitModel();

        $time = time();
        $dTime = date('Y',$time);

        $yearStart = mktime(0,0,0,1,1,$dTime);
        $yearEnd = strtotime('+ 1 year',$yearStart);
        $yearEnd = strtotime('- 1 day',$yearEnd);
        $yearEnd = mktime(23,59,59,date('m',$yearEnd),date('d',$yearEnd),date('Y',$yearEnd));

        $ymdYearStart = date('Y-m-d H:i:s',$yearStart);
        $ymdYearEnd = date('Y-m-d H:i:s',$yearEnd);

        //新增用户
        $addAccount = $accountModel->where("create_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();

        //用户回访量
        $backVisit = $backVisitModel->where("visit_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();

        $yearReturn['add_account'] = $addAccount;
        $yearReturn['back_visit'] = $backVisit;

        for($i=0;$i<12;$i++){
            if($i == 0){
                $yearstart_01 = $yearStart;
                $yearstart_02 = strtotime('+ 1 month',$yearstart_01);
                $yearstart_02 = strtotime(' - 1 day',$yearstart_02);
            }else if($i == 11){
                $yearstart_01 = $yearstart_02;
                $yearstart_01 = mktime(0,0,0,date('m',$yearstart_01),date('d',$yearstart_01),date('Y',$yearstart_01));
                $yearstart_01 = strtotime(' + 1 day',$yearstart_01);
                $yearstart_02 = strtotime('+ 1 month',$yearstart_01);
                $yearstart_02 = strtotime(' - 1 day',$yearstart_02);
            }else{
                $yearstart_01 = $yearstart_02;
                $yearstart_01 = mktime(0,0,0,date('m',$yearstart_01),date('d',$yearstart_01),date('Y',$yearstart_01));
                $yearstart_01 = strtotime(' + 1 day',$yearstart_01);
                $yearstart_02 = strtotime('+ 1 month',$yearstart_01);
                $yearstart_02 = strtotime(' - 1 day',$yearstart_02);
            }

            $yearstart_02 = mktime(23,59,59,date('m',$yearstart_02),date('d',$yearstart_02),date('Y',$yearstart_02));

            $ymdYearStart = date('Y-m-d H:i:s',$yearstart_01);
            $ymdYearEnd = date('Y-m-d H:i:s',$yearstart_02);

            $yearReturn['x'][] = (date('n',$yearstart_02)).'月份';

            $accountCount = $accountModel->where("create_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();
            $yearReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        if(!empty($yearReturn)){
            Cache::set('year_data',$yearReturn,3600);
        }


        return $yearReturn;

    }






}
?>
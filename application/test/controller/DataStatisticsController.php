<?php
namespace app\test\controller;
use app\test\model\TestAccount;
use app\test\model\TestAccountBackVisitModel;
use think\Request;
use think\Cache;

class DataStatisticsController extends BaseController
{

    /**
     * @name 获取所有用户统计数据
     * @auth sam
     * @param Request $request
     * @return mixed|string
     */
    public function getAllAccountDataTotal(Request $request)
    {
        Cache::clear();
        $type = $request->param('type','every_day');

        $accountModel = new TestAccount();

        //总用户数量
        $totalAccount = $accountModel->count();

        switch($type){
            case 'every_day':
                $data = $this->getAllAccountEveryDay();
                break;

            case 'week':
                $data = $this->getAllAccountEveryWeek();
                break;

            case 'month':
                $data = $this->getAllAccountEveryMonth();
                break;

        }

        if(empty($data)){
            return '没有数据';
        }

        $data['total_account'] = $totalAccount;

        return $data;

    }

    /**
     * @name 总用户数量 获取当月每日数据
     * @return mixed
     */
    public function getAllAccountEveryDay()
    {
        //当月每天
        $dayData = Cache::get('all_day_data');

        if(!empty($dayData)){
            return $dayData;
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

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        //新增用户
        $addAccount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $dayReturn['add_account'] = $addAccount;

        //回访用户
        $backVisit = $backVisitModel->where("visit_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $dayReturn['back_visit'] = $backVisit;

        $forNumber = date('d',$monthEnd);

        for($i=0;$i<$forNumber;$i++){
            if($i == 0){
                $dayStart = $monthStart;
                $dayEnd = strtotime('+ 1 day',$dayStart);
                $dayEnd = strtotime('- 1 second',$dayEnd);
            }else{
                $dayStart = strtotime('+ 1 second',$dayEnd);
                $dayEnd = strtotime('+ 1 day',$dayStart);
                $dayEnd = strtotime('- 1 second',$dayEnd);
            }

            $ymdDayStart = date('Y-m-d H:i:s',$dayStart);
            $ymdDayEnd = date('Y-m-d H:i:s',$dayEnd);

            if($i == 0){
                $accountCount = $accountModel->where("create_time BETWEEN '$ymdDayStart' AND '$ymdDayEnd'")->count();
            }else{
                $allAccountCount = $accountCount;
                $accountCount = $accountModel->where("create_time BETWEEN '$ymdDayStart' AND '$ymdDayEnd'")->count();
                $accountCount = $allAccountCount + $accountCount;
            }

/*            if($dayEnd > time()){
                $accountCount = 0;
            }*/

            $dayReturn['y'][] = $accountCount;
            $dayReturn['x'][] = date('Y-m-d',$dayStart);
        }


        if(!empty($dayReturn)){
            Cache::Set('all_day_data',$dayReturn,1200);
        }

        return $dayReturn;


    }

    /**
     * @name 总用户数量 当月每周
     * @auth Sam
     * @return mixed
     */
    public function getAllAccountEveryWeek()
    {
        //当月每周
        $weekData = Cache::get('all_week_data');

        if(!empty($weekData)){
            return $weekData;
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

                $weekReturn['x'][] = '第1周';
            }else{
                $monthStart_01 = strtotime('+ 1 day',$monthStart_02);
                $monthStart_02 = strtotime('+ 6 day',$monthStart_01);

                $weekReturn['x'][] = '第'. (count($weekReturn['x']) + 1) .'周';
            }

            $monthStart_01 = mktime(0,0,0,date('m',$monthStart_01),date('d',$monthStart_01),date('Y',$monthStart_01));
            $monthStart_02 = mktime(23,59,59,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

            $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_01);
            $ymdMonthEnd = date('Y-m-d H:i:s',$monthStart_02);

            if($i == 0){
                $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();
            }else{
                $allAccountCount = $accountCount;
                $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();
                $accountCount = $accountCount + $allAccountCount;
            }

/*            if($monthStart_01 > time()){
                $accountCount = 0;
            }*/
            $weekReturn['y'][] = $accountCount;

        }


        $monthStart_02 = strtotime(' + 1 day',$monthStart_02);
        $monthStart_02 = mktime(0,0,0,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_02);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        $weekReturn['x'][] = '第'.(count($weekReturn['x']) + 1).'周';

        $allAccountCount = $accountCount;
        $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();
        $accountCount = $allAccountCount + $accountCount;

        $weekReturn['y'][] = $accountCount;
        $weekReturn['add_account'] = $addAccount;
        $weekReturn['back_visit'] = $backVisit;

        if(!empty($weekReturn)){
            Cache::set('all_week_data',$weekReturn,1200);
        }

        return $weekReturn;


    }

    /**
     * @name 总用户数量 获取当年每月数据
     * @return mixed
     */
    public function getAllAccountEveryMonth()
    {
        //当年每月
        $monthData = Cache::get('all_month_data');

        if(!empty($monthData)){
            return $monthData;
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

        $monthReturn['add_account'] = $addAccount;
        $monthReturn['back_visit'] = $backVisit;

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

            $monthReturn['x'][] = (date('n',$yearstart_02)).'月份';

            if($i == 0){
                $accountCount = $accountModel->where("create_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();
            }else{
                $allAcountCount = $accountCount;
                $accountCount = $accountModel->where("create_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();
                $accountCount = $allAcountCount + $accountCount;
            }

/*            if($yearstart_01 > time()){
                $accountCount = 0;
            }*/

            $monthReturn['y'][] = $accountCount;


        }

        if(!empty($monthReturn)){
            Cache::set('all_month_data',$monthReturn,1200);
        }

        return $monthReturn;

    }



    /**
     * @name 获取新增用户信息统计
     * @auth Sam
     * @param Request $request
     * @return mixed|string
     */
    public function getAddAccountDataTotal(Request $request)
    {
        //$type = today/week/month/year
        $type = $request->param('type','every_day');

        $accountModel = new TestAccount();

        //总用户数量
        $totalAccount = $accountModel->count();

        switch($type){
            case 'every_day':
                $data = $this->getAddAccountEveryDay();
                break;

            case 'week':
                $data = $this->getAddAccountEveryWeek();
                break;

            case 'month':
                $data = $this->getAddAccountEveryMonth();
                break;

        }

        if(empty($data)){
            return '没有数据';
        }

        $data['total_account'] = $totalAccount;

        return $data;

    }

    /**
     * @name 新增用户数量 当月每天数据
     * @auth Sam
     * @return mixed
     */
    public function getAddAccountEveryDay()
    {
        //当月每天
        $dayData = Cache::get('add_day_data');

        if(!empty($dayData)){
            return $dayData;
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

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        //新增用户
        $addAccount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $dayReturn['add_account'] = $addAccount;

        //回访用户
        $backVisit = $backVisitModel->where("visit_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $dayReturn['back_visit'] = $backVisit;

        $forNumber = date('d',$monthEnd);

        for($i=0;$i<$forNumber;$i++){
            if($i == 0){
                $dayStart = $monthStart;
                $dayEnd = strtotime('+ 1 day',$dayStart);
                $dayEnd = strtotime('- 1 second',$dayEnd);
            }else{
                $dayStart = strtotime('+ 1 second',$dayEnd);
                $dayEnd = strtotime('+ 1 day',$dayStart);
                $dayEnd = strtotime('- 1 second',$dayEnd);
            }

            $ymdDayStart = date('Y-m-d H:i:s',$dayStart);
            $ymdDayEnd = date('Y-m-d H:i:s',$dayEnd);

            $accountCount = $accountModel->where("create_time BETWEEN '$ymdDayStart' AND '$ymdDayEnd'")->count();

            $dayReturn['y'][] = $accountCount;
            $dayReturn['x'][] = date('Y-m-d',$dayStart);

            $accountCount = 0;
        }


        if(!empty($dayReturn)){
            Cache::Set('add_day_data',$dayReturn,1200);
        }

        return $dayReturn;
    }

    /**
     * @name 新增用户数量 获得每月周末数据
     * @return mixed
     */
    public function getAddAccountEveryWeek()
    {
        //当月每周
        $weekData = Cache::get('add_week_data');

        if(!empty($weekData)){
            return $weekData;
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

                $weekReturn['x'][] = '第1周';
            }else{
                $monthStart_01 = strtotime('+ 1 day',$monthStart_02);
                $monthStart_02 = strtotime('+ 6 day',$monthStart_01);

                $weekReturn['x'][] = '第'. (count($weekReturn['x']) + 1) .'周';
            }

            $monthStart_01 = mktime(0,0,0,date('m',$monthStart_01),date('d',$monthStart_01),date('Y',$monthStart_01));
            $monthStart_02 = mktime(23,59,59,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

            $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_01);
            $ymdMonthEnd = date('Y-m-d H:i:s',$monthStart_02);

            $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();
            $weekReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        $monthStart_02 = strtotime(' + 1 day',$monthStart_02);
        $monthStart_02 = mktime(0,0,0,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_02);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        $weekReturn['x'][] = '第'.(count($weekReturn['x']) + 1).'周';

        $accountCount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $weekReturn['y'][] = $accountCount;
        $weekReturn['add_account'] = $addAccount;
        $weekReturn['back_visit'] = $backVisit;

        if(!empty($weekReturn)){
            Cache::set('add_week_data',$weekReturn,1200);
        }

        return $weekReturn;

    }

    /**
     * @name 新增用户数量 获取当年每个月数据
     * @auth Sam
     * @return mixed
     */
    public function getAddAccountEveryMonth()
    {
        //当年每月
        $monthData = Cache::get('add_month_data');

        if(!empty($monthData)){
            return $monthData;
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

        $monthReturn['add_account'] = $addAccount;
        $monthReturn['back_visit'] = $backVisit;

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

            $monthReturn['x'][] = (date('n',$yearstart_02)).'月份';

            $accountCount = $accountModel->where("create_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();
            $monthReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        if(!empty($monthReturn)){
            Cache::set('add_month_data',$monthReturn,1200);
        }


        return $monthReturn;


    }

    /**
     * @name 回访量 查看回访量数据
     * @auth Sam
     * @param Request $request
     * @return mixed|string|void
     */
    public function getBackVisitDataTotal(Request $request)
    {
        $type = $request->param('type','every_day');

        $accountModel = new TestAccount();

        switch($type){
            case 'every_day':
                $data = $this->getBackVisitEveryDay();
                break;

            case 'week':
                $data = $this->getBackVisitEveryWeek();
                break;

            case 'month':
                $data = $this->getBackVisitEveryMonth();
                break;

        }

        if(empty($data)){
            return '没有数据';
        }

        //总用户数量
        $totalAccount = $accountModel->count();

        $data['total_account'] = $totalAccount;

        return $data;



    }

    /**
     * @name 回访量 查看当月每天回访量
     * @aurh Sam
     * @return mixed
     */
    public function getBackVisitEveryDay()
    {
        //当月每天
        $dayData = Cache::get('back_day_data');

        if(!empty($dayData)){
            return $dayData;
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

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        //新增用户
        $addAccount = $accountModel->where("create_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $dayReturn['add_account'] = $addAccount;

        //回访用户
        $backVisit = $backVisitModel->where("visit_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $dayReturn['back_visit'] = $backVisit;

        $forNumber = date('d',$monthEnd);

        for($i=0;$i<$forNumber;$i++){
            if($i == 0){
                $dayStart = $monthStart;
                $dayEnd = strtotime('+ 1 day',$dayStart);
                $dayEnd = strtotime('- 1 second',$dayEnd);
            }else{
                $dayStart = strtotime('+ 1 second',$dayEnd);
                $dayEnd = strtotime('+ 1 day',$dayStart);
                $dayEnd = strtotime('- 1 second',$dayEnd);
            }

            $ymdDayStart = date('Y-m-d H:i:s',$dayStart);
            $ymdDayEnd = date('Y-m-d H:i:s',$dayEnd);

            $accountCount = $backVisitModel->where("visit_time BETWEEN '$ymdDayStart' AND '$ymdDayEnd'")->count();

            $dayReturn['y'][] = $accountCount;
            $dayReturn['x'][] = date('Y-m-d',$dayStart);

            $accountCount = 0;
        }


        if(!empty($dayReturn)){
            Cache::Set('back_day_data',$dayReturn,1200);
        }

        return $dayReturn;



    }

    /**
     * @name 回访量 获得当月每周数据
     * @auth Sam
     * @return mixed
     */
    public function getBackVisitEveryWeek()
    {
        $weekData = Cache::get('back_week_data');

        if(!empty($weekData)){
            return $weekData;
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

                $weekReturn['x'][] = '第1周';
            }else{
                $monthStart_01 = strtotime('+ 1 day',$monthStart_02);
                $monthStart_02 = strtotime('+ 6 day',$monthStart_01);

                $weekReturn['x'][] = '第'. (count($weekReturn['x']) + 1) .'周';
            }

            $monthStart_01 = mktime(0,0,0,date('m',$monthStart_01),date('d',$monthStart_01),date('Y',$monthStart_01));
            $monthStart_02 = mktime(23,59,59,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

            $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_01);
            $ymdMonthEnd = date('Y-m-d H:i:s',$monthStart_02);

            $accountCount = $backVisitModel->where("visit_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();
            $weekReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        $monthStart_02 = strtotime(' + 1 day',$monthStart_02);
        $monthStart_02 = mktime(0,0,0,date('m',$monthStart_02),date('d',$monthStart_02),date('Y',$monthStart_02));

        $ymdMonthStart = date('Y-m-d H:i:s',$monthStart_02);
        $ymdMonthEnd = date('Y-m-d H:i:s',$monthEnd);

        $weekReturn['x'][] = '第'.(count($weekReturn['x']) + 1).'周';

        $accountCount = $backVisitModel->where("visit_time BETWEEN '$ymdMonthStart' AND '$ymdMonthEnd'")->count();

        $weekReturn['y'][] = $accountCount;
        $weekReturn['add_account'] = $addAccount;
        $weekReturn['back_visit'] = $backVisit;

        if(!empty($weekReturn)){
            Cache::set('back_week_data',$weekReturn,1200);
        }

        return $weekReturn;



    }


    /**
     * @name 回访量 获得每月数据
     * @auth Sam
     * @return mixed
     */
    public function getBackVisitEveryMonth()
    {
        $monthData = Cache::get('back_month_data');

        if(!empty($monthData)){
            return $monthData;
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

        $monthReturn['add_account'] = $addAccount;
        $monthReturn['back_visit'] = $backVisit;

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

            $monthReturn['x'][] = (date('n',$yearstart_02)).'月份';

            $accountCount = $backVisitModel->where("visit_time BETWEEN '$ymdYearStart' AND '$ymdYearEnd'")->count();
            $monthReturn['y'][] = $accountCount;

            $accountCount = 0;

        }

        if(!empty($monthReturn)){
            Cache::set('back_month_data',$monthReturn,1200);
        }


        return $monthReturn;







    }





}

?>

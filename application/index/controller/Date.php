<?php
namespace app\index\controller;

class Date
{

    public static $delimiter = '';

    public static function tranDay($time)
    {
        return strpos($time, ',') !== false ? explode(',', $time)[1] : 0;
    }

    public static function tranTime($time,$delimiter = ':')
    {
        if (strpos($time, ',') !== false) {

            $time =  explode(',', $time)[0];

        }
        return Date::secToMH($time, $delimiter);
    }

    public static function formatToMH($time)
    {
         if (strpos($time, ',') !== false) {

             $time =  explode(',', $time);

             return $time[0].'小时'.$time[1].'分钟';

         }else if($time == 0){

             return '0小时0分钟';
         }
    }

    public static function getWeek($week)
    {
        switch (strtolower($week)) {
            case 'mo':
                return '星期一';
            case 'tu':
                return '星期二';
            case 'we':
                return '星期三';
            case 'th':
                return '星期四';
            case 'fr':
                return '星期五';
            case 'sa':
                return '星期六';
            case 'su':
                return '星期日';
            case 'mon':
                return 'mo';
            case 'tue':
                return 'tu';
            case 'wed':
                return 'we';
            case 'thu':
                return 'th';
            case 'fri':
                return 'fr';
            case 'sat':
                return 'sa';
            case 'sun':
                return 'su';
                break;
        }
    }

    public static function getDateWeek($week)
    {
        switch (strtolower($week)) {
            case 'mon':
                return '周一';
            case 'tue':
                return '周二';
            case 'wed':
                return '周三';
            case 'thu':
                return '周四';
            case 'fri':
                return '周五';
            case 'sat':
                return '周六';
            case 'sun':
                return '周日';
                break;
        }
    }

    /**
     * 根据生日获取年龄
     * @param mixd $birthday 生日
     * @return int $age 年龄
     */
    public static function getAge($birthday)
    {
        $birthday = is_numeric($birthday) ? $birthday : strtotime($birthday);
        if (date('Y', time()) > date('Y', $birthday)){
            $age = date('Y', time()) - date('Y', $birthday) - 1;
            if (date('m', time()) == date('m', $birthday)){

                if (date('d', time()) > date('d', $birthday)){

                    $age++;

                }

            }elseif (date('m', time()) > date('m', $birthday)){

                $age++;

            }
        }elseif(date('Y', time()) == date('Y', $birthday)){

            $age = self::getDays($birthday,time())/365;
            $age = round($age,3);
        }

        return $age;
    }
    /**
     * 获取指定日期段的天数
     * @param  date  $startDate 开始日期
     * @param  date  $endDate   结束日期
     * @return int
     */
    public static function getDays($startDate, $endDate)
    {

        $startTimestamp = is_numeric($startDate) ? $startDate : strtotime($startDate);
        $endTimestamp = is_numeric($endDate) ? $endDate : strtotime($endDate);
        // 计算日期段内有多少天
        return ($endTimestamp-$startTimestamp)/86400+1;
    }

    /**
     * 获取指定日期段内每一天的日期
     * @param  date  $startDate 开始日期
     * @param  date  $endDate   结束日期
     * @return array
     */
    public static function getDateFromRange($startDate, $endDate)
    {

        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // 计算日期段内有多少天
        $days = ($endTimestamp-$startTimestamp)/86400+1;

        // 保存每天日期
        $date = array();

        for($i=0; $i<$days; $i++){
            $date[] = date('Y-m-d', $startTimestamp+(86400*$i));
        }

        return $date;
    }

    /**
     * 获取指定日期的星期
     * @param $date
     * @return mixed
     */
    public static function getWeekByDate($date)
    {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date('D',$timestamp);
    }

    /**
     * 获取指定日期的星期与日数
     * @param mixd $date 指定日期或时间戳
     * @return array
     */

    public static function getWeekAndDay($date)
    {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        $data['day'] = date('d',$timestamp);
        $data['week'] = self::getWeekByDate($date);
        return $data;
    }

    public static function secToMH($second, $delimiter = ':')
    {
        self::$delimiter = $delimiter;

        if ($delimiter != 'str') {

            return self::tranSecToDelimiterMH($second);

        }else{

            return self::tranSecToStringMHDY($second);
        }

    }

    public static function tranSecToDelimiterMH($second)
    {
        return self::_tranSecToDelimiterMH($second);
    }

    public static function tranSecToStringMHDY($second)
    {
        return self::_tranSecToStringMHDY($second);
    }
    /**
     * 转换秒(Second)为分钟(Minute)，小时(Hour)
     */
    private static function _tranSecToDelimiterMH($second, $return = '')
    {
        static $times = 0;//递归次数

        if(0 != $second)
        {
            if ($second >= 60 && $second < 60 * 60) {
                if ($times == 0) {

                    $return .= '00'. self::$delimiter . Data::zeroFill(floor($second / 60));

                }else{

                    $return .= Data::zeroFill(floor($second / 60));

                }

                $second = 0;

            }else if($second >= 60 * 60){

                $return .= Data::zeroFill(floor($second / (60 * 60))). self::$delimiter ;

                if (($second %= (60 * 60)) == 0) {

                    $return .= '00';

                }
                $times++;

            }
            return self::_tranSecToDelimiterMH($second, $return);
        }
        else
        {
            if ($times == 0 && $second == 0) {

                return '00:00';

            }
            $times = 0;
            return $return;
        }
    }

    /**
     * 转换秒(Second)为分钟(Minute)，小时(Hour)，天(Day)，年(Year)
     */
    private static function _tranSecToStringMHDY($second, $return = '')
    {
        static $times = 0;//递归次数
        if($second > 30240000) return '超过50年';//把值写大点。大概超过51年左右，就报错了
        if(0 != $second)
        {
            if($second < 60) {
                $return .= $second.'秒'; $second = 0;
            } else if($second >= 60 && $second < 60 * 60) {//分钟
                $return .= floor($second / 60).'分钟'; $second %= 60;$times++;
            } else if($second >= 60 * 60 && $second < 24 * 60 * 60) {//小时
                $return .= floor($second / (60 * 60)).'小时'; $second %= (60 * 60);$times++;
            } else if ($second >= 24 * 60 * 60 && $second < 7 * 24 * 60 * 60) {//天
                $return .= floor($second / (24 * 60 * 60)).'天'; $second %= (24 * 60 * 60);$times++;
            } else if ($second >= 7 * 24 * 60 * 60 && $second < 365 * 24 * 60 * 60) {//年
                $return .= floor($second / (7 * 24 * 60 * 60)).'年'; $second %= (7 * 24 * 60 * 60);$times++;
            }
            return self::_tranSecToStringMHDY($second, $return);
        }
        else
        {
            if ($times == 0 && $second == 0) {

                return '0小时0分钟';

            }
            $times = 0;
            return $return;
        }
    }
}

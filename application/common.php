<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
defined('UPLOADS_PATH') or define('UPLOADS_PATH',ROOT_PATH . 'public' . DS . 'uploads' . DS );
defined('IMAGE_PATH') or define('PUBLIC_PATH','uploads' . DS );
defined('CONNECT') or define('CONNECT','ims_new');
defined('PREFIX') or define('PREFIX','ims_');

function getSucc($msg)
{
    return ['status'=>1,'msg'=>$msg];
}

function getSuccess($msg)
{
    return ['status'=>1,'msg'=>$msg];
}

function getError($msg)
{
    return ['status'=>0,'msg'=>$msg];
}


function getErr($msg)
{
    return ['status'=>0,'msg'=>$msg];
}

function abortError($msg)
{
    abort('404',$msg);
}

function zero_fill($str, $length = 2, $pad = 0, $type = 'LEFT')
{
    $type = strtoupper($type);
    switch ($type) {
        case 'LEFT':
            $type = STR_PAD_LEFT;
            break;
        case 'RIGHT':
            $type = STR_PAD_RIGHT;
            break;
        case 'BOTH':
            $type = STR_PAD_BOTH;
            break;
    }
    return str_pad($str,$length,$pad,$type);
}

/**
 * @param $str
 * @param string $return
 * @return string
 * 将大驼峰字符串转换为下划线风格
 */
function hump_to_under_score($str, $return = '')
{
    for($i=0;$i<strlen($str);$i++){
        if($str[$i] == strtolower($str[$i])){
            $return .= $str[$i];
        }else{
            if($i>0){
                $return .= '_';
            }
            $return .= strtolower($str[$i]);
        }
    }
    return $return ?: $str;
}

/**
 * 将下划线风格字符串转成大驼峰
 */
function under_score_to_hump($str, $return = ''){

    for ($i = 0;$i<strlen($str);$i++){
        $string = $str[$i];
        if($string == '_'){
            $string = strtoupper($str[$i+1]);
            $i++;
        }else{
            if($i == 0){
                $string = strtoupper($string);
            }
        }
        $return .= $string;
    }
    return $return;
}


/**
 * @param $var
 * @return bool|int|string
 * 获取变量名
 */
function get_var_name($var) {
    foreach($GLOBALS as $varName => $value) {
        if ($value === $var) {
            return $varName;
        }
    }
    return false;
}

function format_object_data($object,$propertyName1,$propertyName2 = null,$return = array()){
    foreach ($object as $index => $item) {

        $ret = $item->$propertyName1;
        if($propertyName2){
            $ret = $ret->$propertyName2;
        }
        $return[$index] = $ret;
    }
    return $return;
}

function tran_seconds_to_hours_and_minutes($second)
{
    $second = abs($second);
    if($second > 24 * 60 * 60){
        exception('秒数大于一天!');
    }
    if($second >= 60 * 60){
        $hours = floor($second / (60 * 60)).'小时';
        $second %= (60 * 60);
    }else{
        $hours = '0小时';
    }
    if($second >= 60){
        $minutes = floor($second / 60).'分钟';
    }else{
        $minutes = '0分钟';
    }
    return $hours . $minutes;
}

/**
 * 获取指定日期段的天数
 * @param  date  $startDate 开始日期
 * @param  date  $endDate   结束日期
 * @return int
 */
function get_day_amount($startDate, $endDate)
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
function get_date_from_range($startDate, $endDate)
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
function get_week_by_date($date)
{
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date('D',$timestamp);
}

/**
 * 获取指定日期的星期与日数
 * @param mixd $date 指定日期或时间戳
 * @return array
 */

function get_week_and_day($date)
{
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $data['day'] = date('d',$timestamp);
    $data['week'] = get_week_by_date($date);
    return $data;
}

/**
 * 根据生日获取年龄
 * @param mixd $birthday 生日
 * @return int $age 年龄
 */
function get_age($birthday)
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

        $age = get_day_amount($birthday,time())/365;
        $age = round($age,3);
    }

    return $age;
}

/**
 * 获取指定数字对应的字母(大于等于26)
 * @param $order
 * @param string $letter
 * @return string
 */
function get_letter($order,$letter = '')
{
    if($order <=26){
         $letter = letter($order);
    }elseif ($order >=26 && $order <= 26 * 26) {
        $times = floor($order/26);
        $letter = letter($times);
        if ($times >= 1 ) {
            $order = $order % 26;
            $letter .= letter($order);
        }
    }
    return $letter;
}
/**
 * 获取指定数字对应的字母(大于等于26)
 * @param string $letter
 * @param int $order
 * @return string
 */
function letter_tran_number($letter,$order = 0)
{
    $letterLength = strlen($letter);
    if($letterLength == 1){
        $order = tran_letter($letter);
    }elseif ($letterLength == 2) {
        $firstLetter = $letter[0];
        $secondLetter = $letter[1];
        $firstOrder =  tran_letter($firstLetter) * 26;
        $secondOrder =  tran_letter($secondLetter);
        $order = $firstOrder + $secondOrder;
    }
    return $order;
}


/**
 * 获取指定数字对应的字母(小于等于26)
 * @param $order
 * @param string $letter
 * @return string
 */
function letter($order = 1)
{
    return chr(ord('A') + $order - 1);
}

/**
 * 通过制定字母转文字(小于等于26)
 * @param $letter
 * @return int
 */
function tran_letter($letter)
{
    return ord($letter) - ord('A') + 1;
}

function check_multiple_key($keyArray,$data)
{
    $count = 0;
    foreach ($keyArray as $value) {
        if (isset($data[$value])) {
            $count ++;
        }
    }
    if ($count == count($keyArray)) {
        return true;
    }
    return false;
}

/**
 * 获取随机字符串
 * @param int 字符串长度
 */
function get_nonce_str($length = 8)
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function get_need_between($str,$first,$second){
    $start = stripos($str, $first);
    $end = stripos($str, $second);
    if ($start === false || $end === false || $start >= $end) {

        return false;

    }
    return substr($str, $start + strlen($first) ,$end - $start - strlen($first));
}

function checkEmpty($data)
{
    if (empty($data) || trim($data) == ''){
        return true;
    }
    return false;
}
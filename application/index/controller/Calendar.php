<?php
namespace app\index\controller;
use app\index\model\Calendar as CalendarModel;
use app\index\model\Contract as ContractModel;
use app\index\model\Order as OrderModel;
use think\Db;
use think\Validate;
use think\Auth;

class Calendar extends BaseController
{
    protected $rule = [
        'hotelId'           => 'require',
        'addressId'         => 'require',
        'countryId'         => 'require',
        'type'              => 'require',
        'holiday_name'      => 'require',
        'start_time'        => 'dateFormat:Y-m-d',
        'end_time'          => 'dateFormat:Y-m-d',
        'addSn'             => 'require',
    ];

    protected $message = [
        'hotelId.require'   => '酒店不能为空',
        'addressId.require' => '目的地不能为空',
        'countryId.require' => '国家不能为空',
        'type.require'      => '类型不能为空',
        'holiday_name.require' => '节假日名称不能为空',
        'start_time.dateFormat' => '开始时间格式错误',
        'end_time.dateFormat' => '结束时间格式错误',
        'addSn'               => '请填写员工编号',
    ];

    public function index()
    {
        echo 'test';
    }

    //获得日历表当月数据
    public function getCaleInfo()
    {
/*        $request = request();

        $auth = new Auth;
        $path = strtolower($request->routeInfo()['route']);
        $result = $auth->check($path,80000);

        if(empty($result)){
            echo 'fail';
        }*/

        $request = $this->request;
        $userSn = $request->param('userSn',0);
        $addressId = $request->param('addressId',0);
        $hotelId = $request->param('hotelId',0);
        $countryId = $request->param('countryId',0);
        $dateTime = $request->param('dateTime','');
        $year = substr($dateTime,0,4);
        $month = substr($dateTime,5,2);

        if(!empty($userSn) & !empty($hotelId) & !empty($dateTime)){
            $conInfo = array();
            $retConInfo = array();
            $orderInfo = array();
            $caleInfo = array();
            $disCon = array();
            $color = '';
            //查询淡旺季信息
            $conModel = new ContractModel();
            $conInfo = $conModel->getConInfo($hotelId);

            $conInfo = json_decode(json_encode($conInfo),true);

            if(!empty($conInfo)){
                foreach($conInfo as $k=>$v){
                    foreach($v as $m=>$n){
                        if($m == 'dates' && $disCon = $this->disSeason($v['dates'],$year,$month)){
                            $retConInfo[] = $v;
                            $disCon = array();
                        }
                    }
                }
            }

            //查询节日信息
            $caleModel = new CalendarModel();
            $caleInfo = $caleModel->getCaleInfo($countryId,$addressId,$year,$month);

            //查询订单信息
            $orderModel = new OrderModel();
            $orderInfo = $orderModel->getOrderInfo($userSn,$hotelId,$year,$month);

            //查询订单颜色
            $color = Db::table('ims_color_record')->field('color')->where(['en_name'=>'people_order'])->find();
            if(!empty($orderInfo)){
                foreach((array)$orderInfo as $k=>$v){
                    $orderInfo[$k]['color'] = $color['color'];
                }
            }
            $returnArray = $caleInfo;
            $returnArray = ['season'=>$retConInfo,'holiday'=>$caleInfo,'order'=>$orderInfo];
            return json($returnArray);

        }

//        return json('查询失败');

    }

    //获得日历表选择日数据
    public function getNowCaleInfo()
    {
        $request = $this->request;
        $userSn = $request->param('userSn',0);
        $addressId = $request->param('addressId',0);
        $hotelId = $request->param('hotelId',0);
        $countryId = $request->param('countryId',0);
        $dateTime = $request->param('dateTime','');

        if(!empty($userSn) & !empty($hotelId) & !empty($dateTime)){
            $conInfo = array();
            $retConInfo = array();
            $orderInfo = array();
            $caleInfo = array();
            $disCon = array();
            $color = '';

            //查询淡旺季信息
            $conModel = new ContractModel();
            $conInfo = $conModel->getConInfo($hotelId);


            if(!empty($conInfo)){
                foreach($conInfo as $k=>$v){
                    foreach($v as $m=>$n){
                        if($m == 'dates' && $disCon = $this->disNowSeason($v['dates'],$dateTime)){
                            $retConInfo[$k] = $v;
                            $retConInfo[$k]['dates'] = $disCon;
                            $disCon = array();
                        }
                    }
                }
            }

            //获得选择节假日信息
            $caleModel = new CalendarModel();
            $caleInfo = $caleModel->getNowCaleInfo($countryId,$addressId,$dateTime);

            //获得选择日期的订单
            $orderModel = new OrderModel();
            $orderInfo = $orderModel->getNowOrderInfo($userSn,$hotelId,$dateTime);

            //查询订单颜色
            $color = Db::table('ims_color_record')->field('color')->where(['en_name'=>'people_order'])->find();
            if(!empty($orderInfo)){
                foreach((array)$orderInfo as $k=>$v){
                    $orderInfo[$k]['color'] = $color['color'];
                }
            }

            return json(['season'=>$retConInfo,'holiday'=>$caleInfo,'order'=>$orderInfo]);
        }

        return json('查询失败');




    }

    // 添加节日
    public function addCalendar()
    {
        $request = $this->request;
        $data = $request->param();

        $validate = new Validate($this->rule,$this->message);
        $result   = $validate->batch(true)->check($data);

        if(!empty($result)){
            $dateStart = strtotime($data['start_time']);
            $dateEnd = strtotime($data['end_time']);

            if($dateStart > $dateEnd){
                return json('结束时间不能大于开始时间');
            }

            $caleModel = new CalendarModel();
            $caleModel->data([
                'holiday_name' => $data['holiday_name'],
                'type' => $data['type'],
                'hotel_id' => $data['hotelId'],
                'country_id' => $data['countryId'],
                'address_id' => $data['addressId'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'remake' => $data['remake'],
                'add_emp' => $data['addSn'],
            ]);
            $result = $caleModel->save();

            if(!empty($result)){
                return json('添加成功');
            }else{
                return json($caleModel->getError());
            }
        }else{
            return json($validate->getError());
        }

        return json('添加失败');

    }

    //删除节假日
    public function deleteCalendar()
    {
        $request = $this->request;
        $id = $request->param('id',0);
        $holidayName = $request->param('holidayName','');
        $startTime = $request->param('startTime','');
        $dateTime = date('Y-m-d H:i:s',time());

        if(!empty($id) && !empty($holidayName) && !empty($startTime)){
            $data = [
                'id' => $id,
                'holiday_name' => $holidayName,
                'start_time' => $startTime,
            ];

            $result = Db::table('ims_calendar')
                ->where($data)
                ->update(['is_delete'=>'是','modify_time'=>$dateTime]);

            if(!empty($result)){
                return json('修改成功');
            }
        }

        return json('修改失败');

    }


    //Ajax获得订单信息
    public function showOrderInfo()
    {
        $request = $this->request;
        $userSn = $request->param('userSn','0');
        $hotelId = $request->param('hotelId',0);
        $start_time = $request->param('start_time','');

        $orderInfo = array();
        $orderInfo = Db::table('ims_order')->where(['emp_sn'=>$userSn,'start_date'=>$start_time,'hotel_id'=>$hotelId])->select();

        return json($orderInfo);
    }

    /*淡旺季数据当月处理
     * @param $str = 淡旺季的字符串
     * @param $year = '2017' 年
     * @param $month = '06'  月
     */
    public function disSeason($str,$year,$month)
    {
        if(empty($str)){
            return array();
        }

        $expArray = array();
        $returnArray = array();

        $str = str_replace(array('"','[',']'),"",$str);
        $expArray = explode(',',$str);

        foreach($expArray as $k=>$v){
            $subYear = substr($v,0,4);
            $subMonth = substr($v,5,2);

            if((int)$year == (int)$subYear && (int)$month == (int)$subMonth){
                return $v;
            }

        }

        return($returnArray);

    }

    //淡旺季获得时间段中的数据
    public function disNowSeason($str,$dateTime)
    {
        if(empty($str)){
            return array();
        }
        $dateTime = strtotime($dateTime);

        $expArray = array();
        $returnArray = array();

        $str = str_replace(array('"','[',']'),"",$str);
        $expArray = explode(',',$str);
        $newArray = array();
        $start = '';
        $end = '';


        foreach($expArray as $k=>$v){
            $newArray = explode('~',$v);
            $start = strtotime($newArray[0]);
            $end = strtotime($newArray[1]);

            if($start <= $dateTime){
                return $v;
            }

        }

        return array();

    }








}































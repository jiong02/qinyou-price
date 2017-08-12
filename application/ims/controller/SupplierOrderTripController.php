<?php
namespace app\ims\controller;
use app\ims\model\OrderModel;
use app\ims\model\SupplierOrderTripModel;
use app\ims\model\SupplierOrderModel;
use app\ims\model\SupplierAccountDataModel;
use app\ims\model\SupplierGradeModel;
use app\ims\model\ImageModel;
use app\ims\model\SupplierOrderPassport;
use app\ims\model\CountryModel;
use app\ims\model\HotelModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\PlaceModel;
use app\ims\model\SupplierClientPassportModel;
use app\ims\model\HotelDefaultVehicleModel;

class SupplierOrderTripController extends BaseController
{
    //获得支付页面信息
    public function getPayInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getErr('没有该订单信息');
        }

        $orderInfo = $this->getOrderInfo($orderId);

        if(empty($orderInfo)){
            return getErr('没有该订单信息或订单已删除');
        }

        //有支付时间并时间超出当前时间，删除订单
        if(!empty($orderInfo['stop_to_pay']) && date('Y-m-d',time()) > $orderInfo['stop_to_pay']){

            $orderModel = new SupplierOrderModel();
            $orderInfo = $orderModel->where('id',$orderId)->find();
            $orderInfo->is_delete = 1;
            $orderInfo->save();

            return getErr('订单已删除或订单不存在');
        }

        $orderDataModel = new SupplierAccountDataModel();
        $gradeInfo = $this->formateData($orderDataModel->field('grade_way,ratio')->where('ims_supplier_account_data.account_id',$orderInfo['account_id'])->join('ims_supplier_grade','grade = ims_supplier_grade.id')->find());

        $imageModel = new ImageModel();
        $imageInfo = array();
        $imageInfo = $imageModel->where('image_uniqid',$orderInfo['pay_record'])->select();

        $returnInfo = array();
        $returnInfo['trip_id'] = $orderInfo['trip_id'];
        $returnInfo['order_id'] = $orderInfo['id'];
        $returnInfo['pay_type'] = $orderInfo['pay_type'];
        $returnInfo['stop_to_pay'] = strtotime($orderInfo['stop_to_pay']);
        $returnInfo['total_price'] = $orderInfo['total_price'];
        $returnInfo['grade_way'] = $gradeInfo['grade_way'];
//        $returnInfo['discount_price'] = $orderInfo['discount_price'];
        $returnInfo['start_time'] = $orderInfo['start_time'];
        $returnInfo['trip_adult'] = $orderInfo['trip_adult'];
        $returnInfo['trip_child'] = $orderInfo['trip_child'];
        $returnInfo['bg_pay_status'] = $orderInfo['bg_pay_status'];
        $returnInfo['bg_passport_status'] = $orderInfo['bg_passport_status'];
        $returnInfo['bg_flight_status'] = $orderInfo['bg_flight_status'];
        $returnInfo['pay_status'] = $orderInfo['pay_status'];
        $returnInfo['passport_status'] = $orderInfo['passport_status'];
        $returnInfo['flight_status'] = $orderInfo['flight_status'];
        $returnInfo['pay_record'] = $imageInfo;
        $returnInfo['now_time'] = time();
        $returnInfo['next'] = $orderInfo['next'];
        $returnInfo['ratio'] = $gradeInfo['ratio'];
//        $returnInfo['base_path'] = base64_encode('http://price.cheeruislands.com/uploads/file/');

        return getSucc($returnInfo);

    }

    public function getBourn()
    {
        $request = $this->request;
        $type = $request->param('type','');
        $id = $request->param('id',0);

        switch($type){
            case 'country':
                $countryModel = new CountryModel();

                $countryList = $this->formateData($countryModel->field('id as value,country_name as label')->select());

                if(!empty($countryList)){
                    foreach($countryList as $k=>$v){
                        $countryList[$k]['children'] = array();
                    }

                    return getSucc($countryList);
                }

            break;

            case 'place':
                $placeModel = new PlaceModel();

                $placeList = $this->formateData($placeModel->field('id as value,place_name as label')->where('country_id',$id)->select());

                if(!empty($placeList)){
                    return getSucc($placeList);
                }

            break;

            case 'hotel':
                $hotelModel = new HotelModel();

                $hotelInfo = $this->formateData($hotelModel->field('id as value,hotel_name as label')->where('place_id',$id)->select());

                if(!empty($hotelInfo)){
                    foreach($hotelInfo as $k=>$v){
                        $hotelInfo[$k]['children'] = array();
                    }

                    return getSucc($hotelInfo);
                }

            break;

            case 'room':
                $roomModel = new HotelRoomModel();

                $roomInfo = $this->formateData($roomModel->field('id as value,room_name as label')->where('hotel_id',$id)->select());

                if(!empty($roomInfo)){
                    return getSucc($roomInfo);
                }

            break;

        }

        return getErr('没有信息');

    }



    //修改支付信息
    public function updateTripPayInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);
        $tripId = $request->param('trip_id',0);
        $payType = $request->param('pay_type','');

        if(empty($orderId) || empty($tripId)){
            return getErr('订单已删除或没有该订单信息');
        }

        if(empty($payType)){
            return getErr('请输入支付类型');
        }

        $tripModel = new SupplierOrderTripModel();
        $tripInfo = $tripModel->where(['order_id'=>$orderId,'id'=>$tripId])->find();

        if(empty($tripInfo)){
            return getErr('订单不存在或订单已删除');
        }

        $tripInfo->pay_type = $payType;
        $tripInfo->pay_status = 1;
        $tripInfo->stop_to_client = date('Y-m-d',strtotime('+ 5 day',time()));
        $tripInfo->upload_pay_time = date('Y-m-d H:i:s',time());
        if($tripInfo->save()){
            return getSucc('修改成功');
        }

        return getSucc('支付方式没有修改');

    }


    //获得订单信息列表
    public function getOrderInfo($orderId)
    {
        if(empty($orderId)){
            return false;
        }

        $orderModel = new SupplierOrderModel();
        $tripModel = new SupplierOrderTripModel();

        $orderInfo = array();

        //查询订单信息
        $orderInfo = $this->formateData($orderModel->where(['id'=>$orderId,'is_delete'=>0])->find());

        if(empty($orderInfo)){
            return false;
        }

        $orderInfo = $this->formateData($orderInfo);

        $tripInfo = $this->formateData($tripModel->where(['order_id'=>$orderInfo['id']])->find());


        if(!empty($tripInfo)){
            $tripInfo['trip_id'] = $tripInfo['id'];
        }else{
            return false;
        }

        unset($tripInfo['order_id']);

        unset($tripInfo['id']);

        $return = array_merge($orderInfo,$tripInfo);

        return $return;
    }



    //删除订单图片资料信息
    public function deleteImage()
    {
        $request = $this->request;
        $imgId = $request->param('image_id','');
        $imgUniqid = $request->param('image_uniqid','');
        $deleteType = $request->param('delete_type','');
        $delStatus = 0;

        if(empty($imgId)){
            return getErr('没有该图片信息');
        }

        $imgModel = new ImageModel();
        $imageInfo = $this->formateData($imgModel->where(['id'=>$imgId])->find());

        if($imgModel->where(['id'=>$imgId])->delete()){
            @unlink('./uploads/file'.$imageInfo['image_path']);
            $delStatus = 1;
        }

        if(!empty($deleteType) && $deleteType == 'passport'){
            $passportModel = new SupplierOrderPassport();

            if(!$passportModel->where('passport_record',$imgUniqid)->delete()){
                return getErr($passportModel->getError());
            }
            $delStatus = 1;
        }

        if($delStatus == 1){
            return getSucc('图片删除成功');
        }else{
            return getErr('图片删除失败');
        }

    }

    //获得护照信息
    public function getPassportInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id','');

        $orderInfo = $this->getOrderInfo($orderId);

        if(empty($orderInfo)){
            return getErr('订单不存在或订单已删除');
        }

        if(!empty('stop_to_client') && date('Y-m-d',time()) > $orderInfo['stop_to_client']){
            $orderModel = new SupplierOrderModel();

            $orderModel = $orderModel->where('id',$orderId)->find();
            $orderModel->is_delete = 1;
            $orderModel->save();

            return getErr('订单已删除');
        }


        $passportModel = new SupplierOrderPassport();
        $passportInfo = array();
        $passportInfo = $passportModel->where(['order_id'=>$orderId,'is_delete'=>0,'status'=>1])->select();

        $imageModel = new ImageModel();

        $imageInfo = array();
        foreach($passportInfo as $k=>$v)
        {
            if(!empty($v['passport_record'])){
                $imageInfo[] = $imageModel->where('image_uniqid',$v['passport_record'])->find();
            }
        }

        $gradeInfo['grade_way'] = '';
        $orderDataModel = new SupplierAccountDataModel();
        $gradeInfo = $this->formateData($orderDataModel->field('grade_way,ratio')->where('ims_supplier_account_data.account_id',$orderInfo['account_id'])->join('ims_supplier_grade','grade = ims_supplier_grade.id')->find());

        $returnInfo = array();

        $returnInfo['pay_status'] = $orderInfo['pay_status'];
        $returnInfo['bg_pay_status'] = $orderInfo['bg_pay_status'];
        $returnInfo['passport_status'] = $orderInfo['passport_status'];
        $returnInfo['bg_passport_status'] = $orderInfo['bg_passport_status'];
        $returnInfo['flight_status'] = $orderInfo['flight_status'];
        $returnInfo['bg_flight_status'] = $orderInfo['bg_flight_status'];
        $returnInfo['contact_people'] = $orderInfo['contact_people'];
        $returnInfo['contact_phone'] = $orderInfo['contact_phone'];
        $returnInfo['passport_info'] = $passportInfo;
        $returnInfo['image_info'] = $imageInfo;
        $returnInfo['trip_adult'] = $orderInfo['trip_adult'];
        $returnInfo['trip_child'] = $orderInfo['trip_child'];
        $returnInfo['start_time'] = $orderInfo['start_time'];
        $returnInfo['grade_way'] = $gradeInfo['grade_way'];
        $returnInfo['stop_to_client'] = $orderInfo['stop_to_client'];
        $returnInfo['order_id'] = $orderInfo['id'];
        $returnInfo['trip_id'] = $orderInfo['trip_id'];

        return getSucc($returnInfo);

    }

    //添加图片记录
     public function addRecordInfo()
     {
         $request = $this->request;
         $addTpye = $request->param('add_type','');
         $uniqid = $request->param('image_uniqid','');
         $orderId = $request->param('order_id',0);

         if(empty($addTpye) || empty($orderId)){
             return getErr('订单不存在或，请输入图片类型');
         }

         $tripModel = new SupplierOrderTripModel();

         $tripInfo = $tripModel->where('order_id',$orderId)->find();

         if(empty($tripInfo)){
             return getErr('没有该订单信息');
         }

         //添加支付图片
         if($addTpye == 'pay'){
             //如果有支付图片uniqid存在，则继续用数据表中的图片uniqid
            if(!empty($tripInfo['pay_record'])){
                $res = $this->getFileImage($tripInfo['pay_record']);

                $returnInfo['id'] = $res->id;
                $returnInfo['image_uniqid'] = $res->image_uniqid;
                $returnInfo['image_path'] = $res->image_path;

                return getSucc($returnInfo);
            }

            //没有支付图片uniqid存在，新建uniqid存入图片模型数据表并存入订单表支付图片上
            if(empty($tripInfo['pay_record'])){
                if(empty($uniqid)){
                    $uniqid = uniqid();
                }

                if($res = $this->getFileImage($uniqid)){
                    $tripInfo->pay_record = $uniqid;
                    $tripInfo->save();

                    $returnInfo['id'] = $res->id;
                    $returnInfo['image_uniqid'] = $res->image_uniqid;
                    $returnInfo['image_path'] = $res->image_path;
                    return getSucc($returnInfo);
                }

            }

         }

         //添加护照图片
         if($addTpye == 'passport'){
             $uniqid = uniqid();
             if(!$res = $this->getFileImage($uniqid)){
                return getErr('添加图片失败');
            }

             $returnInfo['id'] = $res->id;
             $returnInfo['image_uniqid'] = $res->image_uniqid;
             $returnInfo['image_path'] = $res->image_path;

             $passportModel = new SupplierOrderPassport();

             $passportModel->passport_record = $uniqid;
             $passportModel->order_id = $orderId;

             if($passportModel->save()){
                return getSucc($returnInfo);
             }

             $imageModel = new ImageModel();

             $imageModel->where('id',$returnInfo['id'])->delete();

             @unlink('./uploads/files/'.$returnInfo['image_path']);

             return getErr('图片上传失败');
         }

         //添加航空图片
         if($addTpye == 'flight'){
             //如果有护照图片uniqid存在，则继续用数据表中的图片uniqid
             if(!empty($tripInfo['flight_record'])){
                 $res = $this->getFileImage($tripInfo['flight_record']);

                 $returnInfo['id'] = $res->id;
                 $returnInfo['image_uniqid'] = $res->image_uniqid;
                 $returnInfo['image_path'] = $res->image_path;

                 return getSucc($returnInfo);
             }

             //没有护照图片uniqid存在，新建uniqid存入图片模型数据表并存入订单表护照图片上
             if(empty($tripInfo['flight_record'])){
                 if(empty($uniqid)){
                     $uniqid = uniqid();
                 }

                 if($res = $this->getFileImage($uniqid)){
                     $tripInfo->flight_record = $uniqid;
                     $tripInfo->save();

                     $returnInfo['id'] = $res->id;
                     $returnInfo['image_uniqid'] = $res->image_uniqid;
                     $returnInfo['image_path'] = $res->image_path;
                     return getSucc($returnInfo);
                 }

             }
         }

        return getErr('图片上传失败');
     }

     //上传图片核心代码
    public function getFileImage($uniqid)
    {
        $request = $this->request;
        $imgModel = new ImageController();
        $var = $imgModel->fileUpload2($request,$uniqid);
        return $var;
    }



    //修改护照信息
    public function updatePassportInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);
        $contactPeople = $request->param('contact_people','');
        $contactPhone = $request->param('contact_phone','');

        if(empty($orderId) || empty($contactPeople) || empty($contactPhone)){
            return getErr('请填写完整数据');
        }

        $orderInfo = $this->getOrderInfo($orderId);

        if(empty($orderInfo)){
            return getErr('订单不存在或订单已删除');
        }

        $tripModel = new SupplierOrderTripModel();

        $tripInfo = $tripModel->where('order_id',$orderId)->find();

        if(empty($tripInfo)){
            return getErr('订单不存在');
        }

        $tripInfo->contact_people = $contactPeople;
        $tripInfo->contact_phone = $contactPhone;
        $tripInfo->stop_to_flight = date('Y-m-d',strtotime('+ 5 day',time()));
        $tripInfo->passport_status = 1;
        $tripInfo->upload_client_time = date('Y-m-d H:i:s',time());

        if($tripInfo->save()){
            return getSucc('修改成功');
        }

        return getErr('修改失败');
    }

    //活动航班信息
    public function getFlightInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getErr('订单不存在');
        }

        $orderInfo = $this->getOrderInfo($orderId);

        if(empty($orderInfo)){
            return getErr('订单不存在或订单已删除');
        }

        if(!empty($orderInfo['stop_to_flight']) && date('Y-m-d',time()) > $orderInfo['stop_to_flight']){
            $orderModel = new SupplierOrderModel();
            $orderModel = $orderModel->where('id',$orderId)->find();

            $orderModel->is_delete = 1;
            $orderModel->save();
            return getErr('订单已删除');

        }

        $tripModel = new SupplierOrderTripModel();

        $tripInfo = $this->formateData($tripModel->where('order_id',$orderId)->find());

        if(empty($tripInfo)){
            return getErr('订单已删除');
        }

        $hotelDefModel = new HotelDefaultVehicleModel();

        $hotelDefInfo = $this->formateData($hotelDefModel->where('hotel_id',$orderInfo['hotel_id'])->find());

        $imageModel = new ImageModel();
        $imageInfo = array();
        $imageInfo = $this->formateData($imageModel->where('image_uniqid',$orderInfo['flight_record'])->select());

        $returnInfo['pay_status'] = $orderInfo['pay_status'];
        $returnInfo['bg_pay_status'] = $orderInfo['bg_pay_status'];
        $returnInfo['passport_status'] = $orderInfo['passport_status'];
        $returnInfo['bg_passport_status'] = $orderInfo['bg_passport_status'];
        $returnInfo['flight_status'] = $orderInfo['flight_status'];
        $returnInfo['bg_flight_status'] = $orderInfo['bg_flight_status'];
        $returnInfo['time_difference'] = $tripInfo['time_difference'];
        $returnInfo['airport'] = $tripInfo['airport'];
        $returnInfo['order_name'] = $orderInfo['order_name'];
        if(!empty($hotelDefInfo['destination_time'])){
            $returnInfo['flight_time'] = $hotelDefInfo['destination_time'];
        }else{
            $returnInfo['flight_time'] = '00:00';
        }

        if(!empty($hotelDefInfo['destination_place'])){
            $returnInfo['destination_place'] = $hotelDefInfo['destination_place'];
        }else{
            $returnInfo['destination_place'] = '';
        }

        $returnInfo['stop_to_flight'] = $tripInfo['stop_to_flight'];
        $returnInfo['start_time'] = $orderInfo['start_time'];
        $returnInfo['trip_adult'] = $orderInfo['trip_adult'];
        $returnInfo['trip_child'] = $orderInfo['trip_child'];
        $returnInfo['stop_to_flight'] = $orderInfo['stop_to_flight'];
        $returnInfo['order_id'] = $orderInfo['id'];
        $returnInfo['trip_id'] = $orderInfo['trip_id'];
        $returnInfo['image_info'] = $imageInfo;

        return getSucc($returnInfo);

    }

    public function updateFlightInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(!$orderInfo = $this->getOrderInfo($orderId)){
            return getErr('订单已删除');
        }

        $tripModel = new SupplierOrderTripModel();
        $tripModel = $tripModel->where('order_id',$orderId)->find();
        $tripModel->flight_status = 1;
        $tripModel->upload_flight_time = date('Y-m-d H:i:s',time());

        if($tripModel->save()){
            return getSucc('修改成功');
        }

        return getErr('修改失败');
    }

    //获得指定用户所有订单信息
    public function getAllOrder()
    {
        $request = $this->request;
        $accountId = $request->param('account_id',0);

        if(empty($accountId)){
            return getErr('请登录系统');
        }

        $orderModel = new SupplierOrderModel();

        $orderInfo = $orderModel->field('id,order_name,start_time,end_time,trip_adult,trip_child,total_price,is_delete,status')->where('account_id',$accountId)->order('id desc')->select();

        if(!empty($orderInfo)){
            $orderInfo = $this->formateData($orderInfo);
            return getSucc($orderInfo);
        }

        return getErr('订单信息');

    }

    //搜索订单
    public function searchOrder()
    {
        $request = $this->request;
        $search = $request->param('search','');

        if(empty($search)){
            return getErr('请输入需要搜索的订单名称');
        }

        $orderModel = new SupplierOrderModel();

        $orderInfo = $orderModel->field('id,order_name,start_time,end_time,trip_adult,trip_child,total_price,is_delete,status')->where('order_name','like',"%$search%")->select();

        if(!empty($orderInfo)){
            $orderInfo = $this->formateData($orderInfo);

            return getSucc($orderInfo);
        }

        return getErr('没有订单');
    }

    //检测护照页面
    public function checkPayView()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getErr('订单不存在');
        }

        $orderInfo = $this->getOrderInfo($orderId);

        if(empty($orderInfo)){
            return getErr('订单不存在或订单已取消');
        }

        $imageModel = new ImageModel();
        $imageList = $imageModel->field('id,image_uniqid,image_path,image_category')->where('image_uniqid',$orderInfo['pay_record'])->select();

        $returnInfo['id'] = $orderInfo['id'];
        $returnInfo['trip_id'] = $orderInfo['trip_id'];
        $returnInfo['order_name'] = $orderInfo['order_name'];
        $returnInfo['start_time'] = $orderInfo['start_time'];
        $returnInfo['total_price'] = $orderInfo['total_price'];
        $returnInfo['trip_adult'] = $orderInfo['trip_adult'];
        $returnInfo['trip_child'] = $orderInfo['trip_child'];
        $returnInfo['upload_pay_time'] = $orderInfo['upload_pay_time'];
        $returnInfo['pay_type'] = $orderInfo['pay_type'];
        $returnInfo['bg_pay_status'] = $orderInfo['bg_pay_status'];
        $returnInfo['pay_status'] = $orderInfo['pay_status'];
        $returnInfo['img'] = $imageList;

        return getSucc($returnInfo);

    }

    //检测护照功能
    public function checkPay()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);
        $action = $request->param('action','');

        $tripModel = new SupplierOrderTripModel();
        $tripInfo = $tripModel->where('order_id',$orderId)->find();
        if(empty($tripInfo)){
            return getErr('订单不存在');
        }

        if($action == 'ok'){
            $tripInfo->bg_pay_status = 1;
            if($tripInfo->save()){
                return getSucc(['order_id'=>$orderId,'bg_pay_status'=>'1']);
            }
        }

        if($action == 'no_ok'){
            $tripInfo->pay_status = 0;
            $tripInfo->bg_pay_status =0;
            $tripInfo->stop_to_client = '';

            if($tripInfo->save()){
                return getSucc(['order_id'=>$orderId,'bg_pay_status'=>0,'pay_status'=>0]);
            }
        }

        return getErr('操作失败');
    }

    //检测护照页面
    public function checkPassportView()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getErr('订单不存在');
        }

        $orderInfo = $this->getOrderInfo($orderId);

        if(empty($orderInfo)){
            return getErr('订单不存在或订单已取消');
        }

        $cliPassModel = new SupplierClientPassportModel();

        $cliPassList = $this->formateData($cliPassModel->field('ims_supplier_order_client_passport.*,ims_image.id as image_id,image_path,image_category')->where('order_id',$orderId)->join('ims_image','image_uniqid = passport_record','LEFT')->select());

        $returnInfo['id'] = $orderInfo['id'];
        $returnInfo['trip_id'] = $orderInfo['trip_id'];
        $returnInfo['order_name'] = $orderInfo['order_name'];
        $returnInfo['start_time'] = $orderInfo['start_time'];
        $returnInfo['trip_adult'] = $orderInfo['trip_adult'];
        $returnInfo['trip_child'] = $orderInfo['trip_child'];
        $returnInfo['client_info'] = $cliPassList;
        $returnInfo['passport_status'] = $orderInfo['passport_status'];
        $returnInfo['bg_passport_status'] = $orderInfo['bg_passport_status'];

        return getSucc($returnInfo);

    }

    //检测护照
    public function checkPassport()
    {
        $request = $this->request;
        $passportList = json_decode($request->param('passport_list',''),true);
        $action = $request->param('save','');
        $orderId = $request->param('order_id',0);

        if(empty($passportList)){
            return getErr('护照信息不存在');
        }

        $cliPassModel = new SupplierClientPassportModel();

        if($cliPassModel->saveAll($passportList)){

            if($action == 'bg_save' && !empty($orderId)){
                $orderTripModel = new SupplierOrderTripModel();
                $orderTripModel = $orderTripModel->where('order_id',$orderId)->find();

                $orderTripModel->bg_passport_status = 1;
                if($orderTripModel->save()){
                    return getSucc('修改成功');
                }else{
                    return getErr('修改失败');
                }
            }

            return getSucc('修改成功');
        }

        return getErr('修改失败');
    }

    //查看后台所有的订单列表
    public function getBgOrderList()
    {
        $request = $this->request;
        $limit = $request->param('limit',0);
        $page = $request->param('page',5);

        $orderModel = new SupplierOrderModel();

        $orderList = $this->formateData($orderModel->field(
        'ims_supplier_order.id,
        order_name,start_time,end_time,ims_supplier_order.account_id,use_package,
        ims_supplier_order_trip.id as trip_id,pay_status,bg_pay_status,
        passport_status,bg_passport_status,flight_status,bg_flight_status,company_name,trip_adult,trip_child,ims_supplier_order.create_time')
        ->join('ims_supplier_order_trip','ims_supplier_order.id = ims_supplier_order_trip.order_id')
        ->join('ims_supplier_account_data','ims_supplier_order.account_id = ims_supplier_account_data.account_id','INNER')->limit($limit,$page)->order('id desc')->select());

        $orderCount = $this->formateData($orderModel->field(
            'ims_supplier_order.id,
        order_name,start_time,end_time,ims_supplier_order.account_id,use_package,
        ims_supplier_order_trip.id as trip_id,pay_status,bg_pay_status,
        passport_status,bg_passport_status,flight_status,bg_flight_status,company_name,trip_adult,trip_child')
            ->join('ims_supplier_order_trip','ims_supplier_order.id = ims_supplier_order_trip.order_id')
            ->join('ims_supplier_account_data','ims_supplier_order.account_id = ims_supplier_account_data.account_id','INNER')->count());

        $ceilPage = ceil($orderCount / 5);


        if(!empty($orderList)){
            $returnInfo['page'] = $ceilPage;
            $returnInfo['order_list'] = $orderList;

            return getSucc($returnInfo);
        }

        return getErr('订单列表不存在');

    }

    //检测飞行信息页面
    public function checkFlight()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);
        $action = $request->param('bg_flight','');

        if(empty($orderId)){
            return getErr('订单不存在');
        }

        $orderTripModel = new SupplierOrderTripModel();

        $tripInfo = $orderTripModel->where('order_id',$orderId)->find();

        if(empty($tripInfo)){
            return getErr('订单不存在');
        }

        if($action == 'bg_flight'){
            $tripInfo->bg_flight_status = 1;

            if($tripInfo->save()){
                return getSucc('修改成功');
            }
        }

        return getErr('修改失败');

    }

    /**
     * @name 删除订单
     * @auth Sam
     * @return \think\response\Json
     */
    public function cancelOrder()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getErr('订单不存在');
        }

        $orderModel = new SupplierOrderModel();

        $orderInfo = $orderModel->where('id',$orderId)->find();

        if(empty($orderInfo)){
            return getErr('订单不存在2');
        }

        $orderInfo->is_delete = 1;

        if($orderInfo->save()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');

    }




}




























?>
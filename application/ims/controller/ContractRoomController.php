<?php
namespace app\ims\controller;
use app\ims\model\ContractRoomModel;
use app\ims\model\HotelModel;
use app\ims\model\HotelRoomModel;

class ContractRoomController extends BaseController
{

    //获得费用信息内的房型列表
    public function getRoomList()
    {
        $request = $this->request;
        $seasonUnqid = $request->param('season_unqid','');
        $pricing = $request->param('pricing_mode','房型');
        $packageUnqid = $request->param('package_unqid','');

        $roomModel = new ContractRoomModel();
        if($pricing == '房型'){
            $roomModel = $roomModel->field('ims_contract_room.id,room_id,room_name,season_unqid,package_unqid')->join('ims_hotel_room','room_id = ims_hotel_room.id')->where(['season_unqid'=>$seasonUnqid,'pricing_mode'=>$pricing,'package_unqid'=>$packageUnqid])->select();
        }else{
            $roomModel = $roomModel->where(['season_unqid'=>$seasonUnqid,'pricing_mode'=>$pricing,'package_unqid'=>$packageUnqid])->find();
        }

        if(empty($roomModel)){
            return getErr('没有房型信息或人信息');
        }

        $roomModel = $this->formateData($roomModel);

        if($pricing == '人'){

            if(!empty($roomModel['adult_fare'])){
                $roomModel['adult_fare'] = json_decode($roomModel['adult_fare'],true);
            }else{
                $roomModel['adult_fare'] = array();
            }

            if(!empty($roomModel['child_fare'])){
                $roomModel['child_fare'] = json_decode($roomModel['child_fare'],true);
            }else{
                $roomModel['child_fare'] = array();
            }

            if(!empty($roomModel['baby_fare'])){
                $roomModel['baby_fare'] = json_decode($roomModel['baby_fare'],true);
            }else{
                $roomModel['baby_fare'] = array();
            }

        }

        return getSucc($roomModel);
    }

    //删除房型
    public function deleteRoom()
    {
        $request = $this->request;
        $roomId = $request->param('room_id',0);

        if(empty($roomId)){
            return getErr('没有该房型信息');
        }

        $roomModel = new ContractRoomModel();

        if($roomModel->where('id',$roomId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');


    }


    //获得房型信息
    public function getRoomInfo()
    {
        $request = $this->request;
        $roomId = $request->param('room_id',0);
        $unqid = $request->param('season_unqid','');
        $conId = $request->param('contract_id',0);
        $pkUnqid = $request->param('package_unqid','');

        if(empty($roomId) || empty($unqid) || empty($conId)){
            return getErr('没有该房型信息');
        }

        $roomModel = new ContractRoomModel();

        $roomInfo = array();
        $roomInfo = $roomModel->where(['contract_id'=>$conId,'room_id'=>$roomId,'season_unqid'=>$unqid,'package_unqid'=>$pkUnqid])->find();


        if(empty($roomInfo)){
            return getErr('没有该房型信息2');
        }

        $roomInfo = $this->formateData($roomInfo);
//var_dump($roomInfo);
        $hotelRoomModel = new HotelRoomModel();
        $hotelRoomModel = $hotelRoomModel->where(['id'=>$roomId])->find();

        if(!empty($hotelRoomModel)){
            $hotelRoomModel = $this->formateData($hotelRoomModel);
//$this->dumpExit($hotelRoomModel);
            $roomInfo['standard_adult'] = $hotelRoomModel['standard_adult'];
            $roomInfo['extra_adult'] = $hotelRoomModel['extra_adult'];
            $roomInfo['extra_child'] = $hotelRoomModel['extra_child'];
            $roomInfo['extra_logic'] = $hotelRoomModel['extra_logic'];
        }else{
            $roomInfo['standard_adult'] = '';
            $roomInfo['extra_adult'] = '';
            $roomInfo['extra_child'] = '';
            $roomInfo['extra_logic'] = '';
        }

        if(!empty($roomInfo['adult_fare'])){
            if(!is_array($roomInfo['adult_fare'])){
                $roomInfo['adult_fare'] = json_decode($roomInfo['adult_fare'],true);
            }

        }else{
            $roomInfo['adult_fare'] = [];
        }

        if(!empty($roomInfo['child_fare'])){
            if(!is_array($roomInfo['child_fare'])){
                $roomInfo['child_fare'] = json_decode($roomInfo['child_fare'],true);
            }
        }else{
            $roomInfo['child_fare'] = [];
        }

        if(!empty($roomInfo['baby_fare'])){
            if(!is_array($roomInfo['baby_fare'])){
                $roomInfo['baby_fare'] = json_decode($roomInfo['baby_fare'],true);
            }

        }else{
            $roomInfo['baby_fare'] = [];
        }

        if(!empty($roomInfo['extra_adult_fare'])){
            if(!is_array($roomInfo['extra_adult_fare'])){
                $roomInfo['extra_adult_fare'] = json_decode($roomInfo['extra_adult_fare'],true);
            }

        }else{
            $roomInfo['extra_adult_fare'] = [];
        }

        if(!empty($roomInfo['extra_child_fare'])){
            if(!is_array($roomInfo['extra_child_fare'])){
                $roomInfo['extra_child_fare'] = json_decode($roomInfo['extra_child_fare'],true);
            }

        }else{
            $roomInfo['extra_child_fare'] = [];
        }

        if(!empty($roomInfo['room_price'])){
            if(!is_array($roomInfo['room_price'])){
                $roomInfo['room_price'] = json_decode($roomInfo['room_price'],true);
            }

        }else{
            $roomInfo['room_price'] = [];
        }

        return getSucc($roomInfo);
    }

    //批量添加费用房型ID
    public function addRoomList()
    {
        $request = $this->request;
        $conId = $request->param('contract_id',0);
        $roomId = $request->param('room_id',0);
        $seasonUnqid = $request->param('season_unqid','');
        $packageUnqid = $request->param('package_unqid','');
        $pricingMode = $request->param('pricing_mode','房型');

        if( empty($conId) || empty($roomId) || empty($seasonUnqid) || empty($packageUnqid)){
            return getErr('请填写完整数据');
        }

        $roomModel = new ContractRoomModel();

        $roomModel->contract_id = $conId;
        $roomModel->room_id = $roomId;
        $roomModel->pricing_mode = $pricingMode;
        $roomModel->season_unqid = $seasonUnqid;
        $roomModel->package_unqid = $packageUnqid;

        if($roomModel->save()){
            return getSucc('添加成功');
        }

        return getErr('添加失败');
    }



    //房型数据
    public function roomData($request,$roomModel)
    {
        if(empty($request) || empty($roomModel)){
            return false;
        }


//        $roomModel->package_id = $request->param('package_id',0);
        $roomModel->contract_id = $request->param('contract_id',0);
        $roomModel->room_id = $request->param('room_id',0);
        $roomModel->pricing_mode = $request->param('pricing_mode','房型');
        $roomModel->total_price = $request->param('total_price','单人总价');
        $roomModel->adult_fare = $request->param('adult_fare','');
        $roomModel->child_fare = $request->param('child_fare','');
        $roomModel->baby_fare = $request->param('baby_fare','');
        $roomModel->extra_adult_fare = $request->param('extra_adult_fare','');
        $roomModel->extra_child_fare = $request->param('extra_child_fare','');
        $roomModel->room_price = $request->param('room_price','');
        $roomModel->child_is_bed = $request->param('child_is_bed',0);
        $roomModel->child_is_discount = $request->param('child_is_discount',0);
        $roomModel->baby_is_bed = $request->param('baby_is_bed',0);
        $roomModel->baby_is_discount = $request->param('baby_is_discount',0);
        $roomModel->season_unqid = $request->param('season_unqid','');
        $roomModel->package_unqid = $request->param('package_unqid','');
//$this->dumpExit($this->formateData($roomModel));
        return $roomModel;
    }

    //清空某些数据字段
    public function roomDataNull($roomModel)
    {
        $roomModel->pricing_mode = '房型';
        $roomModel->total_price = '单人总价';
        $roomModel->adult_fare = '';
        $roomModel->child_fare = '';
        $roomModel->baby_fare = '';
        $roomModel->extra_adult_fare = '';
        $roomModel->extra_child_fare = '';
        $roomModel->room_price = '';
        $roomModel->is_bed = 0;
        $roomModel->is_discount = 0;

        return $roomModel;

    }


    //修改房型信息
    public function updateRoomInfo()
    {
        $request = $this->request;
        $conRoomId = $request->param('id',0);

        //修改
        if(!empty($conRoomId)){
            $conRoomInfo = array();
            $conRoomInfo = ContractRoomModel::get(['id'=>$conRoomId]);
                if(empty($conRoomInfo)){
                    return getErr('没有改房型信息');
                }
//var_dump($request->param());
            $conRoomInfo = $this->roomData($request,$conRoomInfo);
//$this->dumpExit($conRoomInfo);
            if($conRoomInfo->save()){
                return getSucc('修改成功');
            }

            return getErr('修改失败');

        }else{//新增
            $conRoomInfo = new ContractRoomModel();
            $conRoomInfo = $this->roomData($request,$conRoomInfo);
            if($conRoomInfo->save()){
                return getSucc('修改成功');
            }

            return getErr('修改失败');

        }


    }

    //计算价格季内的日期
    public function countSeasonDate()
    {
        $request = $this->request;
        $startDate = $request->param('start_date','');
        $endDate = $request->param('end_date','');
        $seasonType = $request->param('season_type','');
        $notWork = $request->param('not_work',[]);
        $work = $request->param('work',[]);

        if(empty($startDate) || empty($endDate) || empty($seasonType)){
            return getErr('请输入开始时间与结束时间与价格季类型');
        }

        if(!empty($notWork)){
            $notWork = json_decode($notWork,true);
        }

        if(!empty($work)){
            $work = json_decode($work,true);
        }

        $countDate = 0;

        switch($seasonType){
            case '工作日':
                $countDate = $this->getAllDayCount($startDate,$endDate,$notWork);
            break;

            case '所有日期':
                $countDate = $this->countAllDate($startDate,$endDate,$notWork);
            break;

            case '周末':
                $countDate = $this->countWeek($startDate,$endDate,$notWork);
            break;

            case '某几天':
                $countDate = $this->getAllDayCount($startDate,$endDate,$notWork);

                $workCount = 0;
                $notWorkCount = 0;

                foreach($work as $k=>$v){
                    $workCount += $this->count_all_day($v['start_date'],$v['end_date']);
//                    echo $workCount.'|||';
                }

                if(!empty($notWork)){
                    foreach($work as $k=>$v){
                        foreach($notWork as $m=>$n){
                            if(strtotime($v['start_date']) <= strtotime($n['start_date']) && strtotime($v['end_date']) >= strtotime($n['end_date'])){
                                $notWorkCount += $this->count_all_day($n['start_date'],$n['end_date']);
//                                echo $n['start_date'];
                            }
                        }

                    }
                }

//                return $workCount.'|||'.$notWorkCount;
                $countDate = trim($workCount - $notWorkCount,'-');
//                $countDate = $countDate + 1;

            break;

            default :
                return getErr('没有价格季类型');
            break;

        }

        return getSucc($countDate);


    }






















}





























?>
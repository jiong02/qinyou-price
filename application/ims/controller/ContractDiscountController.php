<?php
namespace app\ims\controller;
use app\ims\model\ContractDiscountModel;
use app\ims\model\ContractPackageModel;
use app\ims\model\HotelRoomModel;

class ContractDiscountController extends BaseController
{
    //获得优惠的数据
    public function discountData($request,$disModel)
    {
        if(empty($request) || empty($disModel)){
            return false;
        }

        $disModel->package_name = $request->param('package_name',0);
        $disModel->contract_id = $request->param('contract_id',0);
        $disModel->room_id = $request->param('room_id',0);
        $disModel->discount_name = $request->param('discount_name','');
        $disModel->discount_type = $request->param('discount_type','');
        $disModel->in_advance = $request->param('in_advance',0);
        $disModel->check_in = $request->param('check_in',0);
        $disModel->pay = $request->param('pay',0);
        $disModel->discount = $request->param('discount',0);
        $disModel->discount_description = $request->param('discount_description','');
        $disModel->season_unqid = $request->param('season_unqid','');
        $disModel->package_unqid = $request->param('package_unqid','');

        return $disModel;
    }

    //添加/修改优惠信息
    public function updateDiscount()
    {
        $request = $this->request;

        $disList = json_decode($request->param('disInfo',''),true);

        if(empty($disList)){
            return getErr('请输入优惠信息');
        }

        $mun = 1;
        $returnError = '';
        foreach($disList as $k=>$v){
            if(!empty($v['id'])){
                $disModel = ContractDiscountModel::get($v['id']);
            }else{
                $disModel = new ContractDiscountModel();
            }

            $disModel->package_name = $v['package_name'];
            $disModel->contract_id = $v['contract_id'];
            $disModel->room_id = $v['room_id'];
            $disModel->discount_name = $v['discount_name'];
            $disModel->discount_type = $v['discount_type'];
            $disModel->discount_condition = !empty($v['discount_condition']) ? $v['discount_condition'] : '其他';
            $disModel->condition_content = $v['condition_content'];
            $disModel->in_advance = $v['in_advance'];
            $disModel->check_in = $v['check_in'];
            $disModel->pay = $v['pay'];
            $disModel->discount = $v['discount'];
            $disModel->discount_description = $v['discount_description'];
            $disModel->season_unqid = $v['season_unqid'];
            $disModel->package_unqid = $v['package_unqid'];

            if(!$disModel->save()){
                $returnError .= $mun;
            }
            $mun = $mun + 1;
        }

        if(!empty($returnError)){
            return getSucc('第'.$returnError.'条数据没有改变');
        }

        return getSucc('修改成功');
    }

    //删除优惠
    public function deleteDis()
    {
        $request = $this->request;
        $disId = $request->param('dis_id',0);

        $disModel = new ContractDiscountModel();

        if($disModel->where('id',$disId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');
    }


    //获得优惠信息列表
    public function getDisList()
    {
        $request = $this->request;
        $contractId = $request->param('contract_id',0);
        $seasonUnqid = $request->param('season_unqid','');

        if(empty($contractId) || empty($seasonUnqid)){
            return getErr('请输入合同信息与价格季信息');
        }

        $disModel = new ContractDiscountModel();

        $disList = array();
        $add = array();
        $noAdd = array();

        $add = $disModel->field('id,contract_id,package_name,room_id,discount_name,discount_type,season_unqid,package_unqid')->where(['contract_id'=>$contractId,'discount_type'=>'叠加','season_unqid'=>$seasonUnqid])->group('discount_name')->select();

        $noAdd = $disModel->field('id,contract_id,package_name,room_id,discount_name,discount_type,season_unqid,package_unqid')->where(['contract_id'=>$contractId,'discount_type'=>'不可叠加','season_unqid'=>$seasonUnqid])->group('discount_name')->select();

        !empty($add) ? $disList['add'] = $this->formateData($add) : $disList['add'] = array();

        !empty($noAdd) ? $disList['no_add'] = $this->formateData($noAdd) : $disList['no_add'] = array();

        return getSucc($disList);

    }

    //获得优惠信息
    public function getDisInfo()
    {
        $request = $this->request;
        $discountName = $request->param('discount_name','');
        $contractId = $request->param('contract_id',0);
        $seasonUnqid = $request->param('season_unqid',0);

        if(empty($discountName) || empty($contractId) || empty($seasonUnqid)){
            return getErr('请输入优惠信息和合同信息与价格季信息');
        }

        $disModel = new ContractDiscountModel();

        $disInfo = array();
        $disInfo = $disModel->where(['discount_name'=>$discountName,'contract_id'=>$contractId,'season_unqid'=>$seasonUnqid])->select();

        if(empty($disInfo)){
            return getErr('没有该优惠信息');
        }

        $disInfo = $this->formateData($disInfo);

        return getSucc($disInfo);

    }



    //添加优惠信息列表
    public function addDisList()
    {
        $request = $this->request;
        $disList = json_decode($request->param('dis_list',''),true);

        if(empty($disList)){
            return getErr('请输入需要添加的优惠信息');
        }

        $disModel = new ContractDiscountModel();
        $mysqlResult = $disModel->insertAll($disList);
        if(!empty($mysqlResult)){
            return getSucc('添加成功');
        }

        return getErr('添加失败');

    }

    //获得所有的酒店列表
    public function getAllHotelRoom()
    {
        $allRoom = array();
        $roomModel = new HotelRoomModel();
        $allRoom = $this->formateData($roomModel->field('id,hotel_id,room_name,room_ename')->select());

        if(empty($allRoom)){
            $allRoom = array();
        }

        return getSucc($allRoom);
    }

    //获得指定酒店房型列表
    public function getHotelRoomList()
    {
        $allRoom = array();
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('酒店不存在');
        }

        $roomModel = new HotelRoomModel();
        $allRoom = $this->formateData($roomModel->field('id,hotel_id,room_name,room_ename')->where('hotel_id',$hotelId)->select());

        if(empty($allRoom)){
            $allRoom = array();
        }

        return getSucc($allRoom);
    }










}




?>
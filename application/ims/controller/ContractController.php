<?php
namespace app\ims\controller;
use app\ims\model\ContractModel;
use app\ims\model\ContractSeasonModel;
use app\index\model\Contract;
use think\Db;
use app\ims\model\ContractItemModel;

class ContractController extends BaseController
{

    //获得合同信息
    public function getContractInfo()
    {
        $request = $this->request;
        $conId = $request->param('con_id',0);
        $hotelId = $request->param('hotel_id',0);

        if(empty($conId) || empty($hotelId)){
            return getErr('没有合同信息或酒店ID');
        }

        $conModel = ContractModel::get(['id'=>$conId,'date_type'=>'可用']);

        if(empty($conModel)){
            return getErr('没有该合同信息');
        }

        $conModel = $this->formateData($conModel);

        $notwork_date = '';

        $notwork_date = $this->formateData(ContractModel::all(['contract_number'=>$conModel['contract_number'],'date_type'=>'不可用']));

        if(!empty($notwork_date)){
            $conModel['notwork_date'] = $notwork_date;
        }else{
            $conModel['notwork_date'] = array();
        }


        $itemModel = new ContractItemModel();
        $mealList = $itemModel->where(['contract_id'=>$conId,'item_type'=>'餐食'])->select();
        $feesList = $itemModel->where(['contract_id'=>$conId,'item_type'=>'强制收费'])->select();

        //餐食信息
        if(!empty($mealList)){
            $conModel['meal'] = $mealList;
        }else{
            $conModel['meal'] = array();
        }

        if(!empty($feesList)){
            $conModel['fees'] = $feesList;
        }else{
            $conModel['fees'] = array();
        }

        //价格季信息
        $seasonModel = new ContractSeasonModel();
        $seasonInfo = $seasonModel->where(['hotel_id'=>$hotelId,'contract_id'=>$conId])->group('season_name')->select();


//        $seasonInfo = $this->formateData(ContractSeasonModel::all(['hotel_id'=>$hotelId,'contract_id'=>$conId]));

        if(!empty($seasonInfo)){
            $conModel['season'] = $seasonInfo;
        }else{
            $conModel['season'] = array();
        }

        return getSucc($conModel);

    }


    //删除合同
    public function deleteContract()
    {
        $request = $this->request;
        $conId = $request->param('contract_id',0);
        $hotelId = $request->param('hotel_id',0);
        $conNumber = $request->param('contract_number','');

        if(empty($conId) || empty($hotelId) || empty($conNumber)){
            return getErr('请输入网完整信息');
        }

        $conModel = new ContractModel();
        $conInfo = $conModel->where(['id'=>$conId,'hotel_id'=>$hotelId,'contract_number'=>$conNumber,'status'=>'1'])->select();

        if(empty($conInfo)){
            return getErr('没有合同信息');
        }

    }

    //删除不可用日期
    public function deleteNotWork()
    {
        $request = $this->request;
        $notId = $request->param('not_id',0);

        if(empty($notId)){
            return getErr('没有该日期信息');
        }

        $conModel = new ContractModel();
        if($conModel->where('id',$notId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');
    }

    //删除强制收费
    public function deleteItem()
    {
        $request = $this->request;
        $itemId = $request->param('item_id',0);

        if(empty($itemId)){
            return getErr('没有该收费信息');
        }

        $itemModel = new ContractItemModel();
        if($itemModel->where('id',$itemId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');
    }

    //删除某几天日期
    public function deleteSomeDay()
    {
        $request = $this->request;
        $dayId = $request->param('day_id',0);

        if(empty($dayId)){
            return getErr('没有日期信息');
        }

        $seasonModel = new ContractSeasonModel();

        if($seasonModel->where('id',$dayId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');

    }



    //获得合同列表
    public function getContractList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请输入该酒店ID');
        }

        $conModel = new ContractModel();

        $conList = $conModel->where(['hotel_id'=>$hotelId,'date_type'=>'可用','status'=>1])->group('contract_number')->select();


        if(!empty($conList)){
            return getSucc($conList);
        }
        return getErr('没有合同列表');

    }


    //添加多个合同
    public function addContractList()
    {
        $request = $this->request;
        $conList = json_decode($request->param('con_list',''),true);

        if(empty($conList)){
            return getErr('请输入合同列表');
        }

        $conModel = new ContractModel();

        if($conModel->saveAll($conList)){
            return getSucc('添加合同成功');
        }


        return getError('添加合同失败');
    }

    //删除价格季
    public function deleteSeason()
    {
        $request = $this->request;
        $seasonId = $request->param('season_id',0);

        if(empty($seasonId)){
            return getErr('没有该价格季信息');
        }

        $seasonModel = new ContractSeasonModel();

        if($seasonModel->where('id',$seasonId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');

    }


    //添加/修改合同信息
    public function contractData($request,$conModel)
    {
        if(empty($request) || empty($conModel)){
            return false;
        }

        $conModel->hotel_id = $request->param('hotel_id',0);
        $conModel->contract_number = $request->param('contract_number','');
        $conModel->date_type = $request->param('date_type','可用');
        $conModel->contract_start_date = $request->param('start_date','');
        $conModel->contract_end_date = $request->param('end_date','');
        $conModel->notwork_date = $request->param('notwork_date','');
        $conModel->mandatory_fees = $request->param('mandatory_fees','');

        return $conModel;
    }


    //修改合同信息
    public function updateContractInfo()
    {
        $request = $this->request;
        $conId = $request->param('con_id',0);
        $item = json_decode($request->param('item',''),true);

        if(empty($conId)){
            return getErr('修改失败');
        }

        //修改合同日期信息
        $conModel = ContractModel::get($conId);

        if(empty($conModel)){
            return getErr('没有该合同');
        }

        //查询是否有相同的合同日期
        $startDate = $request->param('contract_start_date','');
        $endDate = $request->param('contract_end_date','');
        $conDateModel = new ContractModel();
        $conDateModel = $this->formateData($conDateModel->where('contract_start_date <= '.$startDate.' and contract_end_date >='.$endDate)->select());

        if(!empty($conDateModel)){
            return getErr('合同日期已存在');
        }

        $conModel->hotel_id =  $request->param('hotel_id',0);
        $conModel->contract_number =  $request->param('contract_number','');
        $conModel->date_type =  '可用';
        $conModel->contract_start_date =  $request->param('contract_start_date','');
        $conModel->contract_end_date =  $request->param('contract_end_date','');
        $conModel->meals_remake =  $request->param('meals_remake','');

        $conModel->save();

        $conModel = array();

        //修改合同不可用信息
        $notWork = json_decode($request->param('notwork_date',''),true);

        $notWorkMun = 1;
        $newWorkMun = '';
        if(!empty($notWork)){
            foreach($notWork as $k=>$v){
                if(!empty($v['id'])){
                    $conModel = ContractModel::get($v['id']);
                }else{
                    $conModel = new ContractModel();
                }

                $conModel->hotel_id =  $request->param('hotel_id',0);
                $conModel->contract_number =  $request->param('contract_number','');
                $conModel->date_type =  '不可用';
                $conModel->contract_start_date =  $v['contract_start_date'];
                $conModel->contract_end_date =  $v['contract_end_date'];
                $conModel->meals_remake =  $request->param('meals_remake','');

                if(!$conModel->save()){
                    $newWorkMun .= $notWorkMun.',';
                }
                $notWorkMun = $notWorkMun + 1;
            }
        }

        //修改合同餐食信息
        $itemMun = 1;
        $newItemMun = '';
        foreach($item as $k=>$v){
            if(!empty($v['id'])){
                $itemModel = ContractItemModel::get($v['id']);
            }else{
                $itemModel = new ContractItemModel();
            }

            $itemModel->contract_id = $v['contract_id'];
            $itemModel->item_name = $v['item_name'];
            $itemModel->item_type = $v['item_type'];
            $itemModel->fare_type = $v['fare_type'];
            $itemModel->adult_fare = $v['adult_fare'];
            $itemModel->kids_fare = $v['kids_fare'];
            $itemModel->infant_fare = $v['infant_fare'];

            if(!$itemModel->save()){
                $newItemMun .= $itemMun.',';
            }

            $itemMun = $itemMun + 1;
        }

        if(strlen($newWorkMun) > 3){
            $newWorkMun = '不可用日期第'.trim($newWorkMun,',').'没有修改 ';
        }

        if(strlen($newItemMun) > 3){
            $newItemMun = '餐食费用第'.trim($newItemMun,',').'没有修改';
        }

        if(!empty($newWorkMun) || !empty($newItemMun)){
            $result = $newWorkMun.$newItemMun;
            return getSucc($result);
        }

            return getSucc('修改成功');

    }

    //添加修改合同的季节
    public function contractSeason()
    {
        $request = $this->request;
        $type = $request->param('type','');
        $unqid = $request->param('unqid','');
        $hotelId = $request->param('hotel_id',0);
        $contractId = $request->param('contract_id',0);
        $seasonName = $request->param('season_name','');
        $seasonId = $request->param('season_id',0);

        if($type == 'add'){
            $seasonModel = new ContractSeasonModel();

            if(empty($seasonModel)){
                return getErr('添加失败');
            }

            if(empty($unqid)){
                $unqid = uniqid();
            }

            $seasonModel->season_name = $seasonName;
            $seasonModel->hotel_id = $hotelId;
            $seasonModel->contract_id = $contractId;
            $seasonModel->season_unqid = $unqid;

            $result = $seasonModel->save();
            if(!empty($result)){
                return getSucc(['season_id'=>$seasonModel->id,'unqid_id'=>$unqid]);
            }
            return getErr('添加失败');
        }

        if($type == 'delete'){
            $seasonModel = ContractSeasonModel::get($seasonId);

            if(empty($seasonModel->season_unqid)){
                return getErr('没有该价格季信息');
            }

            $seasonUnqid = $seasonModel->season_unqid;

            $seasonModel = new ContractSeasonModel();
            $delInfo = $seasonModel->where('season_unqid',$seasonUnqid)->delete();

            if(!empty($delInfo)){
                return getSucc('删除成功');
            }

            return getErr('删除失败');
        }


    }

    //获得价格季的信息
    public function getSeasonInfo()
    {
        $request = $this->request;
        $seasonId = $request->param('season_id',0);

        if(empty($seasonId)){
            return getErr('请输入季节ID');
        }

        $seasonList = array();
        $seasonList = $this->formateData(ContractSeasonModel::get($seasonId));

        if(empty($seasonList)){
            return getErr('没有该季节信息');
        }

        if($seasonList['date_type'] == '某几天'){
            $seasonModel = new ContractSeasonModel();
            $seasonList2 = $seasonModel->field('id,someday_start as start_date,someday_end as end_date')->where(['season_unqid'=>$seasonList['season_unqid']])->select();

            $seasonList['type_date'] = $seasonList2;
        }

        return getSucc($seasonList);
    }

    //修改价格季信息
    public function updateSeasonInfo()
    {

        $request = $this->request;
        $seasonId = $request->param('season_id',0);
        $typeDate = json_decode($request->param('type_date',''),true);
        $seasonName = $request->param('season_name','');
        $seasonUnqid = $request->param('season_unqid','');
        $season_start_date = $request->param('season_start_date','');
        $season_end_date = $request->param('season_end_date','');
        $dateType = $request->param('date_type','工作日');
        $dateCount = $request->param('date_count',0);
        $contractId = $request->param('contract_id',0);
        $hotelId = $request->param('hotel_id',0);

        if($dateType == '某几天' && !empty($typeDate)){
            if(empty($seasonId)){
                return getErr('请输入价格季信息');
            }

            $seasonModel = ContractSeasonModel::get($seasonId);

            if(empty($seasonModel)){
                return getErr('没有该价格季信息');
            }

            $seasonModel->season_name = $seasonName;
            $seasonModel->season_unqid = $seasonUnqid;
            $seasonModel->season_start_date = $season_start_date;
            $seasonModel->season_end_date = $season_end_date;
            $seasonModel->date_type = $dateType;
            $seasonModel->date_count = $dateCount;
            $seasonModel->contract_id = $contractId;
            $seasonModel->hotel_id = $hotelId;

            $seasonModel->save();

            $seasonModel = array();
            foreach($typeDate as $k=>$v){
                if(!empty($v['id'])){
                    $seasonModel = ContractSeasonModel::get(['id'=>$v['id']]);
                }else{
                    $seasonModel = new ContractSeasonModel();
                }


                $seasonModel->season_name = $seasonName;
                $seasonModel->season_unqid = $seasonUnqid;
                $seasonModel->season_start_date = $season_start_date;
                $seasonModel->season_end_date = $season_end_date;
                $seasonModel->someday_start = $v['start_date'];
                $seasonModel->someday_end = $v['end_date'];
                $seasonModel->date_type = '某几天';
                $seasonModel->date_count = $dateCount;
                $seasonModel->hotel_id = $hotelId;
                $seasonModel->contract_id = $contractId;

                $seasonModel->save();
            }

            return getSucc('修改成功');

        }else{
            if(empty($seasonId)){
                return getErr('请输入价格季信息');
            }

            $seasonModel = ContractSeasonModel::get($seasonId);

            if(empty($seasonModel)){
                return getErr('没有该价格季信息');
            }

            $seasonModel->season_name = $seasonName;
            $seasonModel->season_unqid = $seasonUnqid;
            $seasonModel->season_start_date = $season_start_date;
            $seasonModel->season_end_date = $season_end_date;
            $seasonModel->date_type = $dateType;
            $seasonModel->date_count = $dateCount;
            $seasonModel->hotel_id = $hotelId;
            $seasonModel->contract_id = $contractId;

            if(!$seasonModel->save()){
                return getErr('保存失败,数据没有修改');
            }

            return getSucc('修改成功');
        }



    }





}



?>
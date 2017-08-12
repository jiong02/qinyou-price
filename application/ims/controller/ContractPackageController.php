<?php
namespace app\ims\controller;
use app\ims\model\ContractPackageModel;
use think\Exception;

class ContractPackageController extends BaseController
{
    //获得套餐列表
    public function getPackageList()
    {
        $request = $this->request;
        $unqid = $request->param('unqid',0);
        $hotelId = $request->param('hotel_id',0);
        $contractId = $request->param('contract_id',0);

        if(empty($unqid)){
            return getErr('没有套餐列表');
        }

        $packModel = new ContractPackageModel();
        $packList = array();
        $noBase = $packModel->field('season_unqid,package_name,package_unqid')->where(['season_unqid'=>$unqid])->where("package_name != '基础套餐'")->group('package_name')->select();
        $base = $this->formateData($packModel->field('season_unqid,package_name,package_unqid')->where(['season_unqid'=>$unqid])->where("package_name = '基础套餐'")->group('package_name')->select());

//        halt($base);

        if(!empty($noBase)){
            $packList['noBase'] = $this->formateData($noBase);
        }else{
            $packList['noBase'] = [];
        }

        if(!empty($base)){
//            $packList['base'] = $this->formateData($base);
            $packList['base'] = $base;
        }else{
            $packUnqid = uniqid();
            $packModel->package_name = '基础套餐';
            $packModel->season_unqid = $request->param('unqid','');
            $packModel->package_unqid = $packUnqid;
            $packModel->contract_id = $contractId;
            $packModel->hotel_id = $hotelId;
                if(empty($packModel->save())){
                    return getErr('添加基础套餐成功');
                }

            $packList['base'] = ['season_unqid'=>$request->param('unqid',''),'package_name'=>'基础套餐','package_unqid'=>$packUnqid];

        }


        return getSucc($packList);

    }



    //获得套餐信息
    public function getPackageInfo()
    {
        $request = $this->request;
        $seasonUnqid = $request->param('season_unqid','');
        $packageUnqid = $request->param('package_unqid','');
        $packType = $request->param('package_type','');

        if(empty($seasonUnqid) || empty($packageUnqid) || empty($packType)){
            return getErr('请输入完整信息');
        }

        $packageInfo = array();
        $packModel = new ContractPackageModel();

        $packageInfo = $packModel->where(['season_unqid'=>$seasonUnqid,'package_unqid'=>$packageUnqid,'package_type'=>$packType])->find();


        if($packageInfo['include_meal']){
            $meal = explode(',',$packageInfo['include_meal']);
            $packageInfo['include_meal'] = $meal;
        }


        if(!empty($packageInfo)){
            $packageInfo = $this->formateData($packageInfo);
            return getSucc($packageInfo);
        }

        return getErr('没有该套餐信息');


    }


    //添加多个套餐
    public function addPackageList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);
        $conId = $request->param('con_id',0);
        $unqid = $request->param('unqid',0);
        $packList = json_decode($request->param('pack_list',''),true);

        if(empty($hotelId) || empty($conId) || empty($packList) || empty($unqid)){
            return getErr('添加失败，请输入完整的数据');
        }

        foreach($packList as $k=>$v){
            $packModel = new ContractPackageModel();
            $packModel->hotel_id = $hotelId;
            $packModel->contract_id = $conId;
            $packModel->package_name = $v['package_name'];
            $packModel->season_unqid = $unqid;
            $packModel->package_unqid = uniqid();

            if(!$packModel->save()){
                return getErr('添加失败，请输入完整的数据');
            }
        }

        return getSucc('添加成功');
    }

    //删除套餐
    public function deletePackage()
    {
        $request = $this->request;
        $packageUnqid = $request->param('package_unqid','');

        if(empty($packageUnqid)){
            return getErr('没有该套餐信息');
        }

        $packModel = new ContractPackageModel();

        if($packModel->where('package_unqid',$packageUnqid)->delete()){
            return getSucc('删除成功');
        }else{
            return getErr('删除失败');
        }

    }


    //获得套餐信息
    public function PackageData($request,$packModel)
    {
        if(empty($request) || empty($packModel)){
            return false;
        }

        $activityList = json_decode($request->param('include_activity',''),true);
        $facilityList = json_decode($request->param('include_facility',''),true);

        foreach($activityList as $k=>$v){
            $acResult = 0;

            if(!empty($v['activity_name'])){
                $acResult += 1;
            }

            if(!empty($v['activity_type'])){
                $acResult += 1;
            }

            if(!empty($v['id'])){
                $acResult += 1;
            }
//echo $acResult.'activity<br/>';
            if($acResult == 0){

            }else if($acResult == 3){

            }else{
                return false;
            }


        }

        foreach($facilityList as $k=>$v){
            $acResult = 0;

            if(!empty($v['facility_name'])){
                $acResult += 1;
            }

            if(!empty($v['facility_type'])){
                $acResult += 1;
            }

            if(!empty($v['id'])){
                $acResult += 1;
            }
//echo $acResult.'facility<br/>';
            if($acResult == 0){

            }else if($acResult == 3){

            }else{
                return false;
            }
        }



        $packModel->hotel_id = $request->param('hotel_id',0);
        $packModel->contract_id = $request->param('contract_id','');
        $packModel->season_unqid = $request->param('season_unqid','');
        $packModel->package_name = $request->param('package_name','');
        $packModel->package_type = $request->param('package_type','标准成人');
        $packModel->include_meal = $request->param('include_meal','早餐');
        $packModel->is_add_bed = $request->param('is_add_bed',0);
        $packModel->bed_cost = $request->param('bed_cost',0);
        $packModel->include_night = $request->param('include_night',0);
        $packModel->include_go_vehicle = $request->param('include_go_vehicle','');
        $packModel->include_back_vehicle = $request->param('include_back_vehicle','');
        $packModel->include_activity = $request->param('include_activity','');
        $packModel->include_facility = $request->param('include_facility','');
        $packModel->include_others = $request->param('include_others','');
        $packModel->season_unqid = $request->param('season_unqid','');
        $packModel->package_unqid = $request->param('package_unqid','');

        return $packModel;

    }


    //修改套餐信息
    public function updatePackageInfo()
    {
        $request = $this->request;
        //$all代表适用于所有的表单
        $all = $request->param('all','');
        $packId = $request->param('package_id',0);

        $packageUnqid = $request->param('package_unqid');
        $seasonUnqid = $request->param('season_unqid');

        if(empty($packageUnqid) || empty($seasonUnqid)){
            return getErr('数据不完整');
        }

        //有标准成人 且 适用所有表单
        if(!empty($packId) && $all == 'all'){
            $packModel = new ContractPackageModel();

            //事务处理，第一次标准成人的修改
            $packModel = ContractPackageModel::get(['id'=>$packId]);

            $hotelId = $packModel->hotel_id;
            $contractId = $packModel->contract_id;
            $packName = $packModel->package_name;

            if(empty($packModel)){
                return getErr('表单不存在');
            }

            $packModel = $this->PackageData($request,$packModel);

            if(empty($packModel)){
                return getError('请选择完整设施或活动');
            }

            if(!$packModel->save()){
//                $this->dumpExit($packModel->getError());
                return getErr('修改标准成人失败');
            }

            $packModel = array();

            $packModel = ContractPackageModel::get(['hotel_id'=>$hotelId,'contract_id'=>$contractId,'package_name'=>$packName,'package_type'=>'额外成人']);

            //有 额外成人 数据则修改，没有则新增
            if(!empty($packModel)){
                $packModel = $this->PackageData($request,$packModel);
                $packModel->package_type = '额外成人';

                if(empty($packModel)){
                    return getError('请选择完整设施或活动');
                }

                $extAdult = $packModel->save();
            }else{
                $packModel = new ContractPackageModel();
                $packModel = $this->PackageData($request,$packModel);
                $packModel->package_type = '额外成人';

                if(empty($packModel)){
                    return getError('请选择完整设施或活动');
                }

                $extAdult = $packModel->save();
            }

            if(empty($extAdult)){
                return getErr('修改额外成人失败');
            }

            $packModel = array();

            //有 额外儿童 数据则修改，没有则新增
            $packModel = ContractPackageModel::get(['hotel_id'=>$hotelId,'contract_id'=>$contractId,'package_name'=>$packName,'package_type'=>'额外儿童']);

            if(!empty($packModel)){
                $packModel = $this->PackageData($request,$packModel);
                $packModel->package_type = '额外儿童';

                if(empty($packModel)){
                    return getError('请选择完整设施或活动');
                }

                $extChild = $packModel->save();
            }else{
                $packModel = new ContractPackageModel();
                $packModel = $this->PackageData($request,$packModel);
                $packModel->package_type = '额外儿童';

                if(empty($packModel)){
                    return getError('请选择完整设施或活动');
                }

                $extChild = $packModel->save();
            }

            if(empty($extChild)){
                return getErr('修改额外儿童失败');
            }


            $packModel = array();

            //有 婴儿房费 数据则修改，没有则新增
            $packModel = ContractPackageModel::get(['hotel_id'=>$hotelId,'contract_id'=>$contractId,'package_name'=>$packName,'package_type'=>'额外婴儿']);

            if(!empty($packModel)){
                $packModel = $this->PackageData($request,$packModel);
                $packModel->package_type = '额外婴儿';

                if(empty($packModel)){
                    return getError('请选择完整设施或活动');
                }

                $extChild = $packModel->save();
            }else{
                $packModel = new ContractPackageModel();
                $packModel = $this->PackageData($request,$packModel);
                $packModel->package_type = '额外婴儿';

                if(empty($packModel)){
                    return getError('请选择完整设施或活动');
                }

                $extChild = $packModel->save();
            }

            if(empty($extChild)){
                return getErr('修改婴儿房费失败');
            }

            return getSucc('修改成功');
        }


        //修改单独的表单
        if(!empty($packId) && $all == ''){
            $packModel = ContractPackageModel::get(['id'=>$packId]);

            if(empty($packModel)){
               return getErr('该表单不存在');
            }

            $packModel = $this->PackageData($request,$packModel);

            if(empty($packModel)){
                return getError('请选择完整设施或活动');
            }

            if($packModel->save()){
                return getSucc('修改成功');
            }

            return getErr('修改失败');

        }

        //新建表单
        if(empty($packId) && $all == ''){

            $packModel = new ContractPackageModel();

            $packModel = $this->PackageData($request,$packModel);

            if(empty($packModel)){
                return getError('请选择完整设施或活动');
            }

            if($packModel->save()){
                return getSucc('修改成功');
            }

            return getErr('修改失败');




        }


    }







}


?>
<?php
namespace app\ims\controller;

use app\ims\model\CountryModel;
use app\ims\model\PlaceModel;
use think\Request;
use app\ims\model\ImageModel;
use app\ims\model\PlaceNoBaseModel;

class PlaceController extends PrivilegeController
{
    public function getPlaceData($return = [])
    {
        $placeModel = new PlaceModel();
        $countryId = $this->post('country_id');
        $placeData = $placeModel->where('country_id',$countryId)->select();
        foreach ($placeData as $index => $item) {
            if ($item->place_type == '目的地'){
                $return['dest'] = $item->base_data;
            }elseif($item->place_type == '中转地'){
                $return['transfer'] = $item->base_data;
            }
        }
    }


    public function getPlaceList()
    {
        $placeModel = new PlaceModel();

        $placeList = $placeModel->field('id,country_id,place_name')->where('status',1)->select();

        $placeList = $placeList->toArray();

        return getSucc($placeList);

    }


    //查询指定的海岛信息
    public function getPlaceInfo()
    {
        $placeModel = new PlaceModel();
        $request = $this->request;
        $placeId = $request->param('place_id',0);

        if(empty($placeId)){
            return getErr('');
        }
        $placeData = array();
        $placeData = $placeModel
            ->field('id,country_id,place_name as name,place_ename as eng_name,official_language as language,island_number as number,place_area as area,is_island_rental as contain,visa_way as visa,plug_standard as electric,time_difference as equation,image_uniqid,voltage,place_desc,tourist_season')
            ->where('id',$placeId)
            ->find();

        $placeData = json_decode(json_encode($placeData),true);
        $placeData = $this->nullToChange($placeData);

        if($placeData['contain'] == 1){
            $placeData['contain'] = '是';
        }else{
            $placeData['contain'] = '否';
        }
        if(!empty($placeData['image_uniqid'])){
            $imageModel = new ImageModel();
            $imageInfo = '';
            $imageInfo = $imageModel->field('image_path as src')->where('image_uniqid',$placeData['image_uniqid'])->find();
            if(!empty($imageInfo['src'])){
                $placeData['img'] = $imageInfo['src'];
            }else{
                $placeData['img'] = array();
            }
        }

        if(!empty($placeData['tourist_season'])){
            $season = explode(',',$placeData['tourist_season']);
            $count = count($season);
            $placeData['start_month'] = !empty($season[0]) ? $season[0] : '';
            $placeData['end_month'] = !empty($season[1]) ? $season[1] : '';
        }

        if(!empty($placeData)){
            return getSucc($placeData);
        }
            return getErr(array());
    }

    //新增海岛信息
    public function addPlaceInfo()
    {
        $placeModel = new PlaceModel();
        $placeModel = $this->paramPlaceData($placeModel);
        if($placeModel->save()){
            return getSucc($placeModel->id);
        }else{
            return getErr([]);
        }
    }

    //修改海岛信息
    public function updatePlaceInfo()
    {
        $request = $this->request;
        $placeId = $request->param('id',0);
        $param = $request->param();
        $countryId = $request->param('country_id',0);
        if(empty($placeId) || empty($placeId)){
            return getErr('没有海岛ID 或 国家ID');
        }

        $placeModel = PlaceModel::get($placeId);
        $placeModel = $this->paramPlaceData($placeModel);


        if($placeModel->save()){
            return getSucc('修改数据成功');
        }else{
            return getErr('请检查数据是否没有修改');
        }

    }

    //获得
    public function paramPlaceData($placeModel)
    {
        $request = $this->request;
        $uniqid = $request->param('uniqid','');
        $placeModel->country_id = $request->param('country_id',0);
        $placeModel->place_type = $request->param('place_type','目的地');
        $placeModel->place_name = $request->param('name','');
        $placeModel->place_ename = $request->param('eng_name','');
        $placeModel->official_language = $request->param('language','');
        $placeModel->island_number = $request->param('number',0);
        $placeModel->place_desc = $request->param('place_desc','');
        $placeModel->place_area = $request->param('area',0);
        $placeModel->is_island_rental = $request->param('contain','是') == '是' ? 1 : 0;
        $placeModel->visa_way = $request->param('visa',1);
        $placeModel->plug_standard = $request->param('electric','');
        $placeModel->voltage = $request->param('voltage','');
        $placeModel->time_difference = $request->param('equation','');
        $placeModel->tips_custom = $request->param('tips','');
        $placeModel->image_uniqid = $uniqid;
        $placeModel->tourist_season = $request->param('start_month',0).','.$request->param('end_month',0);

        return $placeModel;
    }

    /**
     * 获取目的地海岛id和名字
     */
    public function getAllName()
    {
        $countryId = $this->post('id');
        $data = PlaceModel::where('place_type','目的地')
            ->where('country_id',$countryId)
            ->field('id,place_name as value')->select();
        return getSucc($data);
    }

    /**
     * @name 修改海岛状态
     * @auth Sam
     * @access public
     * @param mixed $placeId 海岛ID
     * @param mixed $status 海岛上下架状态
     * @return string
     */
    public function updatePlaceStatus()
    {
        $request = $this->request;
        $placeId = $request->param('place_id',0);
        $status = $request->param('status',0);

        if(empty($placeId) || !is_numeric($status)){
            return getErr('操作有误');
        }

        $placeModel = new PlaceNoBaseModel();
        $placeInfo = $placeModel->where('id',$placeId)->find();

        if(empty($placeInfo)){
            return getErr('海岛不存在');
        }

        $placeInfo->status = $status;

        if($placeInfo->save()){
            return getSucc('修改成功');
        }

        return getErr('修改失败');
    }






}
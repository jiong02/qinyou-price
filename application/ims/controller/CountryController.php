<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-23
 * Time: 10:29
 */

namespace app\ims\controller;

use app\common\controller\FileUpload;
use app\ims\model\CountryModel;
use app\ims\model\HotelModel;
use app\ims\model\ImageModel;
use think\Request;
use app\ims\model\PlaceModel;
use think\Db;

class CountryController extends PrivilegeController
{
    public function getCountryData($return = [])
    {
        $countryId = $this->post('country_id');
        if($countryModel = CountryModel::get($countryId)) {
            $return = $countryModel -> all_data;
        };
        return getSucc($return);
    }


    //查询指定国家与国家下的海岛信息
    public function getCountryPlaceData()
    {
        $request = $this->request;
        $countryId = $request->param('country_id',0);

        if(empty($countryId)){
            return getErr('查询失败，请输入ID');
            exit;
        }

        $countryInfo = '';
        $destList = '';
        $transferList = '';

        $countryModel = new CountryModel();

        //查询国家信息
        $countryInfo = $countryModel
            ->where('id',$countryId)
            ->field('id,country_name as name')
            ->find();


        $countryInfo = $this->formateData($countryInfo);
        $countryInfo = $this->nullToChange($countryInfo);

        if(!empty($countryInfo)){
            $placeModel = new PlaceModel();
            //查询目的地海岛
            $destList = $this->formateData($placeModel
                ->where([
                    'ims_place.country_id' => $countryInfo['id'],
                    'place_type' => '目的地',
                ])
                ->field('place_name as name,place_ename as eng_name,ims_place.id,image_uniqid,status')
                ->select());


//            $placeDB = Db::connect('mysql://test:CWcwtWv6V7uqO1Sq@120.24.189.240:3306/ims_new#utf8');
            // $placeDB = Db::connect('mysql://cheeru:%@r87naw9Sfhv%FL@rm-wz9p64l93868isk1d.mysql.rds.aliyuncs.com:3306/ims_new#utf8');
                $destList2 = Db::query("select place_name as name,place_ename as eng_name,ims_place.id,image_uniqid,ims_place.status from ims_place where ims_place.country_id = $countryInfo[id] AND place_type = '目的地' AND ims_place.status = 0");

                if(!empty($destList2) && is_array($destList2)){
                    $destList = array_merge($destList,$destList2);
                }


                if(!empty($destList)){
                    $hotelModel = new HotelModel();
                    $imageModel = new ImageModel();
                    $amount = 0;
                    $image_path['image_uniqid'] = '';

                    foreach($destList as $k=>$v){
                        $amount = $hotelModel->where('place_id',$v['id'])->count();
                        $destList[$k]['amount'] = $amount;

                        $image_path = $this->formateData($imageModel->where('image_uniqid',$v['image_uniqid'])->find());

                        $destList[$k]['image_path'] = !empty($image_path['image_path'])?$image_path['image_path']:'';

                    }
                }

            if(!empty($destList[0]['name'])){
                $destList = $this->formateData($destList);
                $destList = $this->nullToChange2($destList);
            }else{
                $destList = array();
            }

            //查询中转地海岛
            $transferList = $this->formateData($placeModel
                ->where([
                    'ims_place.country_id' => $countryInfo['id'],
                    'place_type' => '中转地',
                ])
                ->field('place_name as name,place_ename as eng_name,ims_place.id,image_uniqid,status')
                ->select());

                $transferList2 = $placeDB->query("select place_name as name,place_ename as eng_name,ims_place.id,image_uniqid,ims_place.status from ims_place where ims_place.country_id = $countryInfo[id] AND place_type = '中转地' AND ims_place.status = 0");

                if(!empty($transferList2) && is_array($transferList2)){
                    $transferList = array_merge($transferList,$transferList2);
                }


            if(empty($transferList)){
                $return['dest'] = $destList;
                $return['transfer'] = array();

                return getSucc($return);
            }

            if(!empty($transferList)){
                $hotelModel = new HotelModel();
                $imageModel = new ImageModel();
                $amount = 0;
                $image_path['image_uniqid'] = '';

                foreach($transferList as $k=>$v){
                    $amount = $hotelModel->where('place_id',$v['id'])->count();
                    $transferList[$k]['amount'] = $amount;

                    $image_path = $imageModel->where('image_uniqid',$v['image_uniqid'])->find();
                    $transferList[$k]['image_path'] = !empty($image_path['image_path'])?$image_path['image_path']:'';

                }
            }

            if(!empty($transferList[0]['name'])){
                $transferList = $this->formateData($transferList);
                $transferList = $this->nullToChange2($transferList);
            }else{
                $transferList = array();
            }


        }

        $return = ['country_nav'=>$countryInfo,'dest'=>$destList,'transfer'=>$transferList];
//var_dump($return);exit;
        if(!empty($countryInfo)){
            return getSucc($return);
        }

        return getErr('');

    }

    public function getCountryList($return = [])
    {
        $countryData = CountryModel::all();
        if (count($countryData)>0){
            foreach ($countryData as $index => $item) {
                $return[$index] = $item-> base_data;
            }
        }
        return getSucc($return);
    }

    public function addCountry()
    {
        $countryModel = new CountryModel();
        $countryModel = $this->formatModelData($countryModel);
        if($countryModel->save()){
            return getSucc($countryModel->id);
        }else{
            return getErr($countryModel->getError());
        }
    }

    public function updateCountry()
    {
        $countryId = $this->post('country_id');
        $countryModel = CountryModel::get($countryId);
        $countryModel = $this->formatModelData($countryModel);
        if($countryModel->save()){
            return getSucc('数据修改成功!');
        }else{
            return getErr($countryModel->getError());
        }
    }

    public function formatModelData($countryModel)
    {
        $uniqid = $this->post('uniqid');
        $countryModel->country_name = $this->post('name');
        $countryModel->country_ename = $this->post('eng_name');
        $countryModel->continent = $this->post('continent');
        $countryModel->official_language = $this->post('language');
        $countryModel->country_description = $this->post('desc');
        $countryModel->image_uniqid = $uniqid;
        $countryModel->national_flag = $this->getNationalFlagByUniqid($uniqid);
        return $countryModel;
    }

    public function getNationalFlagByUniqid($uniqid)
    {
        $imageModel = new ImageModel();
        return DS . 'uploads' . DS . 'country' . DS .$imageModel->getImagePathValueByUniqid($uniqid);

    }
    
    /**
     * 获取国家表的说有国家名字和id
     */

    public function getAllName()
    {
        $data = CountryModel::field('id,country_name as value')->select();
        return getSucc($data);
    }
}
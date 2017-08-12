<?php
namespace app\test\controller;
use app\test\model\ImsPlaceModel;
use app\test\model\ImsCountryModel;
use app\test\model\BaseModel;
use think\Request;
use app\test\model\PlaceSortModel;
use app\test\model\CountrySortModel;

class SortController extends BaseController
{
    /**
     * @name 获得国家列表（含排序）
     * @auth Sam
     * @return bool|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getSortCountryList()
    {
        $countryModel = new ImsCountryModel();

        $countryList = $countryModel->field("ims_country.id,ims_country.country_name,IFNULL(country_sort,0) as country_sort,ims_country.image_uniqid,image_category,image_path")->join('test.cheeru_country_sort','ims_country.id = test.cheeru_country_sort.country_id','LEFT')->join('ims_image','ims_country.image_uniqid = ims_image.image_uniqid','LEFT')->order('country_sort asc')->select();

        if(!empty($countryList)){
            return $this->formateData($countryList);
        }

        return '没有数据';
    }

    /**
     * @name 获得海岛列表
     * @auth Sam
     * @param Request $request
     * @return bool|mixed|string
     */
    public function getSortPlaceList(Request $request)
    {
        $countryId = $request->param('country_id',0);

        if(empty($countryId)){
            return '没有海岛信息';
        }


        $placeModel = new ImsPlaceModel();

        $placeList = $placeModel->field("ims_place.id,ims_place.country_id,ims_place.place_name,IFNULL(place_sort,0) as place_sort,cheeru_place_sort.id as sort_id")->join('test.cheeru_place_sort','ims_place.id = place_id','LEFT')->where('ims_place.country_id',$countryId)->order('place_sort asc')->select();

        if(!empty($placeList)){
            return $this->formateData($placeList);
        }

        return '数据不存在';

    }

    /**
     * @name 搜索国家排序信息
     * @auth Sam
     * @param Request $request
     * @return bool|mixed|string
     */
    public function searchCountrySortInfo(Request $request)
    {
        $sort = $request->param('sort',0);
        $countryName = $request->param('country_name','');

        if(!empty($sort)){
            $countryModel = new ImsCountryModel();

            $countryList = $countryModel->field("ims_country.id,ims_country.country_name,IFNULL(country_sort,0) as country_sort")->join('test.cheeru_country_sort','ims_country.id = test.cheeru_country_sort.country_id','LEFT')->order('country_sort desc,ims_country.id asc')->where('country_sort',$sort)->find();

            if(empty($countryList)){
                return '没有国家信息';
            }

            $countryList = $this->formateData($countryList);

            $placeModel = new ImsPlaceModel();

            $placeList = $placeModel->field("ims_place.id,ims_place.country_id,ims_place.place_name,IFNULL(place_sort,0) as place_sort")->join('test.cheeru_place_sort','ims_place.id = place_id','LEFT')->where('ims_place.country_id',$countryList['id'])->order('place_sort desc,ims_place.id asc')->select();

            if(empty($placeList)){
                return '没有海岛信息';
            }

            return $this->formateData($placeList);

        }


        if(!empty($countryName)){

            $countryModel = new ImsCountryModel();

            $countryList = $countryModel->field("ims_country.id,ims_country.country_name,IFNULL(country_sort,0) as country_sort")->join('test.cheeru_country_sort','ims_country.id = test.cheeru_country_sort.country_id','LEFT')->order('country_sort desc,ims_country.id asc')->where("ims_country.country_name like '%$countryName%'")->find();

            if(empty($countryList)){
                return '没有国家信息';
            }

            $countryList = $this->formateData($countryList);

            $placeModel = new ImsPlaceModel();

            $placeList = $placeModel->field("ims_place.id,ims_place.country_id,ims_place.place_name,IFNULL(place_sort,0) as place_sort")->join('test.cheeru_place_sort','ims_place.id = place_id','LEFT')->where('ims_place.country_id',$countryList['id'])->order('place_sort desc,ims_place.id asc')->select();

            if(empty($placeList)){
                return '没有海岛信息';
            }

            return $this->formateData($placeList);

        }

        return '没有国家信息';

    }

    /**
     * @name 修改海岛排序
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updatePlaceSort(Request $request)
    {
        $placeInfo = $request->param('country_place_info/a',array());

        if(empty($placeInfo) || !is_array($placeInfo)){
            return '数据不完整';
        }

        $placeModel = new ImsPlaceModel();

        if($placeModel->saveAll($placeInfo)){
            return '修改成功';
        }

        return '修改失败';

    }

    /**
     * @name 修改单一海岛排序
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateSinglePlaceSort(Request $request)
    {
        $place = $request->param('place_info/a',array());

        if(empty($place) || !is_array($place)){
            return '数据不完整';
        }

        $placeSortModel = new PlaceSortModel();
        $placeInfo = $placeSortModel->where(['country_id'=>$place['country_id'],'place_id'=>$place['place_id']])->find();

        if($place['place_sort'] == 0){
            $place['place_sort'] = 99;
        }


        if(!empty($placeInfo)){
            $placeInfo->country_id = $place['country_id'];
            $placeInfo->place_id = $place['place_id'];
            $placeInfo->place_name = $place['place_name'];
            $placeInfo->place_sort = $place['place_sort'];

            $result = $placeInfo->save();
        }else{
            $placeSortModel->country_id = $place['country_id'];
            $placeSortModel->place_id = $place['place_id'];
            $placeSortModel->place_name = $place['place_name'];
            $placeSortModel->place_sort = $place['place_sort'];

            $result = $placeSortModel->save();
        }

        if(!empty($result)){
            return '修改成功';
        }

        return '修改失败';

    }



    /**
     * @name 修改国家排序
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateCountrySort(Request $request)
    {
        $countryId = $request->param('country_id',0);
        $countryName = $request->param('country_name','');
        $countrySort = $request->param('country_sort',0);

        if(empty($countryId) || empty($countrySort) || empty($countryName)){
            return '数据不完整';
        }

        $couSortModel = new CountrySortModel();

        $couSortInfo = $couSortModel->where(['country_id'=>$countryId,'country_name'=>$countryName])->find();

        if(!empty($couSortInfo)){
            $couSortInfo->country_id = $countryId;
            $couSortInfo->country_name = $countryName;
            $couSortInfo->country_sort = $countrySort;

            $result = $couSortInfo->save();

        }else{
            $couSortModel->country_id = $countryId;
            $couSortModel->country_name = $countryName;
            $couSortModel->country_sort = $countrySort;

            $result = $couSortModel->save();

        }

        if(!empty($result)){
            return '修改成功';
        }

        return '修改失败';

    }





}

?>
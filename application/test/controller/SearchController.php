<?php
namespace app\test\controller;
use app\test\model\ImsPlaceModel;
use app\test\model\ImsCountryModel;
use think\Request;
use app\test\model\ImsRouteModel;
use app\test\model\ImsImageModel;

class SearchController extends BaseController
{
    /**
     * @name 获取海岛信息分页
     * @auth Sam
     * @param Request $request
     * @return bool|mixed|string
     */
       public function getPlacePage(Request $request)
       {
           $countryId = $request->param('country_id',0);
           $page = $request->param('page',0);
           $limit = $request->param('limit',9);

           $placeModel = new ImsPlaceModel();
           $countryModel = new ImsCountryModel();

           //查询海岛信息
           if(!empty($countryId)){

               $placeList = $placeModel->field("ims_place.id,ims_place.country_id,ims_place.place_name,IFNULL(place_sort,0) as place_sort,cheeru_place_sort.id as sort_id")->join('cheeru_test.cheeru_place_sort','ims_place.id = place_id','LEFT')->where('ims_place.country_id',$countryId)->order('place_sort asc')->limit($page,$limit)->select();

               if(!empty($placeList)){
                   return $this->formateData($placeList);
               }else{
                   return '没有海岛信息';
               }

           }else{//查询热门海岛信息
               $countryList = $countryModel->field("ims_country.id,ims_country.country_name,IFNULL(country_sort,0) as country_sort,ims_country.image_uniqid,image_category,image_path")->join('cheeru_test.cheeru_country_sort','ims_country.id = cheeru_test.cheeru_country_sort.country_id','LEFT')->join('ims_image','ims_country.image_uniqid = ims_image.image_uniqid','LEFT')->order('country_sort = 0 desc,country_sort asc')->select();

               $countryString = '';

               foreach($countryList as $k=>$v){
                    $countryString .= $v['id'].',';
               }

               $countryString = trim($countryString,',');

               $placeList = $placeModel->field("ims_place.id,ims_place.country_id,ims_place.place_name,IFNULL(place_sort,0) as place_sort,cheeru_place_sort.id as sort_id")->join('cheeru_test.cheeru_place_sort','ims_place.id = place_id','LEFT')->where("ims_place.country_id in ($countryString)")->order('place_sort = 0 desc,place_sort asc')->limit($page,$limit)->select();

                if(!empty($placeList)){
                    return $this->formateData($placeList);
                }else{
                    return '没有海岛信息';
                }

           }

       }

    /**
     * @name 前端页面搜索线路
     * @auth Sam
     * @param Request $request
     * @return bool|mixed|string
     */
        public function webSearchRoute(Request $request)
        {
            $search = $request->param('search','');
            $page = $request->param('page',0);
            $limit = $request->param('limit',5);
            $date = $request->param('date','');
            $day = $request->param('day',0);

            if(empty($search) && empty($page) && empty($date) && empty($day)){
                return '必须选择一项查询条件';
            }

            $searchResult = true;

            $searchResult = $this->searchCountryRoute($search,$date,$day,$page,$limit);

            if(!empty($searchResult)){
                return $searchResult;
            }

            $searchResult = $this->searchPlaceRoute($search,$date,$day,$page,$limit);

            if(!empty($searchResult)){
                return $searchResult;
            }

            return '没有线路信息';

        }

    /**
     * @name 查询国家下海岛信息
     * @param $countryName
     * @param $date
     * @param $day
     * @param $page
     * @param $limit
     * @return bool|mixed
     */
    public function searchCountryRoute($countryName,$date,$day,$page,$limit)
    {
        //国家或海岛条件
        if(!empty($countryName)){
            $countryModel = new ImsCountryModel();

            $countryInfo = $countryModel->where("country_name like '%$countryName%'")->find();

            if(empty($countryInfo)){
                return false;
            }

            $placeModel = new ImsPlaceModel();

            $placeList = $placeModel->where('country_id',$countryInfo->id)->select();

            if(empty($placeList)){
                return false;
            }

            $placeList = $this->formateData($placeList);
            $placeIdList = '';

            foreach($placeList as $k=>$v){
                $placeIdList .= $v['id'].',';
            }

            $placeIdList = trim($placeIdList,',');

            $map['destination_place_id'] = ['in',$placeIdList];
        }

        //日期条件
        if(!empty($date)){
            $map['start_time'] = ['<=',$date];
            $map['end_time'] = ['>=',$date];
        }

        //套餐条件
        if(!empty($day)){
            $map['package_name'] = ['like',"$day%"];
        }


        $imsRouteModel = new ImsRouteModel();

        $routeList  = $imsRouteModel->field("ims_route.id as route_id,route_name,route_code,image_uniqid,min_fare")->where($map)->limit($page,$limit)->select();

        if(empty($routeList)){
            return false;
        }

        $routeList = $this->formateData($routeList);

        $imsImageModel = new ImsImageModel();

        foreach($routeList as $k=>$v){
            $imageInfo = $imsImageModel->where('image_uniqid',$v['image_uniqid'])->find();

            if(!empty($imageInfo)){
                $routeList[$k]['image_category'] = $imageInfo->image_category;
                $routeList[$k]['image_path'] = $imageInfo->image_path;
            }else{
                $routeList[$k]['image_category'] = '';
                $routeList[$k]['image_path'] = '';
            }

        }


        $routeCount = $imsRouteModel->field('ims_route.id as route_id,route_name,route_code')->where($map)->count();

        $returnArr['route_list'] = $routeList;
        $returnArr['route_count_ceil'] = ceil($routeCount / 5);
        $returnArr['route_count'] = $routeCount;

        return $this->formateData($returnArr);
    }

    /**
     * @name 查询海岛下的线路
     * @auth Sam
     * @param $placeName
     * @param $date
     * @param $day
     * @param $page
     * @param $limit
     * @return bool|mixed
     */
    public function searchPlaceRoute($placeName,$date,$day,$page,$limit)
    {
        if(!empty($placeName)){
            $placeModel = new ImsPlaceModel();

            $placeInfo = $placeModel->where("place_name like '%$placeName%'")->find();

            if(empty($placeInfo)){
                return false;
            }

            $map['destination_place_id'] = $placeInfo->id;
        }

        //日期条件
        if(!empty($date)){
            $map['start_time'] = ['<=',$date];
            $map['end_time'] = ['>=',$date];
        }

        //套餐条件
        if(!empty($day)){
            $map['package_name'] = ['like',"$day%"];
        }

        $routeModel = new ImsRouteModel();



        $routeList = $routeModel->field('ims_route.id as route_id,route_name,route_code,ims_route.image_uniqid,min_fare')->where($map)->limit($page,$limit)->select();

        if(empty($routeList)){
            return false;
        }

        $routeList = $this->formateData($routeList);

        $imsImageModel = new ImsImageModel();

        foreach($routeList as $k=>$v){
            $imageInfo = $imsImageModel->where('image_uniqid',$v['image_uniqid'])->find();

            if(!empty($imageInfo)){
                $routeList[$k]['image_category'] = $imageInfo->image_category;
                $routeList[$k]['image_path'] = $imageInfo->image_path;
            }else{
                $routeList[$k]['image_category'] = '';
                $routeList[$k]['image_path'] = '';
            }

        }

        $routeCount = $routeModel->field('ims_route.id as route_id,route_name,route_code')->where($map)->count();

        $returnArr['route_list'] = $routeList;
        $returnArr['route_count_ceil'] = ceil($routeCount / 5);
        $returnArr['route_count'] = $routeCount;

        return $this->formateData($returnArr);
    }

}
?>
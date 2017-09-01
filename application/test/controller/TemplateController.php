<?php
namespace app\test\controller;
use app\test\model\ImsRouteModel;
use app\test\model\TemplateModel;
use think\Request;
use think\Validate;
use app\test\model\TemplateRouteModel;


class TemplateController extends BaseController
{
    /**
     * @name 获得模板列表
     * @auth Sam
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getTemplateList()
    {
/*        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $tempModel = new TemplateModel();

        $tempList = $tempModel->order('template_sort asc')->select();

        if(!empty($tempList)){
            $tempList = $tempList->toArray();
            return $tempList;
        }

        return '没有模板数据';
    }

    /**
     * @name 删除模板
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function deleteTempInfo(Request $request)
    {
/*        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $tempId = $request->param('temp_id',0);

        if(empty($tempId)){
            return '模板不存在';
        }

        $tempModel = new TemplateModel();

        $tempResult = $tempModel->where('id',$tempId)->delete();

        if($tempResult){
            return '删除成功';
        }
        return '删除失败';
    }

    /**
     * @name 新建模板
     * @auth Sam
     * @param Request $request
     * @return mixed|string
     */
    public function createTemplate(Request $request)
    {
/*        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $tempName = $request->param('temp_name','');
        $tempDesc = $request->param('temp_desc','');

        if(empty($tempName) || empty($tempDesc)){
            return '数据不完整';
        }

        $tempModel = new TemplateModel();

        $tempModel->template_name = $tempName;
        $tempModel->template_description = $tempDesc;

        if($tempModel->save()){
            return $tempModel->id;
        }

        return '添加失败';
    }

    /**
     * @name 修改模板
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateTempInfo(Request $request)
    {
        $tempInfo = $request->param('temp_info/a',array());

        if(empty($tempInfo) || !is_array($tempInfo)){
            return '数据不完整';
        }

        $tempModel = new TemplateModel();

        if(!empty($tempInfo['id'])){
            $result = $tempModel->update($tempInfo);
        }else{
            $result = $tempModel->save($tempInfo);
        }

//        $result = $tempModel->save($tempInfo);

        if($result){
            return '修改成功';
        }

        return '修改失败';

    }



    /**
     * @name 修改模板线路
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateTempRoute(Request $request)
    {
/*        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $tempRouteInfo = $request->param('temp_route_info/a',array());

        if(empty($tempRouteInfo) || !is_array($tempRouteInfo)){
            return '模板总数据为空';
        }

        $tempInfo = $tempRouteInfo['temp_info'];
        $routeInfo = $tempRouteInfo['route_info'];

        if(empty($tempInfo) || !is_array($tempInfo)){
            return '模板数据为空';
        }

        if(empty($routeInfo) || !is_array($routeInfo)){
            return '线路数据为空';
        }

        $tempModel = new TemplateModel();

        $tempRouteModel = new TemplateRouteModel();

        $tempResult = $tempModel->update($tempInfo);

        if(empty($tempResult)){
            return '修改模板失败';
        }

        foreach($routeInfo as $k=>$v){
            $tempRouteModel = new TemplateRouteModel();

            if(!empty($v['id'])){
                $tempRouteModel->update($v);
            }else{
                $tempRouteModel->save($v);
            }

        }


        return '修改成功';
    }

    /**
     * @name 获取选中的模板线路列表
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function getChooseTempRouteInfo(Request $request)
    {
/*        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $tempRouteId = $request->param('temp_route_id',0);

        if(empty($tempRouteId)){
            return '线路不存在';
        }

        $tempModel = new TemplateModel();
        $tempRouteModel = new TemplateRouteModel();

        $tempInfo = $tempModel->where('id',$tempRouteId)->find();

        if(empty($tempInfo)){
            return '没有模板数据';
        }

        $tempInfo = $tempInfo->toArray();


        $bannerInfo = $tempRouteModel->where(['temp_id'=>$tempRouteId,'is_carousel_banner'=>1])->select();

        if(empty($bannerInfo)){
            $bannerInfo = array();
        }

        $bannerInfo = $bannerInfo->toArray();

        $noBannerInfo = $tempRouteModel->where(['temp_id'=>$tempRouteId,'is_carousel_banner'=>0])->select();

        if(empty($noBannerInfo)){
            $noBannerInfo = array();
        }

        $noBannerInfo = $noBannerInfo->toArray();

        $return['temp_info'] = $tempInfo;
        $return['banner_info'] = $bannerInfo;
        $return['no_banner_info'] = $noBannerInfo;

        return $return;

    }

    /**
     * @name 删除模板线路
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function deleteTempRoute(Request $request)
    {
        $routeId = $request->param('temp_route_id',0);

        if(empty($routeId)){
            return '没有模板线路ID';
        }

        $tempRouteModel = new TemplateRouteModel();

        if($tempRouteModel->where('id',$routeId)->delete()){
            return '删除成功';
        }

        return '删除失败';

    }

    /**
     * @name 前端获取模块与线路信息
     * @auth Sam
     * @return bool|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function webTempRoute()
    {
        $tempModel = new TemplateModel();
        $routeRouteModel = new \app\route\model\RouteModel();
        $routeRouteFareModel = new \app\route\model\RouteFareModel();
        $imageModel = new \app\ims\model\ImageModel();

        $tempInfo = $tempModel->order('template_sort asc')->select();

        if(empty($tempInfo)){
            return '没有板块信息';
        }

        $tempInfo = $this->formateData($tempInfo);
//halt($tempInfo);
        $ymdDate = date('Y-m-d',time());

        foreach($tempInfo as $k=>$v){
            $tempRouteModel = new TemplateRouteModel();

            $tempRouteList = $tempRouteModel->field('id as temp_route_id,temp_id,place_id,place_name,route_id,route_name,is_carousel_banner,sort')->where('temp_id',$v['id'])->order('sort asc')->select();
            $tempRouteList = $tempRouteList->toArray();
//halt($tempRouteList);
            foreach($tempRouteList as $m=>$n){
/*                $return = array();
                $arr = array();*/

                $routeInfo = $routeRouteModel->field('route_describe,image_uniqid,min_fare')->where('id',$n['route_id'])->find();
//                $routeInfo = $routeInfo->toArray();

                $routeFareInfo = $routeRouteFareModel->field('IFNULL(adult_fare,0) as adult_fare,IFNULL(child_fare,0) as child_fare,expired_date')->where("is_enable = 1 AND route_id = $n[route_id]")->find();
//                $routeFareInfo = $routeFareInfo->toArray();

                $imageInfo = $imageModel->field('image_uniqid,image_category,image_path')->where('image_uniqid',$routeInfo['image_uniqid'])->find();
//                $imageInfo = $imageInfo->toArray();

                $return['route_describe'] = $routeInfo['route_describe'];
                $return['image_uniqid'] = $routeInfo['image_uniqid'];
                $return['temp_route_id'] = $n['temp_route_id'];
                $return['temp_id'] = $n['temp_id'];
                $return['place_id'] = $n['place_id'];
                $return['place_name'] = $n['place_name'];
                $return['route_id'] = $n['route_id'];
                $return['route_name'] = $n['route_name'];
                $return['is_carousel_banner'] = $n['is_carousel_banner'];
                $return['adult_fare'] = $routeFareInfo['adult_fare'];
                $return['child_fare'] = $routeFareInfo['child_fare'];
                $return['image_category'] = $imageInfo['image_category'];
                $return['image_path'] = $imageInfo['image_path'];
                $return['min_fare'] = $routeInfo['min_fare'];

                $arr[$m] = $return;
            }


            if(!empty($arr)){
                $tempInfo[$k]['route_list'] = $arr;
            }else{
                $tempInfo[$k]['route_list'] = array();
            }

            $return = array();
            $arr = array();

        }

        return $tempInfo;

    }

/*    public function webTempRoute()
    {
        $tempModel = new TemplateModel();

        $tempInfo = $tempModel->order('template_sort asc')->select();

        if(empty($tempInfo)){
            return '没有板块信息';
        }

        $tempInfo = $this->formateData($tempInfo);

        $ymdDate = date('Y-m-d',time());

        foreach($tempInfo as $k=>$v){
            $tempRouteModel = new TemplateRouteModel();

            $routeInfo = $tempRouteModel->field("cheeru_template_route.id as temp_route_id,cheeru_template_route.temp_id,cheeru_template_route.place_id,cheeru_template_route.place_name,cheeru_template_route.route_id,cheeru_template_route.route_name,is_carousel_banner,cheeru_template_route.sort,ims_route.route_describe,IFNULL(adult_fare,0) as adult_fare,IFNULL(child_fare,0) as child_fare,expired_date,is_enable,ims_route.image_uniqid,ims_image.image_category,ims_image.image_path")->where("cheeru_template_route.temp_id = $v[id] AND ims_route.route_status = 3 AND is_enable = 1 AND expired_date >= $ymdDate")->join('ims_route.ims_route','cheeru_template_route.route_id = ims_route.ims_route.id','LEFT')->join('ims_route.ims_route_fare','cheeru_template_route.route_id = ims_route.ims_route_fare.route_id','LEFT')->join('ims_new.ims_image','ims_route.image_uniqid = ims_image.image_uniqid','LEFT')->order('cheeru_template_route.sort asc')->select();

            if(!empty($routeInfo)){
                $tempInfo[$k]['route_list'] = $routeInfo;
            }else{
                $tempInfo[$k]['route_list'] = array();
            }

        }

        return $tempInfo;

    }*/

}

?>
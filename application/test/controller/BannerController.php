<?php
namespace app\test\controller;
use app\test\model\BaseModel;
use think\Request;
use app\test\model\BannerModel;
use app\test\model\TestAccount;
use think\Validate;

class BannerController extends BaseController
{
    /**
     * @name 获得banner列表
     * @auth Sam
     * @param Request $request
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBannerList(Request $request)
    {
/*        $tokenResult = $this->checkAccountToken();

        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $bannerModel = new BannerModel();

        $bannerList = $bannerModel->field('id')->order('banner_sort asc')->select();

        if(empty($bannerList)){
            return '没有图片信息';
        }

        return $bannerList;
    }

    /**
     * @name 获得banner信息
     * @auth Sam
     * @param Request $request
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getBannerInfo(Request $request)
    {
        $banId = $request->param('banner_id',0);

        if(empty($banId)){
            return '数据不存在';
        }

/*        $result = $this->checkAccountToken();

        if($result !== 'ok'){
            return $result;
        }*/

        $bannerModel = new BannerModel();

        $bannerInfo = $bannerModel->where('id',$banId)->find();

        if(empty($bannerInfo)){
            return '数据不存在';
        }

        return $bannerInfo;

    }

    /**
     * @name 修改banner信息
     * @auth Sam
     * @param Request $request
     * @return array|string
     */
    public function updateBannerInfo(Request $request)
    {
        $bannerInfo = $request->param('banner_info/a',array());

        if(empty($bannerInfo) || !is_array($bannerInfo)){
            return '数据不完整';
        }

        $bannerModel = new BannerModel();

        $validateClass = new Validate($bannerModel->rules);

        $validateResult = $validateClass->check($bannerInfo);

        if(empty($validateResult)){
            return $validateClass->getError();
        }

        if(!empty($bannerInfo['id'])){
            $result = $bannerModel->update($bannerInfo);
        }else{
            $result = $bannerModel->save($bannerInfo);
        }

//        $result = $bannerModel->save($bannerInfo);


        if($result){
            return '修改成功';
        }

        return '修改失败';

    }

    /**
     * @name 删除banner
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function deleteBannerInfo(Request $request)
    {
        $bannerId = $request->param('banner_id',0);

        if(empty($bannerId) || !is_numeric($bannerId)){
            return '数据不完整';
        }

        $bannerModel = new BannerModel();
/*
        $bannerInfo = $bannerModel->where('id',$bannerId)->find();

        if(empty($bannerInfo)){
            return '首图不存在';
        }

        $result = $bannerInfo->delete();*/

        if($bannerModel->where('id',$bannerId)->delete()){
            return '删除成功';
        }

        return '删除失败';
    }

    /**
     * @name 获得所有banner信息
     * @auth Sam
     * @return bool|mixed|string
     */
    public function getAllBannerInfo()
    {
        $bannerModel = new BannerModel();

        $bannerList = $bannerModel->field('id,banner_route_id,banner_route_name,banner_image_uniqid,banner_image_path,banner_sort')->order('banner_sort asc')->select();

        if(!empty($bannerList)){
            return $this->formateData($bannerList);
        }

        return '没有banner信息';
    }




}

?>
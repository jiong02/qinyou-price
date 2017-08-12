<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/14
 * Time: 11:05
 */

namespace app\test\controller;


use app\components\Response;
use app\test\model\ImageModel;
use app\test\model\ImageSettingModel;
use think\Request;

class ImageSettingController extends BaseController
{
    public function addCaseImageData(Request $request)
    {
        $type = 'case';
        $data['image_type'] = $type;
        return $this->addImageData($request, $data);
    }

    public function modifyCaseImageData(Request $request)
    {
        $type = 'case';
        $data['image_type'] = $type;
        return $this->modifyImageData($request, $data);
    }

    public function getCaseImageData()
    {
        $type = 'case';
        $data['image_type'] = $type;
        return $this->getImageData($data);
    }

    public function uploadImage(Request $request)
    {
        $file = $request->file('image');
        $type = $request->get('type','image');
        $uniqueId = $request->param('image_uniqid','');
        if (empty($type)){
            return Response::Error('请上传文件的类型!');
        }
        if (empty($file)) {
            return Response::Error('请选择上传文件!');
        }
        if(empty($uniqueId)){
            $uniqueId = uniqid();
        }
        $uploadDir = $type.DS;
        $showPath = DS.'uploads'. DS . 'image'. DS ;
        $image = $file->move(UPLOADS_PATH.$uploadDir);
        if ($image) {
            $imageData['image_name'] = $image->getFilename();
            $imageData['image_old_name'] = $image->getInfo()['name'];
            $imageData['image_category'] = $type;
            $imageData['image_uniqid'] = $uniqueId;
            $imageData['image_path'] = $image->getSaveName();
            $imageData['image_md5'] = $image->md5();
            $imageData['image_extension'] = $image->getExtension();
            $imageModel = ImageModel::create($imageData);
            return Response::Success('图片上传成功',$showPath . $imageModel->image_path);
        } else {
            return Response::Error($file->getError());
        }
    }


    public function uploadImage2(Request $request)
    {
        $file = $request->file('image');
        $type = $request->get('type','image');
        $uniqueId = $request->param('image_uniqid','');
        if (empty($type)){
            return Response::Error('请上传文件的类型!');
        }
        if (empty($file)) {
            return Response::Error('请选择上传文件!');
        }
        if(empty($uniqueId)){
            $uniqueId = uniqid();
        }
        $uploadDir = $type.DS;
        $showPath = DS.'uploads'. DS . 'image'. DS ;
        $image = $file->move(UPLOADS_PATH.$uploadDir);
        if ($image) {
            $imageData['image_name'] = $image->getFilename();
            $imageData['image_old_name'] = $image->getInfo()['name'];
            $imageData['image_category'] = $type;
            $imageData['image_uniqid'] = $uniqueId;
            $imageData['image_path'] = $image->getSaveName();
            $imageData['image_md5'] = $image->md5();
            $imageData['image_extension'] = $image->getExtension();
            $imageModel = ImageModel::create($imageData);

            $imageReturn['image_uniqid'] = $uniqueId;
            $imageReturn['image_path'] = $showPath . $imageModel->image_path;

            return Response::Success('图片上传成功',$imageReturn);
        } else {
            return Response::Error($file->getError());
        }
    }

    protected function addImageData(Request $request, $data)
    {
        $imageSrc = $data['image_src'] = $request->param('image_src');
        $ImageDescription = $data['image_description'] = $request->param('description');
        $params = [
            'description'=>[$ImageDescription , 'require'],
            'image_src'=>[$imageSrc , 'require'],
        ];
        $this->checkAllParam($params);
        if($result = ImageSettingModel::create($data)) {
            return Response::Success('设置新增成功',$result->id);
        }
        return Response::Error('设置新增失败');
    }

    protected function modifyImageData(Request $request, $data)
    {
        $imageId = $request->param('image_id');
        $imageSrc = $data['image_src'] = $request->param('image_src');
        $ImageDescription = $data['image_description'] = $request->param('description');
        $params = [
            'image_src'=>[$imageSrc , 'require'],
            'description'=>[$ImageDescription , 'require'],
            'image_id'=>[$imageId , 'require'],
        ];
        $this->checkAllParam($params);
        if ($imageSettingModel = ImageSettingModel::get($imageId)){
            $data['id'] = $imageId;
            if($imageSettingModel->update($data)){
                return Response::Success('设置修改成功');
            }
            return Response::Error('设置修改失败');
        }
        return Response::Error('设置不存在');
    }

    protected function getImageData($data)
    {
        $imageSettingModel = new ImageSettingModel();
        $imageSettingResult = $imageSettingModel->where($data)->field('id as image_id, image_description as description, image_src')->find();
        if ($imageSettingResult){
            return Response::Success('案例信息获取成功',$imageSettingResult);
        }
        return Response::Success('案例信息获取失败');

    }
}
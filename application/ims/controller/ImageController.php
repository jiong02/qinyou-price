<?php
namespace app\ims\controller;

use app\ims\model\ImageModel;
use think\Request;

class ImageController extends BaseController
{
    public function fileUpload(Request $request)
    {
        $file = $request->file('file');
        $type = $request->get('type','file');
        if (empty($type)){
            return getErr('请上传文件的类型!');
        }
        if (empty($file)) {
            return getErr('请选择上传文件!');
        }
        $uploadDir = $type.DS;
        $info = $file->move(UPLOADS_PATH.$uploadDir);
        if ($info) {
            $imageData = $this->formatImageSqlData($info,uniqid(),$type);
            $imageModel = ImageModel::create($imageData);
            return getSucc($imageModel->image_uniqid);
        } else {
            return getErr($file->getError());
        }
    }

    public function multipleImageUpload(Request $request)
    {
        $file = $request->file('file');
        $type = $request->get('type','file');
        $imageUniqid = $request->get('image_uniqid',uniqid());
        if (empty($type)){
            return getErr('请上传文件的类型!');
        }
        if (empty($file)) {
            return getErr('请选择上传文件!');
        }
        $uploadDir = $type.DS;
        $info = $file->move(UPLOADS_PATH.$uploadDir);
        if ($info) {
            $imageData = $this->formatImageSqlData($info,$imageUniqid,$type);
            $imageModel = ImageModel::create($imageData);
            return getSucc(['image_id'=>$imageModel->id,'image_uniqid'=>$imageModel->image_uniqid,'src'=>$imageModel->image_path]);
        } else {
            return getErr($file->getError());
        }
    }

    public function formatImageSqlData($image,$uniqid,$type)
    {
        $imageData['image_name'] = $image->getFilename();
        $imageData['image_old_name'] = $image->getInfo()['name'];
        $imageData['image_category'] = $type;
        $imageData['image_uniqid'] = $uniqid;
        $imageData['image_path'] = $image->getSaveName();
        $imageData['image_md5'] = $image->md5();
        return $imageData;
    }

    public function deleteImageData()
    {
        $imageId = $this->request->param('image_id');
        $imageModel = ImageModel::get($imageId);
        if ($imageModel->delete()){
            return getSucc('图片删除成功');
        }
        return getErr('图片删除失败');
    }

    public function fileUpload2(Request $request,$uniqid)
    {
        $file = $request->file('file');
        $type = $request->get('type','file');
//        $uniqid = $request->param('image_uniqid',uniqid());
        if (empty($type)){
            return getErr('请上传文件的类型!');
        }
        if (empty($file)) {
            return getErr('请选择上传文件!');
        }
        $uploadDir = $type.DS;
        $info = $file->move(UPLOADS_PATH.$uploadDir);
        if ($info) {
            $imageData = $this->formatImageSqlData2($info,$uniqid,$type);
            $imageModel = ImageModel::create($imageData);

            return $imageModel;
        } else {
            return getErr($file->getError());
        }
    }

    public function fileUpload3(Request $request,$uniqid='')
    {
        $file = $request->file('file');
        $type = $request->get('type','file');
        $uniqid = $request->param('image_uniqid','');

        if(empty($uniqid)){
            $uniqid = uniqid();
        }


        if (empty($type)){
            return getErr('请上传文件的类型!');
        }
        if (empty($file)) {
            return getErr('请选择上传文件!');
        }
        $uploadDir = $type.DS;
        $info = $file->move(UPLOADS_PATH.$uploadDir);
        if ($info) {
            $imageData = $this->formatImageSqlData2($info,$uniqid,$type);
            $imageModel = ImageModel::create($imageData);

            return getSucc(['image_uniqid'=>$imageModel->image_uniqid,'image_id'=>$imageModel->id]);
        } else {
            return getErr($file->getError());
        }
    }

    public function formatImageSqlData2($image,$uniqid,$type)
    {
        $imageData['image_name'] = $image->getFilename();
        $imageData['image_old_name'] = $image->getInfo()['name'];
        $imageData['image_category'] = $type;
        $imageData['image_uniqid'] = $uniqid;
        $imageData['image_path'] = $image->getSaveName();
        $imageData['image_md5'] = $image->md5();
        return $imageData;
    }







}
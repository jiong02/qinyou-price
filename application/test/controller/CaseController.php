<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/12
 * Time: 17:25
 */

namespace app\test\controller;


use app\components\Response;
use app\test\model\CaseModel;
use think\Request;

class CaseController extends BaseController
{
    public function addCaseData(Request $request)
    {
        $caseTitle = $request->param('title');
        $caseDescription = $request->param('description');
        $caseContent = $request->param('content');
        $headImageSrc = $request->param('image_src');
        $params = [
            'title'=>[$caseTitle , 'require'],
            'description'=>[$caseDescription , 'require'],
            'content'=>[$caseContent , 'require'],
            'image_src'=>[$headImageSrc , 'require'],
        ];
        $this->checkAllParam($params);
        $data['case_title'] = $caseTitle;
        $data['case_description'] = $caseDescription;
        $data['case_content'] = $caseContent;
        $data['case_head_image_src'] = $headImageSrc;
        if($result = CaseModel::create($data)) {
            return Response::Success('案例新增成功',$result->id);
        }
        return Response::Error('案例新增失败');
    }

    public function modifyCaseData(Request $request)
    {
        $caseId = $request->param('case_id');
        $caseTitle = $request->param('title');
        $caseDescription = $request->param('description');
        $caseContent = $request->param('content');
        $headImageSrc = $request->param('image_src');
        $params = [
            'case_id'=>[$caseId , 'require'],
            'title'=>[$caseTitle , 'require'],
            'description'=>[$caseDescription , 'require'],
            'content'=>[$caseContent , 'require'],
            'image_src'=>[$headImageSrc , 'require'],
        ];
        $this->checkAllParam($params);
        if ($caseModel = CaseModel::get($caseId)){
            $data['id'] = $caseId;
            $data['case_title'] = $caseTitle;
            $data['case_description'] = $caseDescription;
            $data['case_content'] = $caseContent;
            $data['case_head_image_src'] = $headImageSrc;
            if($caseModel->update($data)){
                return Response::Success('案例修改成功');
            }
            return Response::Error('案例修改失败');
        }
        return Response::Error('案例不存在');
    }

    public function modifyCaseOrder(Request $request)
    {
        $caseId = $request->param('case_id');
        $caseOrder = $request->param('order');
        $params = [
            'case_id'=>[$caseId , 'require'],
            'order'=>[$caseOrder , 'require'],
        ];
        $this->checkAllParam($params);
        if ($caseModel = CaseModel::get($caseId)){
            $data['id'] = $caseId;
            $data['case_order'] = $caseOrder;
            if($caseModel->update($data)){
                return Response::Success('案例顺序修改成功');
            }
            return Response::Error('案例顺序修改失败');
        }
        return Response::Error('案例不存在');
    }

    public function deleteCaseData(Request $request)
    {
        $caseId = $request->param('case_id');
        $params = [
            'case_id'=>[$caseId , 'require'],
        ];
        $this->checkAllParam($params);
        if ($caseModel = CaseModel::get($caseId)){
            if($caseModel->delete()){
                return Response::Success('案例删除成功');
            }
            return Response::Error('案例顺序失败');
        }
        return Response::Error('案例不存在');
    }

    public function getPartOfCaseData(Request $request)
    {
        $offset = $request->param('offset', 1);
        $length = $request->param('length',6);
        $params = [
            'offset'=>[$offset , 'require|integer'],
            'length'=>[$length , 'require|integer'],
        ];
        $this->checkAllParam($params);
        $caseModel = new CaseModel();
        $caseData = $caseModel->limit($offset, $length)
            ->field('id as case_id, case_title as title,case_content as content,case_description as description, case_head_image_src as image_src')
            ->order('case_order')
            ->select();
        if ($caseData){
            if (!$caseData->isEmpty()){
                return Response::Success('案例信息获取成功',$caseData->toArray());
            }else{
                return Response::Success('当前无案例');
            }
        }
        return Response::Error('案例信息获取失败');
    }

    public function getAllCaseData()
    {
        $caseModel = new CaseModel();
        $caseData = $caseModel->field('case_order, id as case_id, case_title as title,case_content as content,case_description as description, case_head_image_src as image_src')
            ->order('case_order')
            ->select();
        if ($caseData){
            if (!$caseData->isEmpty()){
                return Response::Success('案例信息获取成功', $caseData->toArray());
            }else{
                return Response::Success('当前无案例');
            }
        }
        return Response::Error('案例信息获取失败');
    }

    public function getCaseDataByCaseId(Request $request)
    {
        $caseId = $request->param('case_id');
        $caseModel = new CaseModel();
        $caseCount = $caseModel->where('id',$caseId)->count();
        if ($caseCount){
            $caseData = $caseModel->where('id',$caseId)->field('case_title as title,case_content as content,case_description as description, case_head_image_src as image_src')->find();
            if ($caseData){
                return Response::Success('定制信息获取成功',$caseData->toArray());
            }
            return Response::Error('定制信息获取失败');
        }
        return Response::Error('当前案例不存在');
    }
}
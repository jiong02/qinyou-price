<?php
namespace app\index\controller;
use app\index\model\AuthRule;
use app\index\model\AuthGroup;
use app\index\model\AuthAccess;
use app\index\model\AuthRuleRecord;
use app\index\model\AuthGroupRecord;
use app\index\model\AuthAccessRecord;

class AuthRecord extends BaseController
{
    public function ruleRecord($type,$data,$oldData='',$mysql,$ip,$id=0)
    {
        if(empty($data) || empty($type)){
            return false;
        }

        $sessionInfo = $this->getSession();

        if($type == 'add'){
            $new_other = ['status'=>$data['new_status'],'type'=>$data['new_type'],'condition'=>$data['new_condition']];
            $record = new AuthRuleRecord();
            $record->type = 'add';
            $record->new_name = $data['new_name'];
            $record->new_title = $data['new_title'];
            $record->new_other = json_encode($new_other);
            $record->operator_id = $sessionInfo['emp_sn'];
            $record->opterator_name = $sessionInfo['emp_cn_name'];
            $record->ip = $ip;
            $record->sql_record = $mysql;
            $record->explain = $sessionInfo['emp_cn_name'].' 添加 【规则名称：'.$data['new_title'].'   控制器名称：'.$data['new_name'].'】'.' 权限';
            $record->save();
        }

        if($type == 'update' && !empty($id)){
            if(empty($oldData)){
                return false;
            }

            $new_other = ['status'=>$data['new_status'],'type'=>$data['new_type'],'condition'=>$data['new_condition']];
            $old_other = ['status'=>$oldData['status'],'type'=>$oldData['type'],'condition'=>$oldData['condition']];
            $record = new AuthRuleRecord();
            $record->type = 'update';
            $record->new_name = $data['new_name'];
            $record->new_title = $data['new_title'];
            $record->new_other = json_encode($new_other);
            $record->operator_id = $sessionInfo['emp_sn'];
            $record->opterator_name = $sessionInfo['emp_cn_name'];
            $record->ip = $ip;
            $record->old_name = $oldData['name'];
            $record->old_title = $oldData['title'];
            $record->old_other = json_encode($old_other);
            $record->sql_record = $mysql;
            $record->explain = $sessionInfo['emp_cn_name'].' 修改权限  旧权限：【规则名称：'.$oldData['title'].' 控制器名称：'.$oldData['name'].'】'.' 新权限：【规则名称：'.$data['new_title'].'   控制器名称'.$data['new_name'].'】';

            $record->save();
        }

    }

    //展示规则日志
    public function showRuleRecord()
    {
        return json(db('authrule_record')->select());
    }


    public function groupRecord($type,$data,$oldData='',$mysql,$ip,$id='')
    {
        if(empty($type) || empty($data)){
            return false;
        }

        $sessionInfo = $this->getSession();

        if($type == 'add'){
            $ruleInfo = array();
            $ruleInfo = db('auth_rule')->where("id in($data[new_rules])")->select();
            $ruleName = '';
            foreach($ruleInfo as $k=>$v){
                $ruleName .= $v['title'].',';
            }
            $ruleName = trim($ruleName,',');

            $groupRecord = new AuthGroupRecord();
            $groupRecord->type = 'add';
            $groupRecord->new_title = $data['new_title'];
            $groupRecord->new_status = $data['new_status'];
            $groupRecord->new_rules = $data['new_rules'];
            $groupRecord->new_rule_name = $ruleName;
            $groupRecord->operator_id = $sessionInfo['emp_sn'];
            $groupRecord->operator_name = $sessionInfo['emp_cn_name'];
            $groupRecord->ip = $ip;
            $groupRecord->sql_record = $mysql;
            $groupRecord->explain = $sessionInfo['emp_cn_name'].' 添加用户组 '.'【'.$data['new_title'].'】';

            $groupRecord->save();
        }


        if($type == 'update' && is_numeric($id)){
            $ruleInfo = array();
            $oldRuleInfo = array();
            $ruleInfo = db('auth_rule')->where("id in($data[new_rules])")->select();
            $oldRuleInfo = db('auth_rule')->where("id in ($oldData[rules])")->select();
            $ruleName = '';
            foreach($ruleInfo as $k=>$v){
                $ruleName .= $v['title'].',';
            }
            $ruleName = trim($ruleName,',');

            $oldRuleName = '';
            foreach($oldRuleInfo as $k=>$v){
                $oldRuleName .= $v['title'].',';
            }
            $oldRuleName = trim($oldRuleName,',');

            $groupRecord = new AuthGroupRecord();
            $groupRecord->type = 'update';
            $groupRecord->new_title = $data['new_title'];
            $groupRecord->old_title = $oldData['title'];
            $groupRecord->new_status = $data['new_status'];
            $groupRecord->old_status = $oldData['status'];
            $groupRecord->new_rules = $data['new_rules'];
            $groupRecord->old_rules = $oldData['rules'];
            $groupRecord->new_rule_name = $ruleName;
            $groupRecord->old_rule_name = $oldRuleName;
            $groupRecord->operator_id = $sessionInfo['emp_sn'];
            $groupRecord->operator_name = $sessionInfo['emp_cn_name'];
            $groupRecord->ip = $ip;
            $groupRecord->sql_record = $mysql;
            $groupRecord->explain = $sessionInfo['emp_cn_name'].' 修改用户组 '.'旧用户组：【'.$oldData['title'].'】'.' 新用户组：【'.$data['new_title'].'】';

            $groupRecord->save();
        }
    }

    //展示用户组日志
    public function showGruopRecord()
    {
        return json(db('authgroup_record')->select());
    }

    //用户组关系日志操作
    public function accessRecord($type,$data,$oldData='',$mysql,$ip)
    {
        if(empty($type) || empty($data)){
            return false;
        }

        $sessionInfo = $this->getSession();

        if($type == 'add'){
            $uidName = '';
            $uidName = db('employees')->where("emp_sn = $data[new_uid]")->field('emp_cn_name')->find();

            $groupInfo = '';
            $groupInfo = db('auth_group')->where("id = $data[new_group_id]")->field('title')->find();

            $accessRecord = new AuthAccessRecord();
            $accessRecord->type = 'add';
            $accessRecord->new_uid = $data['new_uid'];
            $accessRecord->new_group_id = $data['new_group_id'];
            $accessRecord->new_uid_name = $uidName['emp_cn_name'];
            $accessRecord->new_group_name = $groupInfo['title'];
            $accessRecord->operator_id = $sessionInfo['emp_sn'];
            $accessRecord->operator_name = $sessionInfo['emp_cn_name'];
            $accessRecord->ip = $ip;
            $accessRecord->sql_record = $mysql;
            $accessRecord->explain = $sessionInfo['emp_cn_name'].' 添加用户组关系 【 用户ID：'.$data['new_uid'].'  用户组关系ID：'.$data['new_group_id'].'】';

            $accessRecord->save();
        }


        if($type == 'update'){
            $uidName = '';
            $uidName = db('employees')->where("emp_sn = $data[new_uid]")->field('emp_cn_name')->find();

            $oldUidName = '';
            $oldUidName = db('employees')->where("emp_sn = $oldData[uid]")->field('emp_cn_name')->find();

            $groupInfo = '';
            $groupInfo = db('auth_group')->where("id = $data[new_group_id]")->field('title')->find();

            $oldGroupInfo = '';
            $oldGroupInfo = db('auth_group')->where("id = $oldData[group_id]")->field('title')->find();

            $accessRecord = new AuthAccessRecord();
            $accessRecord->type = 'update';
            $accessRecord->new_uid = $data['new_uid'];
            $accessRecord->old_uid = $oldData['uid'];
            $accessRecord->new_group_id = $data['new_group_id'];
            $accessRecord->old_group_id = $oldData['group_id'];
            $accessRecord->new_uid_name = $uidName['emp_cn_name'];
            $accessRecord->old_uid_name = $oldUidName['emp_cn_name'];
            $accessRecord->new_group_name = $groupInfo['title'];
            $accessRecord->old_group_name = $oldGroupInfo['title'];
            $accessRecord->operator_id = $sessionInfo['emp_sn'];
            $accessRecord->operator_name = $sessionInfo['emp_cn_name'];
            $accessRecord->ip = $ip;
            $accessRecord->sql_record = $mysql;
            $accessRecord->explain = $sessionInfo['emp_cn_name'].' 修改用户组关系  旧：【用户ID：'.$oldData['uid'].'  用户组ID：'.$oldData['group_id'].'】  新：【用户ID：'.$data['new_uid'].'  用户组ID：'.$data['new_group_id'].'】';

            $accessRecord->save();

        }

    }


    //展示用户关系日志记录
    public function showAccessRecord()
    {
        return json(db('authaccess_record')->select());
    }



}



























?>
<?php
namespace app\index\controller;
use app\index\model\AuthRule;
use app\index\model\AuthGroup;
use app\index\model\AuthAccess;
use think\Session;
use think\Validate;
use app\index\controller\AuthRecord;
use think\Db;

class Auth extends BaseController
{
    protected $ruleRule = [
        'name|规则标识' => 'require',
        'title|控制器名称' => 'require',
        'type|是否验证' => 'number|max:1',
        'status|状态' => 'number|max:1',
    ];

    protected $groupRule = [
        'title|用户组名称' => 'require',
        'status|状态' => 'require|max:1',
        'rules|规则ID群' => 'require',
    ];

    protected $accessRule = [
        'uid|用户ID'  => 'require|number',
        'group_id|用户组ID' => 'require|number',
    ];

    //添加规则
    public function addAuthRule()
    {
        $this->getSession();
        $request = $this->request;
        $param = $request->param();
        $name = $request->param('name','');
        $title = $request->param('title','');
        $type = $request->param('type',1);
        $status = $request->param('status',1);
        $condition = $request->param('condition','');

        if(empty($name) || empty($title)){
            return json('请填写规则名称与控制器名称');
            exit;
        }

        $validate = new Validate($this->ruleRule);

        $result = $validate->check($param);

        if(empty($result)){
            return json($validate->getError());exit;
        }

        $authRule = new AuthRule();
        $authRule->name = $name;
        $authRule->title = $title;
        $authRule->type = $type;
        $authRule->status = $status;
        $authRule->condition = $condition;
        $mysqlResult = $authRule->save();

        if(!empty($mysqlResult)){
            $recoData = [
                'new_name'=>$name,
                'new_title'=>$title,
                'new_type'=>$type,
                'new_status'=>$status,
                'new_condition'=>$condition
            ];

            $mysqlData = [
                'name' => $name,
                'title' => $title,
                'type' => $type,
                'status' => $status,
                'condition' => $condition,
            ];

            $mysql = '';
            $mysql = db('auth_rule')->fetchSql(true)->insert($mysqlData);

            $authRecord = new AuthRecord();
            $authRecord->ruleRecord('add',$recoData,'',$mysql,$request->ip());
            return json('添加成功');exit;
        }

        return json($authRule->getError());exit;
    }

    //修改验证规则
    public function updateAuthRule()
    {
        $this->getSession();
        $request = $this->request;
        $param = $request->param();

        if(empty($param['id'])){
            return json('请选择需要修改的规则');exit;
        }

        $name = $request->param('name','');
        $title = $request->param('title','');
        $type = $request->param('type',1);
        $status = $request->param('status',1);
        $condition = $request->param('condition','');

        if(empty($name) || empty($title)){
            return json('请填写规则名称与控制器名称');
            exit;
        }

        $validate = new Validate($this->ruleRule);

        $result = $validate->check($param);

        if(empty($result)){
            return json($validate->getError());exit;
        }

        $authRule = AuthRule::get($param['id']);
        $oldData = json_decode(json_encode($authRule),true);

        $authRule->name = $name;
        $authRule->title = $title;
        $authRule->type = $type;
        $authRule->status = $status;
        $authRule->condition = $condition;
        $result = $authRule->save();

        if(!empty($result)){
            $recoData = [
                'new_name'=>$name,
                'new_title'=>$title,
                'new_type'=>$type,
                'new_status'=>$status,
                'new_condition'=>$condition
            ];

            $mysql = '';
            $mysql = Db::table('ims_auth_rule')
                ->where('id',$param['id'])
                ->fetchSql(true)
                ->update([
                    'name' => $name,
                    'title' => $title,
                    'type' => $type,
                    'status' => $status,
                    'condition' => $condition,
                ]);

            $authRecord = new AuthRecord();
            $authRecord->ruleRecord('update',$recoData,$oldData,$mysql,$request->ip(),$param['id']);

            return json('修改成功');
        }

        return json($authRule->getError());

    }

    //展示规则页面
    public function authRuleView()
    {
        $ruleInfo = array();
        $ruleInfo = db('auth_rule')->select();
        return json($ruleInfo);
    }






     //添加用户组
     public function addAuthGroup()
     {
         $this->getSession();
         $request = $this->request;
         $param = $request->param();
         $title = $request->param('title','');
         $status = $request->param('status',1);
         $rules = $request->param('rules','');

         if(empty($title)){
             return json('请填写用户组名称');
             exit;
         }

         $validate = new Validate($this->groupRule);

         $result = $validate->check($param);

         if(empty($result)){
             return json($validate->getError());exit;
         }

         $authGroup = new AuthGroup();
         $authGroup->title = $title;
         $authGroup->status = $status;
         $authGroup->rules = $rules;
         $mysqlResult = $authGroup->save();

         if(!empty($mysqlResult)){
             $data = [
                 'new_title' => $title,
                 'new_status' => $status,
                 'new_rules' => $rules,
             ];

             $mysqlData = [
                 'title' => $title,
                 'status' => $status,
                 'rules' => $rules,
             ];

             $mysql = '';
             $mysql = db('auth_group')->fetchSql(true)->insert($mysqlData);

             $authRecord = new AuthRecord();
             $authRecord->groupRecord('add',$data,'',$mysql,$request->ip());
             return json('添加成功');exit;
         }

         return json($authGroup->getError());exit;
     }

    //修改用户组
    public function updateAuthGroup()
    {
        $this->getSession();
        $request = $this->request;
        $param = $request->param();

        if(empty($param['id'])){
            return json('请选择需要修改的用户组ID');exit;
        }

        $title = $request->param('title','');
        $status = $request->param('status',1);
        $rules = $request->param('rules','');

        if(empty($title) || empty($rules)){
            return json('请填写用户组名称与规则ID');
            exit;
        }

        $validate = new Validate($this->groupRule);

        $result = $validate->check($param);

        if(empty($result)){
            return json($validate->getError());exit;
        }

        $authGroup = AuthGroup::get($param['id']);
        $oldData = json_decode(json_encode($authGroup),true);

        $authGroup->title = $title;
        $authGroup->status = $status;
        $authGroup->rules = $rules;
        $result = $authGroup->save();

        if(!empty($result)){
            $data = [
                'new_title' => $title,
                'new_status' => $status,
                'new_rules' => $rules,
            ];

            $mysql = '';
            $mysql = Db::table('ims_auth_group')
                ->fetchSql(true)
                ->where('id',$param['id'])
                ->update([
                    'title' => $title,
                    'status' => $status,
                    'rules' => $rules,
                ]);

            $authRecord = new AuthRecord();
            $authRecord->groupRecord('update',$data,$oldData,$mysql,$request->ip(),$param['id']);
            return json('修改成功');
        }

        return json($authGroup->getError());
    }

    //获得用户组信息页面
    public function authGroupView()
    {
        $groupInfo = array();
        $groupInfo = db('auth_group')->select();

        foreach($groupInfo as $k=>$v){
            $ruleList = array();
            if(!empty($v['rules'])){
                $ruleList = db('auth_rule')->where("id in ($v[rules])")->select();
            }

            $groupInfo[$k]['group_list'] = $ruleList;
        }

        return json($groupInfo);
    }




    //添加用户关系组
    public function addAuthAccess()
    {
        $this->getSession();
        $request = $this->request;
        $param = $request->param();
        $uid = $request->param('uid',0);
        $groupId = $request->param('group_id',0);

        if(empty($uid) || empty($groupId)){
            return json('请填写用户ID与用户组ID');
            exit;
        }

        $validate = new Validate($this->accessRule);

        $result = $validate->check($param);

        if(empty($result)){
            return json($validate->getError());exit;
        }

        $accessRule = new AuthAccess();
        $accessRule->uid = $uid;
        $accessRule->group_id = $groupId;
        $mysqlResult = $accessRule->save();

        if(!empty($mysqlResult)){
            $data = [
                'new_uid' => $uid,
                'new_group_id' => $groupId
            ];

            $mysqlData = [
                'uid' => $uid,
                'group_id' => $groupId,
            ];

            $mysql = '';
            $mysql = Db::table('ims_auth_group_access')
                ->fetchSql(true)
                ->insert($mysqlData);

            $authRecord = new AuthRecord();
            $authRecord->accessRecord('add',$data,'',$mysql,$request->ip());
            return json('添加成功');exit;
        }

        return json($accessRule->getError());exit;
    }


    //修改用户关系组
    public function updateAuthAccess()
    {
        $this->getSession();
        $request = $this->request;
        $param = $request->param();
        $oldUid = $param['oldUid'];
        $oldGroupId = $param['oldGroup_id'];

        if(empty($param['uid']) || empty($param['group_id']) || empty($oldUid) || empty($oldGroupId)){
            return json('请选择需要修改的用户组ID');exit;
        }

        $uid = $request->param('uid',0);
        $groupId = $request->param('group_id',0);

        if(empty($uid) || empty($groupId)){
            return json('请填写用户组名称与规则ID');
            exit;
        }

        $validate = new Validate($this->accessRule);

        $result = $validate->check($param);

        if(empty($result)){
            return json($validate->getError());exit;
        }

        $accessRule = new AuthAccess();
        $result = $accessRule->save(['uid'=>$uid,'group_id'=>$groupId],['uid'=>$oldUid,'group_id'=>$oldGroupId]);


        if(!empty($result)){
            $data = [
                'new_uid' => $uid,
                'new_group_id' => $groupId
            ];

            $oldData = [
                'uid' => $oldUid,
                'group_id' => $oldGroupId
            ];

            $mysql = '';
            $mysql = Db::table('ims_auth_group_access')
                ->where("uid = '$oldUid' AND group_id = '$oldGroupId'")
                ->fetchSql(true)
                ->update([
                    'uid' => $uid,
                    'group_id' => $groupId,
                ]);


            $authRecord = new AuthRecord();
            $authRecord->accessRecord('update',$data,$oldData,$mysql,$request->ip());
            return json('修改成功');
        }

        return json($accessRule ->getError());

    }

    //用户关系表页面
    public function accessAuthView()
    {
            $accessInfo = array();
            $accessInfo = db('auth_group_access')
            ->field('uid,group_id,ims_auth_group.title,ims_auth_group.rules,acct_name')
            ->join('ims_emp_account','uid = emp_sn','LEFT')
            ->join('ims_auth_group','group_id = ims_auth_group.id','LEFT')
            ->select();

            return json($accessInfo);

    }





}






























?>
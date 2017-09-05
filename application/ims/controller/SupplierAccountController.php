<?php
namespace app\ims\controller;
use app\ims\model\ImageModel;
use app\ims\model\SupplierAccountModel;
use app\ims\model\SupplierAccountDataModel;
use app\ims\controller\ImageController;
use think\Db;
use think\Request;
use think\Validate;
use app\ims\model\CountryModel;
use app\ims\model\PlaceModel;
use app\ims\model\HotelModel;
use app\ims\model\SupplierGradeModel;
use app\ims\model\EmployeeAccountModel;
use app\ims\model\EmployeeModel;


class SupplierAccountController extends BaseController
{
    public function updateAccount()
    {
        $request = $this->request;
        $accId = $request->param('account_id', 0);

        $empAccModel = new EmployeeAccountModel();

        $empInfo = $empAccModel->where('account_name','woshiitbu')->find();

        if(empty($empInfo)){
            return getErr('没有最高权限人');
        }

        $empId = $request->param('now_employee_id',0);

        if($empInfo->id !== (int)$empId){
            return getErr('你没有权限修改，请找靓仔网管');
        }


        if(!empty($accId) && is_numeric($accId)){
            $accModel = SupplierAccountModel::get($accId);
        }

        if(empty($accModel)){
            $accModel = new SupplierAccountModel();
//            $accModel->login_count = 0;
        }

//        $login_count = 1;
//        $login_count = $login_count + $accModel->login_count;

        //供应商信息
        $accCheck = [
            'user_name' => $request->param('user_name',''),
            'password' => $request->param('password'),
            'employee_id' => $request->param('employee_id',0),
/*            'login_time' => date('Y-m-d H:i:s',time()),
            'login_ip' => $request->ip(),
            'login_count' => $login_count,*/
        ];

        //供应商资料信息
        $accDataCheck = [
            'company_name' => $request->param('company_name',''),
            'representative' => $request->param('representative',''),
            'travel_code' => $request->param('travel_code',''),
            'mobile_phone' => $request->param('mobile_phone',''),
            'email' => $request->param('email',''),
            'address' => $request->param('address',''),
            'fix_phone' => $request->param('fix_phone',''),
            'grade' => $request->param('grade','1'),
            'employee_id' => $request->param('employee_id',0),
        ];

        //验证账号信息
        $valiModel = new Validate($accModel->rule);
        $valiCheck = $valiModel->check($request->param());
        if(empty($valiCheck)){
            return getErr($valiModel->getError());
        }

        //清空验证数据
        $valiModel = array();
        $valiCheck = '';

        //验证账号资料数据
        $accDataModel = new SupplierAccountDataModel();
        $valiModel = new Validate($accDataModel->rule);

        if(!$valiModel->check($accDataCheck)){
            return getErr($valiModel->getError());
        }

        $accModel->save($accCheck);
        //修改账号信息
/*        if(!$accModel->save($accCheck)){
            return getErr('供应商账号没有改变1');
        }*/

        //修改账号资料信息
        $accDataModel = $accDataModel->where('account_id',$accModel->id)->find();

        if(empty($accDataModel)){
            $accDataModel = new SupplierAccountDataModel();
            $accDataModel->account_id = $accModel->id;
        }

        if(!$accDataModel->save($accDataCheck)){
            return getSucc('供应商信息没有改变2');
        }

        if(!empty($accModel->id)){
            return getSucc($accModel->id);
        }
            return getErr('修改失败');

    }

    public function getEmpList()
    {
        $empAccountModel = new EmployeeAccountModel();

        $empAccountInfo = $empAccountModel->field('ims_employee_account.id,ims_employee.account_name,employee_name')->join('ims_employee','ims_employee_account.account_name = ims_employee.account_name','LEFT')->select();

        return getSucc($empAccountInfo);

    }


    //获得渠道商等级列表
    public function getGradeList()
    {
        $gradeModel = new SupplierGradeModel();

        $gradeList = $this->formateData($gradeModel->field('id,grade_name,grade_way')->select());

        return getSucc($gradeList);


    }


    //搜索目的地
    public function searchBourn()
    {
        $request = $this->request;
        $search = $request->param('search','');
        $returnInfo = array();

        if(empty($search)){
            return getErr('请输入需要搜索的目的地');
        }

        $countryModel = new CountryModel();
        $placeModel = new PlaceModel();
        $hotelModel = new HotelModel();

        //如果国家有信息，则查询国家下海岛数量
        $countryList = $countryModel->field('id,country_name,country_ename,image_uniqid')->where('country_name','like',"%$search%")->select();


        if(!empty($countryList)){
            $countryList = $this->formateData($countryList);

            $placeCount = 0;
            foreach($countryList as $k=>$v){
                if(!empty($v['id'])){
                    $placeCount = $placeModel->where('country_id',$v['id'])->count();
                }

                $countryList[$k]['amount'] = $placeCount;
            }

            if(!empty($countryList)){
                $returnInfo['type'] = 'country';
                $returnInfo['list'] = $countryList;
                return getSucc($returnInfo);
            }

        }

        //有海岛信息，查询酒店数量
        $placeList = $placeModel->field('id,place_name,place_ename,image_uniqid,country_id')->where('place_name','like',"%$search%")->select();

        if(!empty($placeList)){
            $placeList = $this->formateData($placeList);

            $hotelCount = 0;
            $countryInfo = array();
            foreach($placeList as $k=>$v){
                if(!empty($v['id'])){
                    $hotelCount = $hotelModel->where('place_id',$v['id'])->count();

                    $countryInfo = $this->formateData($countryModel->field('id,country_name')->where('id',$v['country_id'])->find());
                }

                $placeList[$k]['amount'] = $hotelCount;
                $placeList[$k]['country'] = $countryInfo;
                $countryInfo = array();
            }

            if(!empty($placeList)){
                $returnInfo['type'] = 'place';
                $returnInfo['list'] = $placeList;

                return getSucc($returnInfo);
            }
        }

        //查询酒店信息
        $hotelList = $hotelModel->field('id,hotel_name,hotel_ename,place_id,country_id')->where('hotel_name','like',"%$search%")->select();

        $countryInfo = array();
        $placeInfo = array();
        if(!empty($hotelList)){
            $hotelList = $this->formateData($hotelList);

            foreach($hotelList as $k=>$v){
                $placeInfo = $this->formateData($placeModel->field('id,place_name,country_id')->where('id',$v['place_id'])->find());

                $hotelList[$k]['place'] = $placeInfo;

                $countryInfo = $this->formateData($countryModel->field('id,country_name')->where('id',$placeInfo['country_id'])->find());

                $hotelList[$k]['country'] = $countryInfo;
            }


            $returnInfo['type'] = 'hotel';
            $returnInfo['list'] = $hotelList;

            return getSucc($returnInfo);
        }

            return getErr('没有目的地信息');

    }



    //获得渠道商账号列表
    public function getAccountList(Request $request)
    {

        $accountModel = new SupplierAccountModel();
        $employeeId = $request->param('employee_id');

        if((int)$employeeId == 60){
            $accountList = $accountModel->field('ims_supplier_account.id,user_name,mobile_phone,grade')->join('ims_supplier_account_data',"ims_supplier_account.id = ims_supplier_account_data.account_id")->select();
        }else{
            $accountList = $accountModel->where('ims_supplier_account.employee_id',$employeeId)->field('ims_supplier_account.id,user_name,mobile_phone,grade')->join('ims_supplier_account_data',"ims_supplier_account.id = ims_supplier_account_data.account_id")->select();
        }


        if(!empty($accountList)){
            $accountList = $this->formateData($accountList);

            $gradeModel = new SupplierGradeModel();
            $gradeInfo['grade_way'] = '';

            foreach($accountList as $k=>$v){
                $gradeInfo = $gradeModel->where('id',$v['grade'])->find();

                $accountList[$k]['grade'] = $gradeInfo['grade_way'];
            }

            return getSucc($accountList);
        }
            return getErr('');

    }

    //获得账号信息
    public function getAccountInfo()
    {
        $request = $this->request;
        $accId = $request->param('acc_id',0);

        if(empty($accId)){
            return getErr('没有该账号信息');
        }

        $accountModel = new SupplierAccountModel();

        $accountInfo = $accountModel->field('ims_supplier_account.id,user_name,password,company_name,representative,travel_code,mobile_phone,email,address,fix_phone,grade,ims_supplier_account.employee_id')->where('ims_supplier_account.id',$accId)->join('ims_supplier_account_data','ims_supplier_account.id = account_id')->find();

//halt($accountInfo->toArray());
        if(!empty($accountInfo)){
            $empModel = new EmployeeModel();
            $empAccModel = new EmployeeAccountModel();

            $empAccInfo = $empAccModel->where('id',$accountInfo->employee_id)->find();

            if(empty($empAccInfo)){
                return '没有用户信息';
            }
//halt($empAccInfo->toArray());

            $empInfo = $this->formateData($empModel->where('account_name',$empAccInfo->account_name)->find());

            if(empty($empInfo)){
                $accountInfo['employee_name'] = '';
            }else{
                $accountInfo['employee_name'] = $empInfo['employee_name'];
            }


            $accountInfo = $this->formateData($accountInfo);

            $gradeModel = new SupplierGradeModel();

            $gradeInfo = $gradeModel->field('grade_way')->where('id',$accountInfo['grade'])->find();
            $accountInfo['grade'] = $gradeInfo['grade_way'];

            return getSucc($accountInfo);
        }

        return getErr('没有该账号信息2');
    }

    //账号登录
    public function accountLogin()
    {
        $request = $this->request;
        $userName = $request->param('user_name','');
        $password = $request->param('password','');

        $accountModel = new SupplierAccountModel();
        $accountInfo = $accountModel->where(['user_name'=>$userName,'password'=>$password,'is_delete'=>0,'status'=>1])->find();

        if(!empty($accountInfo)){
            $loginCount = $accountInfo->login_count + 1;

            $accDataModel = new SupplierAccountDataModel();

            $dataInfo = $accDataModel->where('account_id',$accountInfo['id'])->find();


            $accountInfo->login_ip = $request->ip();
            $accountInfo->login_count = $loginCount;
            $accountInfo->login_time = date('Y-m-d H-i:s',time());

            $accountInfo->save();

            $returnInfo['user_name'] = $accountInfo->user_name;
            $returnInfo['id'] = $accountInfo->id;

            return getSucc($returnInfo);
        }

        return getErr('账号不存在或账号已被禁用');
    }




}






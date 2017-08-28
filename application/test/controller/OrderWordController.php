<?php
namespace app\test\controller;
use app\test\controller\BaseController;
use think\Controller;
use app\test\model\OrderModel;
use app\test\model\OrderCustomerModel;
use think\Request;
use app\route\model\RouteModel;
use app\route\model\RouteDescriptionModel;
use app\route\model\RouteDescriptionHotelModel;
use app\route\model\RouteVehicleModel;
use app\ims\model\VehicleModel;

class OrderWordController extends BaseController
{
    public function outputOrderWord(Request $request)
    {
        $orderId = $request->param('order_id',0);
        if(empty($orderId) || !is_numeric($orderId)){
            return '订单不存在';
        }

        //订单信息
        $orderModel = new OrderModel();
        $orderInfo = $orderModel->where('id',$orderId)->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        $orderInfo = $orderInfo->toArray();

        //旅客信息
        $custModel = new OrderCustomerModel();

        $custList = $custModel->field('customer_name,customer_ename,customer_passport,validity_of_passport,customer_nationality,place_of_issue,customer_phone,customer_wechat')->where('order_id',$orderInfo['id'])->select();

        if(empty($custList)){
            $custList = array();
        }else{
            $custList = $custList->toArray();
        }

        //查找线路信息
        $routeModel = new RouteModel();
        $routeInfo = $routeModel->where('id',$orderInfo['route_id'])->find();

        if(empty($routeInfo)){
            return '线路信息已经被删除';
        }

        $routeInfo = $routeInfo->toArray();
//halt($routeInfo);
        //查找线路描述
        $routeDescModel = new RouteDescriptionModel();

        $routeDescList = $routeDescModel->field('ims_route.ims_route_description.id,ims_route.ims_route_description.route_id,ims_route.ims_route_description.departure_place_name,ims_route.ims_route_description.package_day,ims_route.ims_route_description.package_name,ims_route.ims_route_description_hotel.description_id,ims_route.ims_route_description_hotel.hotel_name,ims_route.ims_route_description_hotel.room_name,ims_route.ims_route_description_hotel.hotel_breakfast,ims_route.ims_route_description_hotel.hotel_lunch,ims_route.ims_route_description_hotel.hotel_dinner')->where('route_id',$orderInfo['route_id'])->join('ims_route.ims_route_description_hotel','ims_route_description.id = ims_route.ims_route_description_hotel.description_id')->select();

        if(empty($routeDescList)){
            $routeDescList = array();
        }

        $routeDescList = $routeDescList->toArray();

        $routeDescArr = array();

        if(!empty($routeDescList)){
            $startTime = $routeInfo['start_time'];
            foreach($routeDescList as $k=>$v){
                $meals = '';
                $routeDescArr[$k][] = $v['hotel_name'];
                $routeDescArr[$k][] = $v['room_name'];
                $routeDescArr[$k][] = '1';

                if($v['hotel_breakfast'] == '早餐包含'){
                    $meals = '含早餐 ';
                }

                if($v['hotel_lunch'] == '午餐包含'){
                    $meals .= '含午餐 ';
                }

                if($v['hotel_dinner'] == '晚餐包含'){
                    $meals .= '含晚餐 ';
                }

                $routeDescArr[$k][] = $startTime;

                $startTime = strtotime($startTime);
                $startTime = strtotime('+ 1 day',$startTime);
                $startTime = date('Y-m-d',$startTime);

                $routeDescArr[$k][] = $startTime;

                $routeDescArr[$k][] = $meals;

                $routeDescArr[$k][] = 1;
            }
        }

        //获取交通信息
        $routeVehicleModel = new RouteVehicleModel();

        $routeVehicleList = $routeVehicleModel->where('route_id',$routeInfo['id'])->select();

        if(!empty($routeVehicleList)){
            $routeVehicleList = $routeVehicleList->toArray();
        }

        $routeVehicleArr = array();

        $vehicleModel = new VehicleModel();

        $vehicleArr = array();

        if(!empty($routeVehicleList)){
            $startTime = $routeInfo['start_time'];
            foreach($routeVehicleList as $k=>$v){
                $vehicleInfo = $vehicleModel->where('id',$v['vehicle_id'])->find();

                if(!empty($vehicleInfo)){
                    $vehicleArr[$k][] = $vehicleInfo['departure_place_name'];
                    $vehicleArr[$k][] = $vehicleInfo['destination_name'];
                    $vehicleArr[$k][] = $startTime;

                    $startTime = strtotime($startTime);
                    $startTime = strtotime('+ 1 day',$startTime);
                    $startTime = date('Y-m-d',$startTime);

                    $vehicleArr[$k][] = $vehicleInfo['vehicle_name'];
                    $vehicleArr[$k][] = 1;

                }else{
                    $vehicleArr[$k][] = array();
                }

            }
        }


        require APP_PATH.'components/PHPWord_Sam/PHPWord.php';
        require APP_PATH.'components/PHPWord_Sam/PHPWord/IOFactory.php';

$PHPWord = new \PHPWord();

$section = $PHPWord->createSection();

//添加标题
$section->addText('行程委托服务协议',array('bold'=>true,'size'=>11),array('align'=>'center'));

//设置数据
$wordData['word_code'] = 'B-BTH003044007FZ';
$wordData['date_time'] = date('Y年m月d日',time());
$wordData['jia_fang'] = $orderInfo['linkman_name'];
$wordData['id_card'] = '';
$wordData['email'] = '';
$wordData['call_phone'] = $orderInfo['linkman_phone'];
$wordData['address'] = '';
//旅客信息
$wordData['customer_data']['key'][] = '中文姓名';
$wordData['customer_data']['key'][] = '英文姓名';
$wordData['customer_data']['key'][] = '护照';
$wordData['customer_data']['key'][] = '护照有效期';
$wordData['customer_data']['key'][] = '国籍';
$wordData['customer_data']['key'][] = '签发地点';
$wordData['customer_data']['key'][] = '手机号码';
$wordData['customer_data']['key'][] = '微信号码';
$wordData['customer_data']['value'] = $custList;

//酒店数据
$wordData['hotel_data']['key'][] = '酒店或度假村名称';
$wordData['hotel_data']['key'][] = '房型(中英文房型)';
$wordData['hotel_data']['key'][] = '数量';
$wordData['hotel_data']['key'][] = '餐型';
$wordData['hotel_data']['key'][] = '入住日期';
$wordData['hotel_data']['key'][] = '退房日期';
$wordData['hotel_data']['key'][] = '晚数';
$wordData['hotel_data']['value'][] = $routeDescArr;

//交通信息
$wordData['traffic_data']['key'][] = '出发地';
$wordData['traffic_data']['key'][] = '目的地';
$wordData['traffic_data']['key'][] = '去程时间';
//$wordData['traffic_data']['key'][] = '回程时间';
$wordData['traffic_data']['key'][] = '类型';
$wordData['traffic_data']['key'][] = '数量（人）';
$wordData['traffic_data']['value'] = $vehicleArr;

//行程信息
$wordData['route_data']['value'][0][] = '第一天';
$wordData['route_data']['value'][0][] = '9月21日';
$wordData['route_data']['value'][0][] = '北京-普吉岛';
$wordData['route_data']['value'][0][] = '1.	北京-普吉 HU7929 18:05-22:50
2.	接机前往普吉机场酒店
3.	入住标准间
';
$wordData['route_data']['value'][0][] = '含早餐';
$wordData['route_data']['value'][1][] = '第一天';
$wordData['route_data']['value'][1][] = '9月21日';
$wordData['route_data']['value'][1][] = '北京-普吉岛';
$wordData['route_data']['value'][1][] = '1.	北京-普吉 HU7929 18:05-22:50
2.	接机前往普吉机场酒店
3.	入住标准间
';
$wordData['route_data']['value'][1][] = '含早餐';
//总费用信息
$wordData['total_price_data']['key'][] = '编号';
$wordData['total_price_data']['key'][] = '内容';
$wordData['total_price_data']['key'][] = '单价（RMB）';
$wordData['total_price_data']['key'][] = '数量';
$wordData['total_price_data']['key'][] = '总价（RMB）';
$wordData['total_price_data']['value'][0][] = '1';
$wordData['total_price_data']['value'][0][] = '泰国要爱岛5天4晚行程 普通成人
2017年9月21日至2017年9月25日
';
$wordData['total_price_data']['value'][0][] = '3705';
$wordData['total_price_data']['value'][0][] = '2';
$wordData['total_price_data']['value'][0][] = '7410';
$wordData['total_price_data']['total_price'] = 6910;
$wordData['total_price_data']['total_price_format'] = '陆仟玖佰壹拾圆';
//费用信息
$wordData['cost_include']['value'] = '1、泰国普吉机场酒店1晚标准间住宿（含早餐）
2、泰国要爱岛3晚豪华海景房（含早餐）
3、交通接送：
*普吉机场-普吉机场酒店接机
*普吉机场酒店-要爱岛度假村车船接送
*要爱岛度假村-要爱岛码头船接送
4、普吉岛环岛包车（10小时）从要爱岛码头接船，以送机至普吉机场结束
5、在线24小时管家服务
6、遇•岛私人路书
7、美亚“万国游踪”全球旅行保障计划（无忧计划）
8、行程定制费用
9、泰国旅游签证
';
$wordData['cost_not_include']['value'] = '1、一切个人消费及费用包含中未提及的任何费用 因航空管制、交通延阻、罢工、天气、飞机机器故障、航班取消或更改时间等原因以及其它不可抗力原因导致的费用，以及个人旅游意外险等 
2、航班费用
';

//文档设置
$cellStyle['borderTopColor'] = '#000';
$cellStyle['borderLeftColo'] = '#000';
$cellStyle['borderRightColor'] = '#000';
$cellStyle['borderBottomColor'] = '#000';
$styleTable = array('borderColor'=>'#000',
    'borderSize'=>6,
    'cellMargin'=>50);
$styleFirstRow = array();
$PHPWord->addTableStyle('myTable', $styleTable, $styleFirstRow);

$str = "协议编号：$wordData[word_code]
日期：$wordData[date_time]
";

//开始设置
$str1 = "本服务协议共10页（含本页）
甲方：$wordData[jia_fang]
身份证号码：$wordData[id_card]
邮箱：$wordData[email]
联系电话：$wordData[call_phone]
地址：$wordData[address]

乙方:遇岛（北京）国际旅行社有限公司
经营范围： （一）入境旅游业务（二）境内旅游业务（三）出境旅游业务
邮箱： info@cheeruislands.com                         联系电话： 010-82515311
公司地址：  北京市海淀区中关村街乙12号院1号楼15层1801                 
旅游服务质量监督意见反馈www.cheeruislands.com （意见反馈栏）或投诉电话：010-82515311
    根据《中华人民共和国合同法》《中华人民共和国旅游法》有关法律法规的规定，甲乙双方在平等、自愿、协商一致的基础上，就下列委托事项达成如下协议：

第一条 旅客信息
1、甲方需要仔细核对旅客的姓名与证件号，乙方将按照以下信息为甲方预定，若因信息有误造成甲方损失，所产生损失由甲方自行承担。甲方最迟不得迟于2017年8月18日（含当天）提供所有客人的资料（包括但不限于护照号码、姓名、证件有效期、出生日期、性别、国籍），以方便乙方落实预定，并且保证提供的所有信息的真实性，乙方不承担因甲方提供的信息错误而造成的任何后果或损失。
";

$str = explode("\r",$str);

foreach($str as $k=>$v){
    $section->addText($v,array('bold'=>true,'size'=>9));
}


$str1 = explode("\r",$str1);

foreach($str1 as $k=>$v){
    $section->addText($v,array(),array('spacing'=>115));
}



$section->addText("");
$table = $section->addTable('myTable');
$table->addRow();
foreach($wordData['customer_data']['key'] as $k=>$v){
    $cell = $table->addCell(2000,$cellStyle);
    $cell->addText($v);
}

foreach($wordData['customer_data']['value'] as $k=>$v){
    $table = $section->addTable('myTable');
    $table->addRow();
    for($i=0;$i<8;$i++){
        $cell = $table->addCell(2000,$cellStyle);
        $cell->addText($v[$i]);
    }
}
$section->addText("");

$str2 = "第二条 服务范围
1、甲方购买乙方在本合同中的服务内容和产品，乙方办理以下服务，乙方按甲方选择的组合内容预订和提供服务，并接受甲方以总价方式支付,甲方支付的费用包含了购买本合同中的服务和产品的总价格；
2、乙方可以为甲方提供预订机票、酒店、以及提供目的地地面资源（例如用车、门票、N日游等）；抵离接送机等服务。具体的服务项目根据本合同第三条的约定确定。

第三条 服务项目及总服务费用
1、分项预定
（1）预定酒店或度假村        
";

$str2 = explode("\r",$str2);
foreach($str2 as $k=>$v){
    $section->addText($v,array(),array('spacing'=>115));
}

$section->addText("");
$table = $section->addTable('myTable');
$table->addRow();
foreach($wordData['hotel_data']['key'] as $k=>$v){
    $cell = $table->addCell(3000,$cellStyle);
    $cell->addText($v);
}

foreach($wordData['hotel_data']['value'] as $k=>$v){
    $table = $section->addTable('myTable');
    $table->addRow();
    for($i=0;$i<7;$i++){
        $cell = $table->addCell(3000,$cellStyle);
        $cell->addText($v[$i]);
    }
}
$section->addText("");

$str3 = "说明：
（a）乙方是否能成功预订该酒店房型以酒店回复确认为准,若订不到甲方指定酒店或房型，甲方可选择更换房型、更换酒店或更改日期等,或乙方将订金退还甲方（退款金额以乙方到账金额为准，不包括刷卡手续费或者支付时已经产生或将要产生的其他手续费和服务费），乙方不承担责任。
（b）关于办理入住时间和退房时间均以酒店安排为准，入住时请自行与酒店前台核实。
（c）酒店说明： 餐食标准为Day 2-5 含早餐。住宿部分按2人1房标准，如协议已签订后甲方有新增人员，则另外需要另行咨询乙方；    

（2）预订交通（不含航班）：
";

$str3 = explode("\r",$str3);

foreach($str3 as $k=>$v){
    $section->addText($v);
}

$section->addText("");
$table = $section->addTable('myTable');
$table->addRow();
foreach($wordData['traffic_data']['key'] as $k=>$v){
    $cell = $table->addCell(3000,$cellStyle);
    $cell->addText($v);
}

foreach($wordData['traffic_data']['value'] as $k=>$v){
    $table = $section->addTable('myTable');
    $table->addRow();
    for($i=0;$i<6;$i++){
        $cell = $table->addCell(3000,$cellStyle);
        $cell->addText($v[$i]);
    }
}
$section->addText('备注：');
$section->addText('除航班外，所用交通工具（车辆、船只等）将根据最终人数而落实，根据乙方的交通预定流程，乙方将于2017年8月18日确定交通费用。对于本次行程，乙方不负责如因甲方问题造成任何的包车使用上的临时修改，所产生的额外费用，乙方不负责客人这部分因为包车退改而造成的经济损失；');
$section->addText("");
$section->addText("（3）预订保险");
$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(5000,$cellStyle);
$cell->addText("保险名称: 美亚“万国游踪”全球旅行保障计划");
$cell = $table->addCell(5000,$cellStyle);
$cell->addText("境外最高保额50 万元/人 保费 155 元/人");

$section->addText("");
$section->addText("（4）预订签证");
$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(5000,$cellStyle);
$cell->addText("签证类型: 泰国旅游签证");
$cell = $table->addCell(5000,$cellStyle);
$cell->addText("费用：50 元/人");

$section->addText("");

$str4 = "（5）备注：
甲方出行期间，乙方可提供24小时在线咨询服务，协助客户处理具体问题。 
免费使用项目：自行车、独木舟
自费项目： 跳岛游、出海海钓【除在本协议中明确约定属于已经付费或自费项目之外的其他项目，全部属于自费项目】    
 
（6）行程概览
";

$str4 = explode("\r",$str4);

foreach($str4 as $k=>$v){
    $section->addText($v,array(),array('spacing'=>115));
}


$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(3000,$cellStyle);
$cell->addText("时间");
$cell = $table->addCell(3000,$cellStyle);
$cell->addText("");
$cell = $table->addCell(3000,$cellStyle);
$cell->addText("");
$cell = $table->addCell(3000,$cellStyle);
$cell->addText("");
$cell = $table->addCell(3000,$cellStyle);
$cell->addText("备注");


foreach($wordData['route_data']['value'] as $k=>$v){
    $table = $section->addTable('myTable');
    $table->addRow();
    for($i=0;$i<5;$i++){
        $cell = $table->addCell(3000,$cellStyle);
        $cell->addText($v[$i]);
    }
}

$section->addText("备注：行程概览中要爱岛活动仅供参考，可以根据自己实际情况安排。",array('bold'=>true));
$section->addText('');

$section->addText('2、服务总包费用');
$section->addText('⑴服务项目总费用：');
$section->addText('');

$table = $section->addTable('myTable');
$table->addRow();
foreach($wordData['total_price_data']['key'] as $k=>$v){
    $cell = $table->addCell(3000,$cellStyle);
    $cell->addText($v);
}

foreach($wordData['total_price_data']['value'] as $k=>$v){
    $table = $section->addTable('myTable');
    $table->addRow();
    for($i=0;$i<5;$i++){
        $cell = $table->addCell(3000,$cellStyle);
        $cell->addText($v[$i]);
    }
}

$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(7000,$cellStyle);
$cell->addText("含税总计");
$cell = $table->addCell(3000,$cellStyle);
$cell->addText($wordData['total_price_data']['total_price']);

$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(7000,$cellStyle);
$cell->addText("大写");
$cell = $table->addCell(3000,$cellStyle);
$cell->addText($wordData['total_price_data']['total_price_format']);

$section->addText('');
$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(5000,$cellStyle);
$cell->addText("费用仅包含");
$cell = $table->addCell(5000,$cellStyle);
$cell->addText('费用不包含');

$table = $section->addTable('myTable');
$table->addRow();
$cell = $table->addCell(5000,$cellStyle);
$cell->addText($wordData['cost_include']['value']);
$cell = $table->addCell(5000,$cellStyle);
$cell->addText($wordData['cost_not_include']['value']);

$str5 = "（2）以上费用以人民币作为结算币种，在签订本协议之当日一次性付清；
（3）发票：甲方在回程十个工作日内通知乙方，按服务协议内容开具发票，发票抬头需与服务协议甲方一致。
（4）其他另行约定：因酒店已过取消期，不适用本协议第六条第1小条
（5）本次服务按照预定的要求，价格有效期为即日起至2017年8月18日。当在2017年8月18日前甲方人员有所减少，造成的每位客人需缴纳的额外费用，乙方有义务向所有客人或合作方负责人通知，但乙方不负责承担这部分的费用。

第四条 双方确认
1、甲方知悉并确认预定信息无误，乙方接受甲方预定的以上事项。
2、甲方无法或未能及时与乙方用书面合同确认事项的，甲方同意双方采用电话、短信、QQ、微信、传真或者电子邮件等方式与乙方确认，并且，甲乙双方均确认，上述电话、短信、QQ、微信、传真或者电子邮件等方式确认的内容对双方均有法律约束力。【例如：乙方能过QQ或微信等发了电子合同给甲方，甲方看过之后，在信息中表示合同内容同意或OK的，则视为甲方对合同条款没有异议，甲方的表示行为视为承诺，并且，双方受合同条款的约束】
3、若甲方未能在最后付款期限前确认并付款，则乙方有权单方即时解除合同并追究相应的法律责任，且乙方不再做任何事先书面通知。
4、甲乙双方同意签约可以采用复印件、传真、电子邮件或其他非原件形式。该非原件协议经送达确认后视为有效并具有法律效力。
5、海岛接送船只受天气影响，雨季出行可能会由于天气原因无法登岛。

第五条 甲乙双方权利和义务
1、甲方权利和义务
（1）甲方应自觉遵守旅游文明行为规范，尊重目的地的风俗习惯、文化传统和宗教禁忌，爱护旅游资源，保护生态环境；
（2）甲方应遵守我国和目的地国家（地区）的法律法规和有关规定，不携带违禁物品出入境，按时归国并按要求参加面试和面试销签、提交销签材料，如甲方违反该条规定，甲方自愿承担乙方因此产生的全部损失，同时，如甲方提交保证金的，乙方有权直接扣除全部保证金，如甲方提交担保函的，乙方有权按照担保函中的保证金额向甲方和甲方担保人索赔。
（3）甲方应特别注意目的地的法律法规及风俗禁忌，慎重选择骑马、攀岩、滑翔、漂流、潜水、游泳、跳伞、热气球、蹦极等高风险或带有危险性的产品；
（4）甲方应在自己能够控制风险的范围内选择活动项目，并对自己的安全负责；
（5）甲方应当确保所提供的证件、资料及联系电话真实有效，因甲方提供材料存在问题或者自身其他原因被拒签、缓签、拒绝入境和出境的，相关责任和费用由甲方承担，如给乙方造成损失的，甲方还应当承担赔偿责任；
（6）甲方向乙方提交的因私护照或者通行证有效期为自出发日期起半年以上，同时护照须具有三页空白页以上，自办签证/签注者应当确保所持签证/签注在出游期间有效；
（7）甲方应按约定向乙方全额支付所有服务项目的服务总费用；
（8）甲方应自行保管好随身携带的财物，如遇丢失由甲方自行承担损失；
（9）发生意外事件或纠纷时，甲方应本着平等协商的原则解决，采取适当措施防止损失扩大，否则应当就扩大的损失承担责任；
（10）在合法权益受到损害要求协助索赔时，甲方应提供合法有效的凭据；
（11）节假日产品，鉴于资源的特殊状况，甲方未经乙方同意不得取消或更改；
（12）甲方因自身原因致使人身、财产权益受到损害的，乙方不承担赔偿责任，若因此造成乙方损失的，还应对乙方承担赔偿责任。
（13） 甲方在委托事项执行过程中如有不满意之时，不得以滞留境外、拒绝登机（车、船）、拒绝入住等方式对委托事项故意拖延、扩大损失、擅自取消行程等处理方法，由此造成的损失由甲方承担。
（14）甲方在执行委托事项时，必须符合我国和目的地国家及地区的有关规定：委托事项实施方（如酒店、机场、票务、景区、海关等）有另外特别规定的，甲方应予以遵守。
（15）甲方若持有非大陆因私护照，请自行向发证机关和目的地使领馆确认是否需要签证，如需签证，请自行办理。如需回程签的，也请自行办理。
（16）甲方在履行合同中， 如果对合同的履行有任何争议的，则应当由双方友好协商，不得采取恶意散布不实信息的行为，否则，另一方有权采取法律行动，要求发布不实信息的一方，以发放的不实信息的被浏览的数量X人民币10元/阅次=赔偿额度。
2、乙方权利和义务
（1）乙方有权根据甲方的身体健康状况及相关条件决定是否接纳报名。
（2）乙方有权核实甲方提供的相关信息资料；
（3）乙方有权按照协议约定向旅游者收取全额旅游费用；
（4）乙方有权拒绝甲方提出的超出协议约定的不合理要求；
（5）乙方依法对旅游者个人信息保密；但同时，乙方有权在突发事件或紧急情况下，将甲方有关的事宜向事发地的警方、海关、政府机关或使领馆等机构报告情况；
（6）乙方有权根据合同约定收取总包的服务费用并出具发票的义务；
（7）乙方应告知甲方抵达目的地的具体接洽事宜；
（8）乙方应按照合同约定的内容和标准为甲方提供服务，不擅自变更行程安排，不降低服务标准；
（9）乙方按照合同约定向甲方提供24小时管家服务；
（10）乙方在合同约定的服务范围内，尽可能为甲方提供便利和协助；
（11）乙方积极协调处理甲方在旅游行程中与第三方之间的纠纷，采取适当措施防止损失扩大；甲方的人身、财产权益受到损害时，乙方应采取合理必要的保护和救助措施，避免旅游者人身、财产权益损失扩大；
（12）乙方有权要求甲方对其在旅游活动中或在双方解决纠纷期间对乙方（出境社）的合法权益造成损害的行为承担赔偿责任，否则乙方有权要求甲方承担法律赔偿责任。   

第六条 违约责任
1、甲方违约责任
（1）由于甲方原因解除协议的，客户取消订单、出境单项服务应当提前 45日（不含45日）书面通知乙方，乙方根据具体条款，从甲方的已付款中扣除相关费用（飞机、车、船退（改）票费、通信联络、房费及其它已支付或已经产生的费用和成本）后，退回余款，所退款项则是在甲方签订退款确认单之后15个工作日内支付至甲方的指定的银行账户；如果甲方没有按约定通知乙方而解除协议的，视为违约，乙方有权单方解除合同。
（2）出发前30日（含30日）以内因甲方原因无法出行，不可退款，乙方根据具体条款，从甲方的已付款中扣除相关费用（飞机、车、船退（改）票费、通信联络、房费及其它已支付或已经产生的费用和成本）以及服务费（行程费用的10%）后，退回余款，所退款项则是在甲方签订退款确认单之后15个工作日内支付至甲方的指定的银行账户；如果甲方没有按约定通知乙方而解除协议的，视为违约，乙方有权单方解除合同。
（3）出发前15日（不含15日）以内由于甲方原因取消行程，均不可退改、不可转让；如果甲方违约坚持取消行程，则甲方所缴的费用全额无法返还。
（4）甲方未按协议约定或者未经乙方同意，临时要求改变机票、酒店或租车等行程的，费用自理，并不得要求退回已支付的费用。
2、乙方违约责任
由于乙方无理取消订单或拒绝履行订单提供服务的，乙方应当向甲方全额返还订单的总费用。
3、特别约定
（1）如甲方委托乙方代订的是包机航班机票或特价机票，该机票一经确认，则不能更改、不能转签、不能退票。如果代订的是往返或多个目的地的机票，如果已使用，对未使用航段的退票，以航司确认为准。
（2）乙方仅对委托的内容负责，不安排旅游行程、不派遣领队，对委托内容以外的事宜不承担责任，非委托的事宜由甲方自行安排。本协议不适用法律法规关于组团的相关规定。 甲方在出游期间的任何活动纯属个人行为，与乙方无关。
（3）甲方代理其他游客签约的，甲方有义务将本协议约定事项向其代理游客做出必要说明，并保证其在协议中的签字能够代表其代理旅游者对协议约定的认可。如甲方未履行上述义务，导致其代理的游客与乙方发生纠纷，责任在于签约人甲方，与乙方无关。



第七条 保险购买
  乙方推荐甲方购买旅游意外险、财产遗失等保险，甲方可自行购买或者委托乙方购买；委托乙方购买的，在甲方出行前，乙方会将电子保单或纸质保单发送给甲方。意外保险赔偿细则以保险公司出具的保单内容为准，承保的保险公司具有最终解释权。

第八条 甲方健康告知
    甲方在签订本协议前，以及在出行过程中应确保自身条件适合出行，甲方应当充分了解和斟酌自己以及相关甲方的健康状况，确保自身身体条件能够适应和完成出行活动。对于已经付费、签订本协议的甲方，受托方视同甲方身体状况适合本次活动，对因甲方个人身体原因导致的损失不承担责任。

第九条 协议变更
1、协议生效后，甲方要求更改的，应选择同一出发地和目的地并仍在可售卖日期内的产品，或经乙方同意，甲方可以将其在协议中的权利和义务转让给符合出游条件的第三人。但甲方应向乙方支付由此增加的服务费用以及给乙方造成的损失也由甲方承担。由此减少的服务费用，乙方应当退还甲方。
2、协议生效后，因乙方原因引起的更改，由此增加的服务费用由乙方承担，由此减少的服务费用，乙方应当退还甲方。
3、协议生效后，如有任何变动调整，以本协议第十五条3的方式予以确认。

第十条 不可抗力和意外事件
【不可抗力】指不能预见、不能避免并不能克服的客观情况，包括但不限于因自然原因和社会原因引起的，如天气原因（包括但不限于如起风、海浪、暴雨、大雪等危及游客出行安全的自然天气原因）、自然灾害、战争、恐怖活动、动乱、骚乱、罢工、突发公共卫生事件、政府或政府部门行为、黑客攻击、电信部门技术管制。
【意外事件】指因当事人故意或者过失以外的偶然因素引发的事件，包括但不限于重大礼宾活动导致的交通堵塞、列车航班晚点、景点临时不开放。
因【不可抗力】或者【意外事件】导致无法履行或者继续履行协议的，乙方在扣除已经发生的费用以及服务费用之后，向甲方退还未实际发生的费用后不再承担其他任何责任；双方同意变更协议的，因此增加的费用由甲方承担，减少的费用乙方退还甲方。

第十一条 第三方责任
由于出入境管理局、各国领馆、航空公司、保险公司、及其他有权机构等不可归责于乙方的原因导致甲方人身、财产权益受到损害的，包括但不限于，航班延误或取消、护照延期、签证拒签或未按时出签、不得出入境等，应由甲方自行协商解决，乙方除在力所能及的范围内予以协助外，不再承担其他责任，如给乙方造成损失的，乙方保留一切追偿权。

第十二条 争议解决
本协议履行过程中发生争议，由双方协商解决，协商不成的，任何一方有权依法向北京市海淀区人民法院起诉。

第十三条 特别提示
1、请在签订合同后，甲方应当尽快与乙方确认出行旅客信息，签订合同后，甲方应当尽快与乙方确认出行旅客信息，同时，甲方应当向乙方提供出行旅客的证件【身份证复印件以及护照复印件】，如果由于甲方拒绝提供或未能及时提供导致所产生的损失由甲方承担；由于甲方签订合同确认了出行人员的数量，乙方为其预定机票或酒店等以保证配套服务，已经产生费用，而甲方又提供不了旅客的证件，所产生的费用由甲方承担，如果甲方此时尚未支付费用，则乙方有追索权。
2、酒店入住：通常酒店对入住客人会有年龄的要求，例如，同订单中至少要有一位入住客人已满十八周岁。按照行业惯例，一般酒店的入住时间是当天14点整至第二天12点整，实际入住和离店时间以入住的酒店为准，如客人提前入住或推迟离店，均须加收一定的费用【具体收费则参照入住的酒店的标准】。
3、目的地酒店资源稀源，容易超售，特别是黄金周等旅游旺季，如乙方与酒店进行确认后因酒店超卖等原因导致甲方在入住时发现酒店房型不是预先确定的，须立即联系通知乙方，甲方不得选择拒绝入住，乙方有义务代甲方向酒店争取补偿减少损失，或根据酒店安排协调至同级别酒店，但不承担非乙方的过错给甲方造成的任何损失及补偿。
4、为保证产品的成功预订，甲方应在产品确认后24小时之内或双方约定的时间付款。如甲方未按要求及时付清相关费用，而此时乙方预留产品的价格、内容或标准等发生变化，乙方对此将不承担任何责任。
5、折扣机票，如甲方选择了折扣机票，因网上数据更新有一定的延时，所选定的航班、舱位和价格以旅行社最终回复为准。
6、代订机票上所示的时间如与航空公司最终通知搭乘飞机时间不一致的，以航空公司最终通知时间为准。
7、联运航班：申请联运航班均以航司批复为准，包括航班时间、航班号、机票价格等，协议约定报价为参考价格，以实际出票为准，按照多退少补原则执行；
8、时差：行程协议上提及的到达时间和起飞时间均为所在国或地区的当地时间，甲方应合理注意旅游目的国和国内的时差。
9、甲方年龄，甲方系18周岁以下（不包括18周岁）或 70岁以上（包括70岁）参加旅游,应有直系亲属或监护人书面同意，非单人出行。
10、温馨提示：甲方需获得准确的航班或酒店等相关信息后，方可安排其他的行程，以免造成不必要的损失；
11、安全提示：
⑴甲方参加高原地区旅游或风险旅游项目（包括但不限于：游泳、浮潜、冲浪、漂流等水上活动以及骑马、攀岩、登山等高风险的活动）或患有不宜出行旅游的病情（包括但不限于：恶性肿瘤、心血管病、高血压、呼吸系统疾病、癫痫、怀孕、精神疾病、身体残疾、糖尿病、传染性疾病、慢性疾病健康受损），须在报名前自行前往医疗机构体检后，确保自身身体条件能够完成本次旅游活动，并向旅行社提供体检报告副本；甲方须保证提供的身体健康状况真实，如隐瞒由本人承担全部责任；甲方系 70岁以上（含70岁）参加旅游，应有亲属同意，且非单人出行，同时在出行前如实填写并提交《身体健康申报表》；旅行社已经给予甲方出游安全提示（旅行社已经提示并劝阻，但如甲方仍坚持参加旅游活动，由此造成任何人身意外及不良后果将由甲方本人全部承担）。
⑵建议甲方出行后不在海上或礁石上、交通公路上、交通工具上、禁止拍照的地方或高山危险地方拍照，一切行为要充分参考当地、当时相关公告及建议，出现意外伤害、财产损失，或购买到假冒商品，乙方均不担责。
12、乙方热心社会公益，其会将营业额的百分之一捐给广州卫蓝自然环境保护协会，用于环保公益事业。乙方也致力于向每位客户倡导环保公益理念，本协议后附的广州市卫蓝自然环境保护协会《“卫蓝”文明出行倡议》，建议甲方仔细阅读后签署并在出行时尽力实施，做“中国好游客”。

第十四条 高危项目活动安全须知
浮潜
1、醉酒者、患有耳、鼻疾病、癫痫症、精神病、结核病、糖尿病、肾脏病、心脏病、气喘、高（低）血压等疾病的游客不能从事潜水活动；低于10岁的儿童不能从事潜水活动。以上疾病类型只是简要示例，如游客尚有其他疾病可能不适合参加旅游活动的，请主动向旅行社告知或咨询。
2、游客境外出游的，在自由活动期间，切勿参加非法或未经中国政府核实的当地旅游团体提供的自费项目、行程，以免发生人身伤亡、财产损失、食物中毒等意外事件。
3、注意气候状况，阴天、雨天或风较大的天气都不适合浮潜。
4、浮潜三宝（面镜、呼吸管及蛙鞋）必不可少，为了海洋环境，请尽量使用物理防晒，穿防晒衣。
5、浮潜时需注意安全，在安全标示的指定区域潜水，遵循“二人同行原则”，并且有教练员或者工作人员的陪同。
6、在整个活动中，务必要听从导游或者工作人员的指示。
7、当不自觉进入流区，无论顺流还是逆流，请尽快离开，以免因逆流消耗体力或因顺流被带离岸边。为节省体力，以顺流斜角游离为宜。
8、掌握简易的镜面排水方法，当浮潜中面镜进水时，双手指头用力按住面镜上部镜缘，由鼻子喷气，水便会由镜面下部排出。请先于浅滩处练习。
9、掌握简易的呼吸管排水方法，当呼吸管进水时，请用力且快速吹气将水排出。另外干式或半干式的呼吸管有排水阀及逆止阀之设计，可有效降低海水进入呼吸管的量，建议最好选择此类呼吸管。
10、浮潜时间不宜过长，以免体力透支。尽量穿戴防水手表，以掌握时间。
11、万一发生体力不支、漂流或溺水之状况，请务必告诉自己必须冷静，唯有冷静才得以自救并求援。海水浮力大，双腿若能以垂直踩脚踏车动作持续移动，可延长救助时间。
12、当同行伙伴发生紧急状况，请即刻就近求援，并评估自身是否具备救援能力，前往救援时，需要携带浮具。
13、浮潜属于高风险旅游项目，请旅游者根据自身情况谨慎选择参加。旅行社在此特别提醒，建议旅游者投保高风险意外险种，酒后禁止参加。浮潜前，仔细阅读景区提示，在景区指定区域内开展活动。

快艇
1、严禁携带易燃、易爆、腐蚀性等危及人身安全的物品上快艇。
2、严重的心脏病、精神病、高血压、高度近视、颈椎病、腰椎病、骨折等疾病患者不能参加快艇活动。以上疾病类型只是简要示例，如游客尚有其他疾病可能不适合旅游活动时，请主动向旅行社告知或咨询。
3、每位游客乘坐快艇时必须穿救生衣，找到安全绳。
4、上艇时不要站在缆绳附近，避免绊倒受伤。
5、船头颠簸剧烈，老人、儿童不能坐船头，以免发生意外身体伤害。
6、带小孩的游客，看管和照顾好自己的孩子安全。
7、在整个活动中，务必要听从导游或者工作人员的指示，注意安全。
8、乘搭快艇时，不要集中在快艇的一侧，以免快艇失去平衡。严禁在快艇内走动，头、手不要放在快艇的边缘外，以免被碰撞及发生其他意外。
9、果壳等废物请放入垃圾箱内，不要抛入水中。
10、参与快艇活动者在途中未经许可不得离艇下水。
11、必须穿戴救生衣，如发生翻艇落水，不要惊慌，救生衣能保证您的安全，请积极配合驾驶员的救护措施。
12、在整个活动中，同船人员要团结、友爱、互助，在紧张刺激、快乐安全中度过全程。
13、快艇属于高风险旅游项目，请旅游者根据自身情况谨慎选择参加。旅行社在此特别提醒，建议旅游者投保高风险意外险种，酒后禁止参加。乘坐快艇前，仔细阅读景区提示，在景区指定区域内开展活动。
潜水
1、醉酒者、患有耳、鼻疾病、癫痫症、精神病、结核病、糖尿病、肾脏病、心脏病、气喘、高（低）血压等疾病的游客不能从事潜水活动；低于10岁的儿童不能从事潜水活动。以上疾病类型只是简要示例，如游客尚有其他疾病可能不适合参加旅游活动的，请主动向旅行社告知或咨询。
2、游客境外出游的，在自由活动期间，切勿参加非法或未经中国政府核实的当地旅游团体提供的自费项目、行程，以免发生人身伤亡、财产损失、食物中毒等意外事件。
3、在整个活动中，务必要听从导游或者工作人员的指示，注意安全。
4、遵守二人同行的原则，不可单独潜水，要紧跟教练或潜导，在指定区域潜水。
5、不要使用耳塞，在耳内感到疼痛前，须学会做耳压平衡。潜水时因为水的压力，在下潜到一定深度的时候会觉得耳朵疼痛，做了反压，即无痛感。
6、为确保您的安全，请在规定范围内潜水。潜水员必须持有有效潜水证，超过半年未潜水的潜水员，须参加复习课程方可再次潜水。非潜水员参加体验潜时必须严格遵循体验潜潜水长或教练的要求。
7、眼睛近视的游客可选择相同近视度的潜水镜
8、在完全离水上岸后再去掉面镜、呼吸管。
9、不可尝试超越休闲潜水准则的深度限制。
10、潜水时须掌握的几种手势语言：ok、注意（物体）方向、下潜、上升、空气要没了（在残压计为50bar时就应该使用）、给我空气等。
11、潜水属于高风险旅游项目，请旅游者根据自身情况谨慎选择参加，对此旅行社不负责。旅行社在此特别提醒，建议旅游者投保高风险意外险种，酒后禁止参加。潜水前，仔细阅读景区提示，在景区指定区域内开展活动。

第十五条 协议的生效及组成
1、本协议自甲方收到电子合同表示同意即生效，传真件或邮件或微信中的扫描件或拍照件均与原件具有同等法律效力。
2、本协议有效期限，自签订之日起至本事务完成时止。
3、协议签订之后，甲方与乙方进行书面往来确认补充或变更事项时，双方同意采用
(1)电话（短信）：15075199772
(2)电子邮件： 664120717@qq.com
等方式进行确认后续事项。

第十六条 其他补充事项未尽事宜，经甲方和乙方双方协商一致，可以列入补充条款。
";

$str5 = explode("\r",$str5);

foreach($str5 as $k=>$v){
    $section->addText($v,array(),array('spacing'=>115));
}

$section->addText('');
$section->addText('');
$section->addText('');
$section->addText('');
$section->addText('以下无协议正文',array(),array('align'=>'center','spacing'=>115));
$section->addText('');

$section->addImage(APP_PATH.'components/PHPWord_Sam/'.'3.jpg',array('_width'=>100,'_height'=>110,'align'=>'center','marginTop'=>10));
$section->addText('');

$section->addText('“卫蓝”文明出行倡议',array('size'=>'18','bold'=>true),array('align'=>'center','spacing'=>115));

$str6 = "轻声说，慢慢行；拘小节，遵法纪；
文明行，乐所宿；既入乡，则随俗；
雅而为，禁而止；爱文物，生态游；
避恶语，晓以理；善相待，光中华。


";

$str6 = explode("\r",$str6);

foreach($str6 as $k=>$v){
    $section->addText($v,array('size'=>'18','bold'=>true),array('align'=>'center','spacing'=>115));
}


$str7 = "1.	轻声说，慢慢行：不大声喧哗，不随意插队，上下电梯地铁不推挤人，过马路不抢灯，奢侈品扫货不气势汹汹。
2.	拘小节，遵法纪：不随处扔垃圾、不随地吐痰、不在路边长椅上不在火车不在飞机上脱鞋脱袜子、不随处蹲下。
3.	文明行，乐所宿：下榻酒店，请勿食用刺激气味食品，请勿毁坏酒店设施，请勿侮辱服务人员。
4.	既入乡，则随俗：遵守当地法律法规，尊重当地传统习俗。
5.	雅而为，禁而止：请勿在自助餐上自行打包占便宜，请勿顺手带精美餐具回家。
6.	爱文物，生态游： 请勿乱涂鸦，请勿乱践踏，请勿乱侵入。请勿乱扔垃圾，请勿伤害动植物。
7.	避恶语，晓以理：遇事冷静，切勿恶语相对，理性睿智，解决问题，避免纷争。
8.	善相待，光中华:  尊重他人，以礼待人。发扬中华礼仪之邦本色。
";

$str7 = explode("\r",$str7);

foreach($str7 as $k=>$v){
    $section->addText($v,array('bold'=>true),array('align'=>'left','spacing'=>115));
}

$section->addText('广州市卫蓝自然环境保护协会',array('size'=>'18','bold'=>true),array('align'=>'center','spacing'=>115));

$section->addImage(APP_PATH.'components/PHPWord_Sam/'.'4.png',array('_width'=>300,'_height'=>70,'align'=>'center','marginTop'=>10));

$section->addText('');
$section->addText('广州市卫蓝自然环境保护协会',array('bold'=>true),array('align'=>'center','spacing'=>115));


//设置页头
$header = $section->createHeader();
$header->addImage(APP_PATH.'components/PHPWord_Sam/'.'1.png',array('_width'=>110,'_height'=>60,'align'=>'left','marginTop'=>10));

//设置页脚
$footer = $section->createFooter();
$footer->addText('Room 1801, Block B, Tianzuoguoji Building,',array('align'=>'left'));
$footer->addText(' No.12 Zhongguancun South Main Street, Haidian District, Beijing, China',array('align'=>'left'));
$footer->addText('北京市海淀区中关村南大街乙12号天作国际B座1801',array('align'=>'left'));
$footer->addText('Tel: 010-82515311',array('align'=>'left'));
$footer->addText('E-mail: info@cheeruislands.com',array('align'=>'left'));
$footer->addText('网站： www.cheeruislands.com',array('align'=>'left','color'=>'#66CDAA'));
$footer->addImage(APP_PATH.'components/PHPWord_Sam/'.'2.jpg',array('_width'=>80,'_height'=>80,'align'=>'right'));


$fileName = "行程确认单";
header("Content-type: application/vnd.ms-word");
header("Content-Disposition:attachment;filename=".$fileName.".docx");
header('Cache-Control: max-age=0');
$objWriter = \PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$objWriter->save('php://output');


    }

    //数字大写
    public static function ParseNumber($number){
        $number=trim($number);
        if ($number>999999999999) return "数字太大，无法处理。抱歉！";
        if ($number==0) return "零";
        if(strpos($number,'.')){
            $number=round($number,2);
            $data=explode(".",$number);
            $data[0]=self::int($data[0]);
            $data[1]=self::dec($data[1]);
            return $data[0].$data[1];
        }else{
            return self::int($number);
        }
    }

    public static function int($number){
        $arr=array_reverse(str_split($number));
        $data='';
        $zero=false;
        $zero_num=0;
        foreach ($arr as $k=>$v){
            $_chinese='';
            $zero=($v==0)?true:false;
            $x=$k%4;
            if($x && $zero && $zero_num>1)continue;
            switch ($x){
                case 0:
                    if($zero){
                        $zero_num=0;
                    }else{
                        $_chinese=self::$basical[$v];
                        $zero_num=1;
                    }
                    if($k==8){
                        $_chinese.='亿';
                    }elseif($k==4){
                        $_chinese.='万';
                    }
                    break;
                default:
                    if($zero){
                        if($zero_num==1){
                            $_chinese=self::$basical[$v];
                            $zero_num++;
                        }
                    }else{
                        $_chinese=self::$basical[$v];
                        $_chinese.=self::$advanced[$x];
                    }
            }
            $data=$_chinese.$data;
        }
        return $data.'元';
    }

    public static function dec($number){
        if(strlen($number)<2) $number.='0';
        $arr=array_reverse(str_split($number));
        $data='';
        $zero_num=false;
        foreach ($arr as $k=>$v){
            $zero=($v==0)?true:false;
            $_chinese='';
            if($k==0){
                if(!$zero){
                    $_chinese=self::$basical[$v];
                    $_chinese.='分';
                    $zero_num=true;
                }
            }else{
                if($zero){
                    if($zero_num){
                        $_chinese=self::$basical[$v];
                    }
                }else{
                    $_chinese=self::$basical[$v];
                    $_chinese.='角';
                }
            }
            $data=$_chinese.$data;
        }
        return $data;
    }






}

?>
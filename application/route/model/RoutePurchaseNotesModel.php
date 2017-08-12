<?php
namespace app\route\model;
use think\Model;

class RoutePurchaseNotesModel extends Model
{
    public $table = 'ims_route_purchase_notes';
    public $rule = [
            'route_id|线路' => 'require|number',
            'agreement|退改协议' => 'require',
            'hint|重要提示' => 'require',
            'cost_includes|费用包含' => 'require',
            'free_item|赠送的项目' => 'require',
            'cost_except|费用不含' => 'require',

    ];






}



?>
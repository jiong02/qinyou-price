<?php
namespace app\index\model;

use app\index\controller\Date;

class Trf extends BaseModel
{
    protected $table = 'traffic';
    protected $connection = 'test_input';

    public function trfBase()
    {
        return $this->belongsTo('TrfBase','base_id');
    }

    protected function getStartTimeAttr($startTime)
    {
        return Date::tranTime($startTime);
    }
    protected function getStartTimeDayAttr($value, $data)
    {
        return Date::tranDay($data['start_time']);
    }

    protected function getEndTimeAttr($endTime)
    {
        return Date::tranTime($endTime);
    }

    protected function getEndTimeDayAttr($value, $data)
    {
        return Date::tranDay($data['end_time']);
    }

    protected function getInTimeAttr($inTime)
    {
        return Date::tranTime($inTime);
    }

    protected function getInTimeDayAttr($value, $data)
    {
        return Date::tranDay($data['in_time']);
    }

    protected function getOutTimeAttr($outTime)
    {
        return Date::tranTime($outTime);
    }

    protected function getOutTimeDayAttr($value, $data)
    {
        return Date::tranDay($data['out_time']);
    }

    public function getWasteAttr($waste)
    {
        return Date::tranTime($waste,'str');
    }

    public function getWasteInAttr($wasteIn)
    {
        return Date::formatToMH($wasteIn);
    }

    public function getWasteOutAttr($wasteOut)
    {
        return Date::formatToMH($wasteOut);
    }

    public function getWasteAllAttr($wasteAll)
    {
        return Date::tranTime($wasteAll,'str');
    }

    public static function  getSupplyBybaseId($baseId)
    {
        return self::where(['base_id'=>$baseId])->value('supply');
    }

    public static function getSupplyById($id)
    {
        return self::where(['id'=>$id])->value('supply');
    }

    public function getSingData($where)
    {
        $where['cat'] = '单程';
        return $this->where($where)->select();
    }

    public  function  getAllBaseId()
    {
        $where['cat'] = '联程';
        return $this->where($where)->distinct(true)->column('base_id');
    }

    public function getConnectData($where)
    {

        $baseId = $this->getAllBaseId();
        foreach ($baseId as $k => $v) {

            $data[] = TrfBase::get($v)->single;

        }
        return $data;
    }

    public function getRouteData($where)
    {
        return TrfRoute::where($where)->select();
    }
}

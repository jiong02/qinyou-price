<?php
namespace app\index\model;

class TrfRoute extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'traffic_route';

    protected function getContentNewAttr($contentNew)
    {
        $str = '';
        foreach (json_decode($contentNew) as $k => $v) {

            if ($v->name == '单程') {

                $trf = Trf::get($v->id);

                if ($trf->method == '航空') {

                    $str .='+'. $trf->method.$trf->num;

                }else{

                    $str .='+'. $trf->method;

                }

            }elseif($v->name == '联程'){

                $singleData = TrfBase::get($v->id)->single;

                foreach ($singleData as $k => $v ) {

                    if ($v->method == '航空') {

                        $str .='+'. $v->method.$v->num.'(联程)';

                    }else{

                        $str .='+'. $v->method.'(联程)';

                    }

                }

            }elseif($v->name == '过夜'){

                $str .= '+'. '过夜';
            }

        }

        return trim($str,'+');
    }

    protected function getFormatContentAttr($value,$data)
    {
        $str = '';
        foreach (json_decode($data['content_new']) as $k => $v) {

            if ($v->name == '单程') {

                $singleData = Trf::get($v->id);
                $ret[$k]['id'] = $singleData->id;
                $ret[$k]['base_id'] = $singleData->base_id;
                $ret[$k]['type'] = $singleData->regular;
                $ret[$k]['methods'] = $singleData->method;
                $ret[$k]['number'] = $singleData->num;
                $ret[$k]['startName'] = $singleData->start_city.''.$singleData->start_name;
                $ret[$k]['endName'] = $singleData->end_city.''.$singleData->end_name;
                $ret[$k]['startEname'] = $singleData->start_ename;
                $ret[$k]['endEname'] = $singleData->end_ename;
                $ret[$k]['inTime'] = $singleData->in_time."(".$singleData->in_time_day.")";
                $ret[$k]['startTime'] = $singleData->start_time;
                $ret[$k]['endTime'] = $singleData->end_time."(".$singleData->end_time_day.")";
                $ret[$k]['outTime'] = $singleData->out_time."(".$singleData->out_time_day.")";
                $ret[$k]['wasteIn'] = $singleData->waste_in;
                $ret[$k]['waste'] = $singleData->waste;
                $ret[$k]['wasteOut'] = $singleData->wasteOut;

            }elseif($v->name == '联程'){

                $connectData = TrfBase::get($v->id)->single;
                foreach ($connectData as $key => $value) {
                    $ret[$k]['type'] = '联程交通';
                    $ret[$k]['id'] = $v->id;
                    $ret[$k]['base_id'] = $v->base_id;
                    $ret[$k]['list'][$key]['type'] = $v->regular;
                    $ret[$k]['list'][$key]['methods'] = $v->method;
                    $ret[$k]['list'][$key]['number'] = $v->num;
                    $ret[$k]['list'][$key]['startName'] = $v->start_city.''.$v->start_name;
                    $ret[$k]['list'][$key]['endName'] = $v->end_city.''.$v->end_name;
                    $ret[$k]['list'][$key]['startEname'] = $v->start_ename;
                    $ret[$k]['list'][$key]['endEname'] = $v->end_ename;
                    $ret[$k]['list'][$key]['inTime'] = $v->in_time."(".$v->in_time_day.")";
                    $ret[$k]['list'][$key]['startTime'] = $v->start_time;
                    $ret[$k]['list'][$key]['endTime'] = $v->end_time."(".$v->end_time_day.")";
                    $ret[$k]['list'][$key]['outTime'] = $v->out_time."(".$v->out_time_day.")";
                    $ret[$k]['list'][$key]['wasteIn'] = $v->waste_in;
                    $ret[$k]['list'][$key]['waste'] = $v->waste;
                    $ret[$k]['list'][$key]['wasteOut'] = $v->wasteOut;
                }
            }
        }
    }

    protected function getRouteAttr($value, $data){

        return json_decode($data['content_new']);
    }
}
<?php

namespace app\ims\controller;

use app\ims\model\HotelModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\RoomModel;
use think\Db;
use think\Request;
use PHPExcel;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_IOFactory;
use PHPExcel_Style_Color;

class RoomController extends PrivilegeController
{
    public $excelObj;
    public $excelSheet;
    public $excelName;

    public function getAllYear($roomId)
    {
        $roomModel = new RoomModel();
        $where['expired_year'] = ['>=',date('Y')];
        $where['room_id'] = $roomId;
        $year = $roomModel->where($where)->group('expired_year')->column('expired_year');
        $result =array_map(function($v){
            return ['value'=>$v];
        },$year);
        if ($result){
            return getSucc($result);
        }
        return getSucc([['value'=>date('Y')]]);
    }

    /**
     * 房型房间数量查询接口
     * @param Request $request
     * @param $year
     * @param $month
     * @return \think\response\Json
     */
    public function query(Request $request)
    {
        $roomId = $request->param('room_id');
        $firstDate = $request->param('first_date');
        $lastDate = $request->param('last_date');
        $firstDate = explode('-',$firstDate);
        $lastDate = explode('-',$lastDate);

        $roomModel = new RoomModel();
        $roomData = $roomModel
            ->field('expired_month as month,expired_day as day,room_amount')
            ->where('expired_year','>=',$firstDate[0])
            ->where('expired_month','>=',$firstDate[1])
            ->where('expired_year','<=',$lastDate[0])
            ->where('expired_month','<=',$lastDate[1])
            ->where('expired_year','>=',date('Y'))
            ->where('room_id',$roomId)
            ->select();
        if ($roomData){
            return getSucc($roomData);
        }
        return getErr('当前月份下没有房型数量');
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('excel');
        $year = $request->get('year');
        $roomId = $request->get('room_id');
        $data['room_id'] = $roomId;
        $data['year'] = $year;
        $data['hotel_id'] = HotelRoomModel::get($roomId)->hotel->id;
        if ($year < date('Y')){
            return getErr('请选择大于或等于今年的年份!');
        }
        // 上传文件验证
        $result = $this->validate(['file' => $file], ['file'=>'require|fileExt:xls'],['file.require' => '请选择上传文件','file.fileExt' => '请上传xls文件!']);
        if(true !== $result){
            return getErr('文件上传失败!');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(PUBLIC_PATH.'room'.DS);
        if (!$info) {
            return getErr($file->getError());
        }
        $saveName = $info->getSaveName();
        $file = PUBLIC_PATH.'room'.DS.$saveName;
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load($file);
        $sheet = $objPHPExcel->getActiveSheet();
        $data = $this->formatImportExcelData($sheet,$data);
        $roomModel = new RoomModel();
        $roomModel->setUniqueIndex();
        $result = $roomModel->modifyAll($data);
        dump($result);
        return getSucc('文件上传成功');
    }

    public function exportExcel($roomId,$year)
    {
        $hotelRoomModel = HotelRoomModel::get($roomId);
        $hotelModel = $hotelRoomModel->hotel;
        $roomAmount = $hotelModel->room()->count();
        $roomModel = new RoomModel();
        $where['expired_year'] = ['>=',date('Y')];
        $where['expired_year'] = $year;
        $where['room_id'] = $roomId;
        $roomData = $roomModel->field('room_amount,expired_month')
            ->where($where)
            ->select();
        $ret = [];
        if (!$roomData->isEmpty()){
            foreach ($roomData->toArray() as $index => $datum) {
                $ret[$datum['expired_month']][] = $datum;
            }
            $monthAmount = $roomModel->where($where)->group('expired_month')->column('expired_month');
        }else{
            $month = date('m');
            $monthAmount = [$month];
            $ret = $this->getTemplateData($year, $month);
        }

        $data['hotel_name'] = $hotelModel->hotel_name;
        $data['hotel_ename'] = $hotelModel->hotel_ename;
        $data['room_name'] = $hotelRoomModel->room_name;
        $data['room_ename'] = $hotelRoomModel->room_ename;
        $data['room_amount'] = $roomAmount;
        $data['month_amount'] = $monthAmount;
        $data['room_data'] = $ret;
        $this->excelName = $data['hotel_name'] . '_' . $data['room_name'] . '_' . $year;
        $this->excelInit();
        $this->formatRoomData($data);
        $this->excelSave();
    }

    protected function getTemplateData($year, $month, $roomAmount = 0, $return = [])
    {
        $dateTimestamp = strtotime($year.'-'.$month);
        $monthDayAmount = date('t',$dateTimestamp);
        for ($i = 1;$i <= $monthDayAmount;$i++) {
            $return[$month][$i] = [
                'room_amount'=>$roomAmount,
                'expired_month'=>$month,
            ];
        }
        return $return;
    }

    protected function formatImportExcelData($sheet, $inputData, $data = [])
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumn = letter_tran_number($highestColumn);
        if ($highestRow < 6 && $highestColumn <33){
            exception('请填写数据之后再提交!');
        }
        for($rowIndex=4;$rowIndex<=$highestRow;$rowIndex++){
            for($colIndex=1;$colIndex<=$highestColumn;$colIndex++){
                $addr = get_letter($colIndex).$rowIndex;
                $cell = $sheet->getCell($addr)->getValue();
                $cell = str_replace(' ','',$cell);
                if ($cell == '月份'){
                    $monthAddr = get_letter($colIndex + 1).$rowIndex;
                    $monthCell = $sheet->getCell($monthAddr)->getValue();
                    $month = trim($monthCell,'月');
                    $result = preg_match('/^0[1-9]|1[0-2]$/', $month);
                    if($result === 0){
                        exception('月份格式错误,个位月份请带0前缀!');
                    }
                }
                if ($rowIndex % 3 == 0){
                    $remainAddr = get_letter($colIndex + 2).$rowIndex;
                    $remainCell = $sheet->getCell($remainAddr)->getValue();
                    if (!is_null($remainCell)){
                        $ret['hotel_id'] = $inputData['hotel_id'];
                        $ret['room_id'] = $inputData['room_id'];
                        $ret['expired_year'] = $inputData['year'];
                        $ret['expired_month'] = $month;
                        $ret['expired_day'] = $colIndex;
                        $ret['room_amount'] = $remainCell;
                        if ($colIndex > 31 || $colIndex<=0){
                            exception($month.'月格式错误,没有数据或超过31条数据!');
                        }
                        if ($remainCell > 255 || $colIndex<=0){
                            exception($month.'月'.$colIndex.'日格式错误,没有数据或房型数量大于255间!');
                        }
                        array_push($data,$ret);
                    }
                }
            }
        }
        return $data;
    }

    protected function excelInit()
    {
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()
            ->setCreator("ZYone")
            ->setLastModifiedBy("ZYone")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        // 设置每一列宽度
        $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑')->setSize(16)->setBold(true);//字体size
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $excelSheet = $objPHPExcel->getActiveSheet();
        $excelSheet->getDefaultRowDimension()->setRowHeight(80);
        $excelSheet->getDefaultColumnDimension()->setWidth(11);
        $excelSheet->getColumnDimension('A')->setWidth(16);
        $excelSheet->getColumnDimension('B')->setWidth(46);

        $excelSheet->mergeCells('C1:AG3');
        $excelSheet->getStyle('C1:AG3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('333f4f');//填充班级背景颜色
        // 表头
        $excelSheet
            ->setCellValue('A1', '酒店名称')
            ->setCellValue('A2', '房型名称')
            ->setCellValue('A3', '房型总数');
        $this->excelSheet = $excelSheet;
        $this->excelObj = $objPHPExcel;

    }

    protected function formatRoomData($roomData)
    {
        $excelSheet = $this->excelSheet;
        $excelSheet
            ->setCellValue('B1', $roomData['hotel_name'].'/'.$roomData['hotel_ename'])
            ->setCellValue('B2', $roomData['room_name'].'/'.$roomData['room_ename'])
            ->setCellValue('B3', $roomData['room_amount']);
        $count = (count($roomData['month_amount'])+1) * 3;
        for($k = 4,$times = 0;$k <= $count;$k++){
            $month = $roomData['month_amount'][$times];
            $excelSheet->setCellValue('B' . $k, $month.'月');
            $excelSheet->setCellValue('A' . $k, '月份');
            $excelSheet->mergeCells('C' . $k . ':AG' . $k);
            $excelSheet->getStyle('C' . $k . ':AG' . $k)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5dce4');

            $k++;
            $excelSheet->mergeCells('A' . $k . ':B' . $k);
            $excelSheet->setCellValue('A' . $k, '日期');
            for ($i = 1; $i <= 31; $i++) {
                $order = get_letter($i + 2) . $k;
                $excelSheet->getStyle($order)->getFont()
                    ->setSize(14)
                    ->setBold(false)
                    ->setItalic(false);//字体size
                $excelSheet->getStyle($order)->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $excelSheet->setCellValue($order, $i);
            }

            $k++;
            $excelSheet->mergeCells('A' . $k . ':B' . $k);
            $excelSheet->setCellValue('A' . $k, '剩余数量 (  间 )');
            foreach ($roomData['room_data'][$month] as $index => $datum) {
                $order = get_letter($index + 3) . $k;
                $excelSheet->getStyle($order)->getFont()
                    ->setSize(22)
                    ->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_DARKGREEN))
                    ->setBold(false)
                    ->setItalic(false);//字体size
                $excelSheet->setCellValue($order, $datum['room_amount']);
            }
            $times++;
        }
    }


    protected function excelSave()
    {
        $title_excel = $this->excelName;
        // 设置 sheet 名字
        $this->excelSheet->setTitle($title_excel);
        // 设置打开时选择 第一个 sheet
        $this->excelObj->setActiveSheetIndex(0);

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $title_excel . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excelObj, 'Excel5');
        $objWriter->save('php://output');
    }


}

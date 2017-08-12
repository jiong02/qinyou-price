<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-07-10
 * Time: 10:38
 */

namespace app\common\components;

use app\ims\model\HotelRoomModel;
use app\ims\model\RoomModel;
use think\Request;
use PHPExcel;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;

class Excel extends PHPExcel
{
    public $fileName;
    public $sheetName;

    public function init()
    {
        $this->fileName = uniqid();
        $this->sheetName = 'sheet';
        $this->setFileProperty();
        $this->setDefaultProperty();
    }

    protected function setFileProperty()
    {
        $this->getProperties()
            ->setCreator("ZYone")
            ->setLastModifiedBy("ZYone")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
    }

    protected function setDefaultProperty()
    {
        // 设置每一列宽度
        $this->getDefaultStyle()->getFont()->setName('宋体')->setSize(12);//字体size
        $this->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $this->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        $this->getActiveSheet()->getDefaultColumnDimension()->setWidth(11);

    }

    public function setHeader($headerData, $headerDefaultWidth = 10, $headerOrder = 1)
    {
        $this->getActiveSheet()->getColumnDimension('A')->setWidth($headerDefaultWidth);
        $this->setContent($headerData, $headerOrder);
    }

    public function defaultExport($header, $body)
    {
        $this->init();
        $this->setHeader($header);
        $this->setBody($body);
        $this->export();
    }

    public function setBody($body)
    {
        foreach ($body as $key => $value) {
            $this->setContent($value, $key + 2);
        }
    }

    public function setContent($content, $contentOrder = 2)
    {
        foreach ($content as $key => $value) {
            $letter = get_letter($key + 1);
            $letterOrder = $letter . $contentOrder;
            $this->getActiveSheet()->setCellValue($letterOrder, $value);
        }
    }

    public function export()
    {
        // 设置 sheet 名字
        $this->getActiveSheet()->setTitle($this->sheetName);
        // 设置打开时选择 第一个 sheet
        $this->setActiveSheetIndex(0);

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->fileName . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel5');
        $objWriter->save('php://output');
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
}
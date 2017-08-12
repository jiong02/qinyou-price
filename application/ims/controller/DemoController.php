<?php
namespace app\ims\controller;

use PHPExcel;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Color;
use PHPExcel_IOFactory;
use PHPExcel_RichText;

class DemoController extends BaseController
{
    public $excelObj;
    public $excelSheet;
    public $excelName;



    public function excelExport()
    {
        $this->excelName = '呵呵哒';
        $this->excelInit();
        $this->formatRoomData();
        $this->excelSave();
    }

    public function excelInit()
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

    public function excelSave()
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

    public function formatRoomData($roomData)
    {
        $excelSheet = $this->getExcelSheet();
        $excelSheet
            ->setCellValue('B1', $roomData->hotel_name.'/'.$roomData->hotel_ename)
            ->setCellValue('B2', $roomData->room_name.'/'.$roomData->room_ename)
            ->setCellValue('B3', $roomData->room_amount);
        for ($k = 4;$k<=$monthAmount + 4;$k++){

            $excelSheet->setCellValue('B'.$k, '月');
            $excelSheet->setCellValue('A'.$k, '月份');
            $excelSheet->mergeCells('C'.$k.':AG'.$k);
            $excelSheet->getStyle('C'.$k.':AG'.$k)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5dce4');

            $k++;
            $excelSheet->mergeCells('A'.$k.':B'.$k);
            $excelSheet->setCellValue('A'.$k,'日期');
            for ($i=1;$i<=31;$i++){
                $order = get_letter($i + 2).$k;
                $excelSheet->getStyle($order)->getFont()
                    ->setSize(14)
                    ->setBold(false)
                    ->setItalic(false);//字体size
                $excelSheet->getStyle($order)->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $excelSheet->setCellValue($order,$i);
            }

            $k++;
            $excelSheet->mergeCells('A'.$k.':B'.$k);
            $excelSheet->setCellValue('A'.$k,'剩余数量 (  间 )');
            for ($i=1;$i<=31;$i++){
                $order = get_letter($i + 2).$k;
                $excelSheet->getStyle($order)->getFont()
                    ->setSize(22)
                    ->setColor(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ))
                    ->setBold(false)
                    ->setItalic(false);//字体size
                $excelSheet->setCellValue($order,$i);
            }
        }
    }
}
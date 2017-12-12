<?php
namespace Common\Service;

/**
 * class GetExcelService
 * @package GetExcelService
 * [PHP EXCEL]
 */
class GetExcelService extends BaseService
{
    private $line       = array(); // 行标

    public function __construct()
    {
        $this->line = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    }

    public function test()
    {
        $title = array('id','账号','类型','商家分类','店铺名称','店铺地址','开门时间','关门时间','起送价格','配送价格','客服电话','创建时间');
        $key = array('id','account','type','merchant_type_id','shop_name','shop_address','start_time','end_time','lowest_price','delivery_fee','service_telephone','create_time');
        $model = M('merchant')->where()->field('id,account,type,merchant_type_id,shop_name,shop_address,start_time,end_time,lowest_price,delivery_fee,service_telephone,create_time')->select();
        $param = array('title'=>'111111');
        $this->createExcel($title,$key,$model,$param);
    }

    /**
     * [createExcel Excel实例化类库]
     * @author zhouwei
     * @param  array  $title    [Excel第一列]
     * @param  array  $modelKey [查询的字段]
     * @param  array  $model    [数据库查询的数据源]
     * @param  array  $param    [参数 title = '文件名称不包含(.xls)']
     * @todo   最多只能输出 26列数据 超出报错
     * @return [type]           [返回一张Excel]
     */
    public function createExcel($title=array(),$modelKey=array(),$model=array(),$param=array())
    {
        // ------------- 实例化PHPExcel类库 -------------
        vendor("PHPExcel.PHPExcel");//如果这里提示类不存在，肯定是你文件夹名字不对。
        $objPHPExcel = new \PHPExcel();//这里要注意‘\’ 要有这个。因为版本是3.2了。
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);//设置保存版本格式
        ob_end_clean(); // 清空缓冲区
        // ------------- 实例化PHPExcel类库 -------------
        
        // ------------- PHPExcel执行显示 -------------
        // dump($model);exit();
        $count = count($title); // 统计标题数量
        $titleValue = array_slice($this->getLine($count),0,$count); // 取多少字母
        foreach ($titleValue as $key => $value) {
            // 遍历标题
            $objPHPExcel->getActiveSheet()->setCellValue($value.'1',$title[$key]); 
        }
        foreach($model as $keyModel =>$valueModel){
            foreach ($titleValue as $k => $v) {
                $objPHPExcel->getActiveSheet()->setCellValue($v.($keyModel+2),$valueModel[$modelKey[$k]]);
            }
        }
        // ------------- PHPExcel执行显示 -------------
        
        // ------------- 将数据变成EXCEl -------------
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename='.$param['title'].'.xls');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        // ------------- 将数据变成EXCEl -------------
    }


    public function getLine($len = 0)
    {
        $one_index = -1;
        $two_index  = 0;
        $result     = array();

        while(1){
            if($one_index == -1){
                $result[] = $this->line[$two_index];
            }else{
                $result[] = $this->line[$one_index].$this->line[$two_index];
            }
            if($two_index == 25){
                $two_index = 0;
                $one_index = $one_index+1;
            }else {
                $two_index = $two_index + 1;
            }
            if(count($result) == $len){
                break;
            }
        }
        return $result;
    }

}

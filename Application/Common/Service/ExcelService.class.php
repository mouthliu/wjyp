<?php
namespace Common\Service;

/**
 * 下载EXCEl 简单版本
 */
class ExcelService extends BaseService
{

	/**
	 * [inquireModel 获取数据]
	 * @author zhouwei
	 * @param array $resource 查询条件 ['model'=>'','where'=>array(),'order'=>'','field'=>'']
	 * @return [type] [数组]
	 */
	function inquireModel($resource)
	{
		$db = M($resource['model'])->where($resource['where'])->order($resource['order'])->field($resource['field'])->select();
		return $db;
	}
	/**
	 * [exportexcel 生成Excel]
	 * @author zhouwei
	 * @param  array  $data     [查询条件组]
	 * @param  array  $title    [ excel的第一行标题,一个数组,如果为空则没有标题]
	 * @param  string $filename [下载的文件名]
	 * @return [type]           [xxxxx.excel]
	 */
	function exportexcel($data=array(),$title=array(),$filename='report'){
	    header("Content-type:application/octet-stream");
	    header("Accept-Ranges:bytes");
	    header("Content-type:application/vnd.ms-excel");  
	    header("Content-Disposition:attachment;filename=".$filename.".xls");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	    $resource = $this->inquireModel($data); // 查询数组
	    //导出xls 开始
	    if (!empty($title)){
	        foreach ($title as $k => $v) {
	            $title[$k]=iconv("UTF-8", "GB2312",$v);
	        }
	        $title= implode("\t", $title);
	        echo "$title\n";
	    }
	    if (!empty($resource)){
	        foreach($resource as $key=>$val){
	            foreach ($val as $ck => $cv) {
	                $resource[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
	            }
	            $resource[$key]=implode("\t", $resource[$key]);
	            
	        }
	        echo implode("\n",$resource);
	    }
	}
}
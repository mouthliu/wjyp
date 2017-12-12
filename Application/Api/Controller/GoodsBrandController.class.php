<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 分类模块控制器
 * Class GoodsBrandController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class GoodsBrandController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 根据品牌id获取品牌信息
     * 请求方式:post
     * 请求参数：
     *   品牌ID : brand_id
     */
    function getBrandInfo(){
        if(empty($_POST['brand_id'])){
            apiResponse('0','请传入品牌id');
        }
        $list = D('GoodsBrand','Logic')->getBrandInfo(array($_POST['id']));
        if($list){
            apiResponse('1','获取品牌数据成功',$list,count($list));
        }else{
            apiResponse('0','获取品牌数据失败');
        }
    }
}
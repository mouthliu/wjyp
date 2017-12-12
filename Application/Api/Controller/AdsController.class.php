<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 广告轮播模块控制器
 * Class AdsController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class AdsController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取广告/轮播
     * 请求方式: post
     * 请求参数:
     * 位置ID：position
     *    (1.首页轮播图 2.拍卖页轮播图 3.一元夺宝首页 4.线下商铺首页 5.无界书院首页.6无界驿站首页)
     * 显示个数: num (可选,无参数显示全部)
     */
    public function adsList(){
        if(empty($_POST['position'])){
            apiResponse('0','请输入位置参数');
        }
        $res = D('Ads','Logic')->adsList(I('post.'));
        if($res){
            apiResponse('1','获取数据成功',$res);
        }else{
            apiResponse('0','暂无数据');
        }
    }
}
<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 分类模块控制器
 * Class GoodsCategoryController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class GoodsCategoryController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 顶部分类条(显示顶级分类简称)
     * 请求方式:post
     * 请求参数:
     */
    public function topNav(){
        D('GoodsCategory','Logic')->topNav(I('post.'));
    }
    /**
     * 获取子类
     * 请求方式: post
     * 请求参数:
     * 分类id: cate_id
     * 分页参数: p
     */
    public function getChildCate(){
        if(empty($_POST['cate_id'])){
            apiResponse('0','请选择一个分类');
        }
        if(empty($_POST['p'])){
            apiResponse('0','请选择分页参数');
        }
        $list = D('GoodsCategory','Logic')->getChildCate($_POST['cate_id'],$_POST['p']);
        if($list){
            apiResponse('1','获取成功',$list,count($list));
        }
    }

    /**
     * 获取某一分类下所有分类
     * 请求方式 : post
     * 请求参数 :
     *   分类ID ：cate_id
     *   分页参数:p
     */
    public function getCate(){
        if(empty($_POST['cate_id'])){
            apiResponse('0','请选择一种分类');
        }
        if(empty($_POST['p'])){
            apiResponse('0','请选择分页参数');
        }
        $res = D('GoodsCategory','Logic')->getCate($_POST['cate_id'],$_POST['p']);
        if(!$res){
            apiResponse('0','此分类下没有数据');
        }else{
            apiResponse('1','获取成功',$res,count($res));
        }
    }

    /**
     * 分类首页
     * 请求方式 :post
     * 请求参数:
     * 分页参数:p
     * 顶级分类id:cate_id(可选)
     */
    public function cateIndex(){
        $user_id = $this->checkLogin();
        D('GoodsCategory','Logic')->cateIndex(I('post.'),$user_id);
    }
}
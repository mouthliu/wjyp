<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 无界预购商品模块控制器
 * Class PreBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class PreBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取预购商品首页函数
     * 请求方式：POST
     * 请求参数：
     *  分类id: cate_id(可选)
     *  分页参数 : p
     */
    public function preBuyIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('PreBuy','Logic')->preBuyIndex(I('post.'));
    }
    /**
     * 预购商品三级分类商品列表
     * 请求参数 : two_cate_id
     * three_cate_id (可选)
     * p
     */
    public function threeList(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        if(empty($_POST['two_cate_id'])){
            apiResponse('0','二级分类id不能为空');
        }
        D('PreBuy','Logic')->threeList(I('post.'));
    }

    /**
     * 获取商品详情页信息
     * 请求方式:post
     * 请求参数
     * 团购id ：PreBuy_id
     */
    public function preBuyInfo(){
        if(empty($_POST['pre_buy_id'])){
            apiResponse('0','请输入预购ID');
        }
        $user_id = $this->checkLogin();
        D('PreBuy','Logic')->preBuyInfo(I('post.'),$user_id);
    }

}
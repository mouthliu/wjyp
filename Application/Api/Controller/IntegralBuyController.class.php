<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 无界商店商品模块控制器
 * Class IntegralBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class IntegralBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取无界商店首页函数
     * 请求方式：POST
     * 请求参数：
     *  分类id: cate_id(可选)
     *  分页参数 : p
     */
    public function integralBuyIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('IntegralBuy','Logic')->integralBuyIndex(I('post.'));
    }
    /**
     * 无界商店三级分类商品列表
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
        D('IntegralBuy','Logic')->threeList(I('post.'));
    }
    /**
     * 获取无界商店详情页信息
     * 请求方式:post
     * 请求参数
     * 商品id ：integral_buy_id
     */
    public function integralBuyInfo(){
        if(empty($_POST['integral_buy_id'])){
            apiResponse('0','请输入积分商品编号');
        }
        $user_id = $this->checkLogin();
        D('IntegralBuy','Logic')->integralBuyInfo(I('post.'),$user_id);
    }

    /**
     * doChange 执行兑换
     * product_id(可选)
     */
    public function doChange(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['integral_buy_id'])){
            apiResponse('0','请输入积分商品编号');
        }
        if(empty($_POST['shipping_id'])){
            apiResponse('0','请输入快递编号');
        }
        if(empty($_POST['goods_num'])){
            apiResponse('0','请输入兑换数量');
        }
         if(empty($_POST['address_id'])){
             apiResponse('0','请输入收货地址');
         }
        D('IntegralBuy','Logic')->doChange(I('post.'),$user_id);
    }
}
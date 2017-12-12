<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 购物车模块控制器
 * Class CartController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class CartController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 加入购物车
     * 请求参数 :
     * 商品id:goods_id
     * 货品id:product_id(可选)
     * 商品数量: num
     */
    function addCart(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['goods_id'])){
            apiResponse('0','请选择商品');
        }
        if(empty($_POST['num'])){
            apiResponse('0','请选择数量');
        }

        D('Cart','Logic')->addCart(I('post.'),$user_id);

    }
    /**
     * 购物车列表
     * 请求方式: post
     * 请求参数: 无
     */
    public function cartList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        D('Cart','Logic')->cartList($user_id);
    }

    /**
     * 修改购物车
     * 请求方式：post
     * 参数：购物车json格式[{"cart_id":"购物车ID","goods_id":"商品ID"，"product_id":"货品ID","num":"数量"}]
     */
    function editCart(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Cart','Logic')->editCart(I('post.'),$user_id);
    }
    /**
     * 删除购物车
     * 请求参数 : 购物车id数组
     */
    function delCart(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['cart_id_json'])){
            apiResponse('0','请选择商品');
        }
        D('Cart','Logic')->delCart(I('post.'));
    }
    /**
     * 加入我的收藏
     * 请求参数 购物车id
     */
    function addCollect(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['cart_id_json'])){
            apiResponse('0','请选择商品');
        }
        D('Cart','Logic')->addCollect(I('post.'),$user_id);
    }
}
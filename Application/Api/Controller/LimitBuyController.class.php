<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 限量购商品模块控制器
 * Class LimitBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class LimitBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取限量购商品首页函数
     * 请求方式：POST
     * 请求参数：
     *  分页参数 : p
     *  stage_id(场次id,可选，默认当前时间)
     */
    public function limitBuyIndex(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('LimitBuy','Logic')->limitBuyIndex(I('post.'),$user_id);
    }

    /**
     * 获取商品详情页信息
     * 请求方式:post
     * 请求参数
     * 团购id ：LimitBuy_id
     */
    public function limitBuyInfo(){

        if(empty($_POST['limit_buy_id'])){
            apiResponse('0','请输入限量购ID');
        }
        $user_id = $this->checkLogin();
        D('LimitBuy','Logic')->limitBuyInfo(I('post.'),$user_id);
    }


    /**
     * 设置限量购活动提醒
     * 请求方式 ：post
     * 请求参数 :
     *     限量购活动id：
     */
    public function remindMe(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['limit_buy_id'])){
            apiResponse('0','请输入限量购活动ID');
        }
        D('LimitBuy','Logic')->remindMe(I('post.'),$user_id);
    }

    /**
     * 购买商品
     *
     *  limit_buy_id 对应活动id
     *
     */
     public function doLimitBuy(){
         $user_id = $this->checkLogin();
         $this->returnNotLoginMsg($user_id);
         if(empty($_POST['limit_buy_id'])){
             apiResponse('0','请输入限量购活动ID');
         }
         //判断是否超过限制数量(查询订单,查询购物车)
         $info =M('LimitBuy')->where("id={$_POST['limit_buy_id']}")->find();
         if($info['limit_store'] <= $info['sell_num']){
             //设置活动状态未结束
             M('LimitBuy')->where("id={$_POST['limit_buy_id']}")->save(array('is_end'=>1));
             apiResponse('0','已被抢光');
         }
         apiResponse('1','去下单',array('limit_buy_id'=>$_POST['limit_buy_id']));
     }
}
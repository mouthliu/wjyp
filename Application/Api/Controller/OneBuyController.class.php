<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 一元购商品模块控制器
 * Class OneBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class OneBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取拍卖商品首页函数
     * 请求方式：POST
     * 请求参数：
     *  分页参数 : p
     *  排序（可选其一）
     *    is_hot 热门
     *    (进度)add_num 1 正序 2 倒序
     *    （人次）person_num 1 正序 2 倒序
     *    （积分）integral 1 正序 2 倒序
     */
    public function oneBuyIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }

        D('OneBuy','Logic')->oneBuyIndex(I('post.'));
    }

    /**
     * 获取商品详情页信息
     * 请求方式:post
     * 请求参数
     * 团购id ：OneBuy_id
     */
    public function oneBuyInfo(){
        if(empty($_POST['one_buy_id'])){
            apiResponse('0','请输入一元购ID');
        }
        D('OneBuy','Logic')->oneBuyInfo(I('post.'));
    }


    function joinOneBuy(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['one_buy_id'])){
            apiResponse('0','请输入一元购ID');
        }
        if(empty($_POST['type'])){
            apiResponse('0','请输入支付类型');
        }
        D('OneBuy','Logic')->joinOneBuy(I('post.'),$user_id);
    }
}
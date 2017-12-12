<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 团购商品模块控制器  11
 * Class GroupBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class GroupBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取团购商品首页函数
     * 请求方式：POST
     * 请求参数：
     *  分类id: cate_id(可选)
     *  分页参数 : p
     */
    public function groupBuyIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空.');
        }
        D('GroupBuy','Logic')->groupBuyIndex(I('post.'));
    }
    /**
     * 拼团购三级分类商品列表
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
        D('GroupBuy','Logic')->threeList(I('post.'));
    }
    /**
     * 获取商品详情页信息
     * 请求方式:post
     * 请求参数
     * 团购id ：GroupBuy_id
     */
    public function groupBuyInfo(){
        if(empty($_POST['group_buy_id'])){
            apiResponse('0','请输入团购ID.');
        }
        $user_id = $this->checkLogin();
//        $user_id = 54;
        D('GroupBuy','Logic')->groupBuyInfo(I('post.'),$user_id);
    }
    /**
     * 单独购买
     * 请求参数:团购id
     */
    public function buyToOne(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['group_buy_id'])){
            apiResponse('0','请输入团购ID.');
        }
        D('GroupBuy','Logic')->buyToOne(I('post.'),$user_id);
    }
    /**
     * 一键开团（当支付成功，就当是开团）
     *   请求参数: 团购id
     */
    public function justBegin(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['group_buy_id'])){
            apiResponse('0','请输入团购ID.');
        }
        D('GroupBuy','Logic')->justBegin(I('post.'),$user_id);
    }
    /**
     * 我要参团页
     * 请求参数: 团购记录id log_id
     */
    public function goGroup(){
        if(empty($_POST['log_id'])){
            apiResponse('0','请输入要参团的id');
        }
        D('GroupBuy','Logic')->goGroup(I('post.'));
    }
    /**
     * 去参团
     *  请求参数  团购记录Id log_id
     */
    public function addGroup(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['log_id'])){
            apiResponse('0','请输入要参团id啊');
        }
        D('GroupBuy','Logic')->addGroup(I('post.'),$user_id);
    }
}
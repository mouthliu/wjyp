<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 拍卖商品模块控制器
 * Class AuctionController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class AuctionController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取拍卖商品首页函数
     * 请求方式：POST
     * 请求参数：
     *  分页参数 : p
     *  是否是预告拍卖：next 1 (可选，默认是今日)
     */
    public function auctionIndex(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Auction','Logic')->auctionIndex(I('post.'),$user_id);
    }

    /**
     * 获取商品详情页信息
     * 请求方式:post
     * 请求参数
     * 团购id ：Auction_id
     */
    public function auctionInfo(){
        if($user_id = $this->checkLogin()){
            $_POST['user_id'] = $user_id;
        }
        if(empty($_POST['auction_id'])){
            apiResponse('0','请输入拍卖ID');
        }

        D('Auction','Logic')->AuctionInfo(I('post.'),$user_id);
    }

    /**
     * 参加竞拍活动
     * 请求方式:post
     * 请求参数:
     *    拍卖活动ID: auction_id
     *    竞拍价格:bid_price
     */
    public function applyAuction(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['auction_id'])){
            apiResponse('0','请输入拍卖活动ID');
        }
        //判断竞拍价格
        if(empty($_POST['bid_price'])){
            apiResponse('0','请输入竞拍价格');
        }
        D('Auction','Logic')->applyAuction(I('post.'),$user_id);
    }

    /**
     * 设置拍卖提醒
     * 请求方式 ： post
     * 请求参数 :
     *     拍卖活动ID：
     */
    public function remindMe(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['auction_id'])){
            apiResponse('0','请输入拍卖活动ID');
        }
        D('Auction','Logic')->remindMe(I('post.'),$user_id);
    }
}
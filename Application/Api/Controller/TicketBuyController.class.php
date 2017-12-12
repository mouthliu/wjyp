<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 无界预购商品模块控制器
 * Class TicketBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class TicketBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取票券区首页函数
     * 请求方式：POST
     * 请求参数：
     *  分类id: cate_id(可选)
     *  分页参数 : p
     */
    public function ticketBuyIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('TicketBuy','Logic')->ticketBuyIndex(I('post.'));
    }
    /**
     * 获取票券区商品详情页信息
     * 请求方式:post
     * 请求参数
     * 票券商品id ：ticket_buy_id
     */
    public function ticketBuyInfo(){
        if(empty($_POST['ticket_buy_id'])){
            apiResponse('0','请输入票券商品ID');
        }
        $user_id = $this->checkLogin();
        D('TicketBuy','Logic')->ticketBuyInfo(I('post.'),$user_id);
    }
    /**
     * 票券区三级分类商品列表
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
        D('TicketBuy','Logic')->threeList(I('post.'));
    }

}
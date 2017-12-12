<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 福利社模块控制器
 * Class WelfareController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class WelfareController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取优惠券列表
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function ticketList()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        D('Welfare', 'Logic')->ticketList(I('post.'), $user_id);
    }

    /**
     * 领取优惠券
     * 请求方式:post
     * 请求参数:优惠券ID
     * 用户ID
     */
    public function getTicket(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['ticket_id'])) {
            apiResponse('0', '请输入优惠券id');
        }
        D('Welfare', 'Logic')->getTicket(I('post.'),$user_id);

    }
    /**
     * 红包封面列表
     * 请求方式 : post
     * 请求参数 :
     *    分页参数 :p
     */
    public function faceList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        D('Welfare','Logic')->faceList(I('post.'));
    }
    /**
     * 红包广告
     * 请求方式 : post
     * 请求参数 :
     *    商家id :
     */
    public function bonusList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['bonus_id'])) {
            apiResponse('0', '请选择红包id');
        }
        D('Welfare','Logic')->bonusList(I('post.'),$user_id);
    }
    /**
     * 领取红包
     * 请求方式:post
     * 请求参数: 红包id (bonus_face)
     *          红包金额
     */
    public function getBonus(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['bonus_id'])) {
            apiResponse('0', '请输入红包id');
        }
        if (empty($_POST['bonus_val'])) {
            apiResponse('0', '请输入红包金额');
        }
        D('Welfare', 'Logic')->getBonus(I('post.'),$user_id);
    }

    /**
     * 获取分享内容
     * type 1微信 2微博 3QQ
     */
    public function shareContent(){
        if (empty($_POST['bonus_id'])) {
            apiResponse('0', '请选择红包id');
        }
        D('Welfare', 'Logic')->shareContent(I('post.'));
    }
    public function shareBack(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['type'])) {
            apiResponse('0', '请填写分享的类型');
        }
        if (empty($_POST['title'])) {
            apiResponse('0', '请填写分享的标题');
        }
        if (empty($_POST['content'])) {
            apiResponse('0', '请填写分享的内容');
        }
        if (empty($_POST['url'])) {
            apiResponse('0', '请填写分享的链接');
        }
        if (empty($_POST['share_type'])) {
            apiResponse('0', '请填写分享的内容类型');
        }
        if (empty($_POST['pic'])) {
            apiResponse('0', '请填写分享的图片');
        }
        if (empty($_POST['id_val'])) {
            apiResponse('0', '请填写分享目标id');
        }
        D('Welfare', 'Logic')->shareBack(I('post.'),$user_id);
    }
}
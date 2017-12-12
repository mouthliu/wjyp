<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 消息模块控制器
 * Class UserMessageController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class UserMessageController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 获取消息中心
     * 请求方式: post
     */
    public function newMsg(){
        if(empty($_POST['p'])){
            apiResponse('请输入分页参数');
        }
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('UserMessage','Logic')->newMsg(I('post.'),$user_id);
    }

    /**
     * 获取通知消息列表
     * 请求方式:post
     * 需要参数:
     *  分页参数:p
     */
    public function msgList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('请输入分页参数');
        }
        D('UserMessage','Logic')->msgList(I('post.'),$user_id);
    }

    /**
     * 获取公告列表
     * 请求方式:post
     * 需要参数:
     *  分页参数:p
     */
    public function announceList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('请输入分页参数');
        }
        D('UserMessage','Logic')->announceList(I('post.'),$user_id);
    }
    /**
     * 获取公告消息
     */
    public function announceInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['id'])){
            apiResponse('请输入公告id');
        }
        $info = M('Announce')->where("id = {$_POST['id']}")->find();
        if(!$info){
            apiResponse('0','获取失败');
        }
        unset($info['update_time']);
        unset($info['sort']);
        unset($info['parent_id']);
        unset($info['status']);
        $info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);
        apiResponse('1','获取成功',$info);
    }
    /**
     * 获取订单消息列表
     * 请求方式:post
     * 需要参数:
     *  分页参数:p
     */
    public function orderMsgList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('请输入分页参数');
        }
        D('UserMessage','Logic')->orderMsgList(I('post.'),$user_id);
    }

}
<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class BaseController
 * @package Api\Controller
 * 基类
 */
class BaseController extends controller{

    public function _initialize(){

    }

    /**
     * 验证登录是否已经过期
     */
    public function checkLogin(){
        unset($w);
        $token = $_SERVER['HTTP_TOKEN'];
        if(empty($token)){
            return 0;
        }
        $w['token']        = $token;
        $w['expired_time'] = array('egt', time());
        $user_id           = M('User')->where($w)->getField('id');
        return $user_id?$user_id:0;
    }

    /**
     * 根据得到的用户ID 如果已经失效返回错误信息
     * @param $user_id
     */
    public function returnNotLoginMsg($user_id){
        if(!$user_id){
            apiResponse('-1','登录失效');
        }
    }

    /**
     * 每次敏感操作都将token时限增加(感觉有用)
     */
    public function reExpiredTime($user_id = 0){
        $where['id'] = $user_id;
        D('User')->where($where)->setInc('expired_time',60);
    }


}

<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 登录注册模块控制器
 * Class RegisterController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class RegisterController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 发送验证码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 手机号：phone
     * 短信类型：type :
     *      注册activate
     *      忘记密码：retrieve
     *      解绑旧手机号：mod_bind
     *      绑定新手机号：re_bind (三方登录绑定)
     *      重置支付密码：re_pay_pwd
     */
    public function sendVerify(){
        if(empty($_POST['phone'])){
            apiResponse('0','请输入手机号');
        }
        if(!isMobile($_POST['phone'])){
            apiResponse('0','请输入正确的手机号');
        }
        if(!in_array($_POST['type'],array('activate','retrieve','mod_bind','re_bind','re_pay_pwd'))){
            apiResponse('0','短信类型错误');
        }

        D('Register','Logic')->sendVerify(I('post.'));
    }

    /**
     * 注册第一步
     * 传递参数的方式：post
     * 需要传递的参数：
     * 手机号：phone
     */
    public function registerOne(){
        if(empty($_POST['phone'])){
            apiResponse('0','请输入手机号');
        }
        D('Register','Logic')->registerOne(I('post.'));
    }

    /**
     * 验证短信验证码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 手机号：phone
     * 短信类型:注册activate 忘记密码：retrieve 解绑旧手机号：mod_bind 绑定新手机号：re_bind 重置支付密码：re_pay_pwd
     * 验证码：verify
     */
    public function checkVerify(){
        if(empty($_POST['phone'])){
            apiResponse('0','请输入手机号');
        }
        if(!isMobile($_POST['phone'])){
            apiResponse('0','请输入正确的手机号');
        }
        if(!in_array($_POST['type'],array('activate','retrieve','mod_bind','re_bind','re_pay_pwd'))){
            apiResponse('0','短信类型错误');
        }
        $res = D('Register','Logic')->checkVerify(I('post.'));
        if($res){
            apiResponse('1','验证码正确');
        }
    }


    /**
     * 注册
     * 请求方式:post
     * 请求参数:
     * 注册手机号: phone
     * 密码：password
     * 确认密码：confirmPassword
     * invite_code(邀请码 可选)
     */
    public function register(){
        if(empty($_POST['phone'])){
            apiResponse('0','请输入注册手机号');
        }
        if(empty($_POST['password'])){
            apiResponse('0','密码不能为空');
        }
        if($_POST['password'] !== $_POST['confirmPassword']){
            apiResponse('0','两次密码不一致，请重新输入');
        }
        if(strlen($_POST['password'])<6){
            apiResponse('0','密码至少为6位');
        }
        D('Register','Logic')->register(I('post.'));
    }

    /**
     * 登录函数
     * 传递参数方式: post
     * 需要传递参数
     * 电话号码 ：phone
     * 密码: password
     */
    public function login() {
        if(empty($_POST['phone'])) {
            apiResponse('0','请输入手机号');
        }
        if(empty($_POST['password'])){
            apiResponse('0','密码不能为空');
        }
        D('Register','Logic')->login(I('post.'));
    }

    /**
     * 忘记密码
     * 传递参数方式:post
     * 传递参数:
     * 手机号 ：phone
     * 验证码 ：verify
     * 新密码：newPassword
     * 确认密码: confirmPassword
     */
    public function resetPassword(){
        if(empty($_POST['verify'])){
            apiResponse('0','验证码不能为空');
        }
        if(empty($_POST['phone'])){
            apiResponse('0','请输入手机号');
        }
        if(strlen($_POST['newPassword'])<6){
            apiResponse('0','密码至少为6位');
        }
        if($_POST['newPassword'] !== $_POST['confirmPassword']){
            apiResponse('0','两次密码不一致，请重新输入');
        }
        D('Register','Logic')->resetPassword(I('post.'));
    }

    /**
     * 第三方登录
     * 首先调取第三方进行授权 然后同意 获取到对方给的open_id head_pic nickname type 在user_bind表中进行查找，
     * 如果用户不存在就添加一条记录user_id = 0 的
     * 同时去跳转绑定手机号 绑定成功之后将得到的id 加入到user_bind 中
     * 1.没有进行三方登录
     * 2.已经绑定
     * 3.登陆了但是没有绑定手机
     * 请求参数:
     *   openid
     *   type 1微信 2微博 3qq
     *   head_pic(可选)
     *   nickname(可选)
     */
    public function otherLogin(){
        if(empty($_POST['openid'])){
            apiResponse('0','请输入openid');
        }
        if(empty($_POST['type'])){
            apiResponse('请输入登录类型');
        }
        D('Register','Logic')->otherLogin(I('post.'));
    }

    /**
     * 三方登录绑定手机
     * 请求方式:post
     * 请求参数:
     *     绑定id: bind_id
     *     绑定手机号:phone
     *     验证码：verify

     */
        public function otherLoginBind(){
        if(empty($_POST['bind_id'])){
            apiResponse('请输入绑定id');
        }
        if(empty($_POST['phone'])){
            apiResponse('请输入绑定手机号');
        }
        if(empty($_POST['verify'])){
            apiResponse('请输入验证码');
        }

        D('Register','Logic')->otherLoginBind(I('post.'));
    }
    /**
     * 扫码注册
     * 手机号
     * 密码
     * 验证码
     * 邀请码
     */
    public function scanRegister(){
        if(empty($_POST['phone'])){
            apiResponse('0','请输入手机号');
        }
        if(!isMobile($_POST['phone'])){
            apiResponse('0','请输入正确的手机号');
        }
        if(empty($_POST['verify'])){
            apiResponse('请输入验证码');
        }
        if(empty($_POST['password'])){
            apiResponse('0','密码不能为空');
        }
        if(strlen($_POST['password'])<6){
            apiResponse('0','密码至少为6位');
        }
        D('Register','Logic')->scanRegister(I('post.'));
    }

    function scanPage(){
        //接收到参数--邀请码
        $invite_code = $_GET['invite_code'];
        $action = C('API_URL').'/index.php/Api/Register/ScanRegister';
        $this->assign("action",$action);
        $this->assign("invite_code",$invite_code);
        $this->display("Register/ScanRegister");
    }
}
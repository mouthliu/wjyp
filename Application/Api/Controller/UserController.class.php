<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 会员模块控制器
 * Class UserController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class UserController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 个人中心
     * 传递参数的方式：post
     * 需要传递的参数：null
     */
   public function userCenter(){
       $user_id = $this->checkLogin();
       $this->returnNotLoginMsg($user_id);

       D('User','Logic')->userCenter($user_id);
   }

    /**
     * 个人设置
     * 请求类型:post
     * 请求参数:
     */
    public function setting(){
         $user_id = $this->checkLogin();
         $this->returnNotLoginMsg($user_id);
         D('User','Logic')->setting($user_id);
    }
    /**
     * bindOther
     * 绑定第三方
     * type '1'=>'微信','2'=>'微博','3'=>'QQ'
     */
    public function bindOther(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['openid'])) {
            apiResponse('0', '请填写openid');
        }
        if (empty($_POST['type'])) {
            apiResponse('0', '请填写绑定类型');
        }
        D('User','Logic')->bindOther(I('post.'),$user_id);
    }
    /**
     * removeBind
     * 解除绑定第三方
     * type '1'=>'微信','2'=>'微博','3'=>'QQ'
     */
    public function removeBind(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['type'])) {
            apiResponse('0', '请填写解除绑定类型');
        }
        if (in_array(array('1','2','3'),$_POST['type'])) {
            apiResponse('0', '绑定类型不正确');
        }
        D('User','Logic')->removeBind($_POST['type'],$user_id);
    }
    /*
     * 获取会员个人资料
     * 传递参数的方式： post
     * 需要传递的参数
     */
    public function userInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        //获取数据
        D('User','Logic')->userInfo($user_id);
    }

    /**
     * 修改个人资料
     * 传递参数方式:post
     * 需要传递的参数:
     * 昵称：nickname
     * 姓名：sex
     * 邮箱：email
     * 所在省:province_id
     * 所在市:city_id
     * 所在区:area_id
     * 所在街道:street_id
     * 头像：head_pic(可选)
     */
    public function editInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['nickname'])) {
            apiResponse('0', '请填写昵称');
        }
//        if (empty($_POST['sex'])) {
//            apiResponse('0', '请选择性别');
//        }

        D('User','Logic')->editInfo(I('post.'),$user_id);
    }
    /**
     * 获取实名认证信息
     * 请求方式:post
     * 请求参数：
     */
    public function getAuth(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->getAuth($user_id);
    }
    /**
     * 实名认证接口
     * 请求方式： post
     * 请求参数:
     * 真实姓名 ：real_name
     * 身份证号: id_card_num
     * 身份证正面照片: id_card_pic(传照片)
     */
    public function addAuth(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['real_name'])){
            apiResponse('0','请输入真实姓名');
        }
        if(empty($_POST['id_card_num'])){
            apiResponse('0','请输入身份证号');
        }
        if(empty($_FILES['id_card_pic']['name'])){
            apiResponse('0','请上传身份证正面照');
        }
        D('User','Logic')->addAuth(I('post.'),$user_id);
    }

    /**
     * 设置登录密码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 新密码：newPassword
     * 确认新密码：rePassword
     */
    public function setPassword(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        if (empty($_POST['newPassword'])) {
            apiResponse('0', '请填写新密码');
        }
        if(strlen($_POST['newPassword'])<6){
            apiResponse('0','密码至少为6位');
        }
        if ($_POST['newPassword'] !== $_POST['rePassword']) {
            apiResponse('0', '两次密码不一致');
        }
        D('User', 'Logic')->setPassword(I('post.'), $user_id);
    }

    /**
     * 修改登录密码
     * 请求方式:post
     * 请求参数"
     *  '是否有原密码':is_password
     *  新密码：newPassword
     *  确认密码: rePassword
     *  原密码：oldPassword (可选)
     */
    public function changePassword(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        if (empty($_POST['newPassword'])) {
            apiResponse('0', '请填写新密码');
        }
        if(strlen($_POST['newPassword'])<6){
            apiResponse('0','密码至少为6位');
        }
        if ($_POST['newPassword'] !== $_POST['rePassword']) {
            apiResponse('0', '两次密码不一致');
        }
        D('User', 'Logic')->changePassword(I('post.'), $user_id);

    }

    /**
     * 设置支付密码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 密码 newPayPwd
     * 确认密码 rePayPwd
     */
    public function setPayPwd(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        if (empty($_POST['newPayPwd'])) {
            apiResponse('0', '请填写新支付密码');
        }
        if(strlen($_POST['newPayPwd'])<6){
            apiResponse('0','密码至少为6位');
        }
        if ($_POST['newPayPwd'] !== $_POST['rePayPwd']) {
            apiResponse('0', '两次密码不一致');
        }
        //此处可以加入正则验证密码
        D('User', 'Logic')->setPayPwd(I('post.'), $user_id);
    }


    /**
     * 修改支付密码函数
     * 请求方式：post
     * 请求参数:
     * 原密码 oldPayPwd (可选)
     * 新密码 newPayPwd
     * 确认密码 rePayPwd
     */
    public function rePayPwd(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        if (empty($_POST['newPayPwd'])) {
            apiResponse('0', '请填写新支付密码');
        }
        if(strlen($_POST['newPayPwd'])<6){
            apiResponse('0','密码至少为6位');
        }
        if ($_POST['newPayPwd'] !== $_POST['rePayPwd']) {
            apiResponse('0', '两次密码不一致');
        }
        //此处可以加入正则验证密码
        D('User', 'Logic')->rePayPwd(I('post.'), $user_id);

    }
    /**
     * 重置支付密码(根据短信验证码)
     * 参数传递方式：post
     * 传递参数:
     * 手机号：phone
     * 短信验证码：verify
     * 新密码: newPayPwd
     * 确认密码 : rePayPwd
     */
    public function resetPayPwd(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['phone'])){
            apiResponse('0','请输入绑定手机号');
        }
        if(empty($_POST['verify'])){
            apiResponse('0','请输入短信验证码');
        }
        if(empty($_POST['newPayPwd'])){
            apiResponse('0','请输入新的支付密码');
        }
        if(strlen($_POST['newPayPwd'])<6){
            apiResponse('0','支付密码至少为6位');
        }
        if($_POST['rePayPwd'] !== $_POST['newPayPwd']){
            apiResponse('0','两次密码不一致');
        }
        D('User','Logic')->resetPayPwd(I('post.'),$user_id);
    }
    /**
     * 更换绑定手机
     * 请求方式: post
     * 需要传递参数:
     * 新手机号:newPhone
     * 验证码 : verify
     */
    public function changePhone(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['newPhone'])){
            apiResponse('0','手机号不能为空');
        }
        if(empty($_POST['verify'])){
            apiResponse('0','请输入验证码');
        }
        D('User','Logic')->changePhone(I('post.'),$user_id);
    }
    /**
     * 我的足迹
     * 请求方式 ： post
     * 请求参数:
     *     分页参数 p
     *     类型： type  1商品(默认) 2店铺
     */
    public function myfooter(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        if (empty($_POST['type'])) {
            //默认商品
            $_POST['type'] = 1;
        }
        D('User','Logic')->myfooter(I('post.'),$user_id);
    }
    /**
     * 删除足迹
     */
    public function delFooter(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['footer_ids'])){
            apiResponse('0','请选择要删除的足迹');
        }
        D('User','Logic')->delFooter(I('post.footer_ids'));
    }
    /**
     * 获取我的评价
     * 参数传递方式：post
     * 传递参数:
     * 分页参数 ：p
     */
    public function myCommentList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        $_POST['user_id'] = $user_id;
        $list = D('Comment','Logic')->commentList(I('post.'));
        if($list){
            apiResponse('1','获取成功',$list['list'],$list['count']);
        }else{
            apiResponse('0',"暂无评价");
        }
    }
    /**
     * 获取我的购物券列表
     * 请求方式：post
     * 请求参数：

     */
    public function vouchersList()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Vouchers', 'Logic')->vouchersList(I('post.'), $user_id);
    }
    /**
     * 获取购物券明细列表
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function vouchersLog()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        D('Vouchers', 'Logic')->vouchersLog(I('post.'), $user_id);
    }
    /**
     * 我的积分
     * 请求方式：post
     */
    public function myIntegral(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->myIntegral(I('post.'),$user_id);
    }
    /**
     * 积分明细
     */
    public function integralLog(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('User','Logic')->integralLog(I('post.'),$user_id);
    }
    /**
     * 增加积分
     *  reason
     *  get_integral
     */
    public function addIntegral(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['reason'])){
            apiResponse('0','请填写获得原因');
        }
        if(empty($_POST['get_integral'])){
            apiResponse('0','请填写成长值');
        }
        D('User','Logic')->addIntegral(I('post.'),$user_id);
    }
    /**
     * 我的优惠券
     * 请求方式:post
     * 传递参数:
     */
    public function myTicket(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->myTicket(I('post.'),$user_id);
    }

    /**
     * 商家推荐
     * 请求方式:post
     * 传递参数:
     *     商户名称：name
     *     经营范围:range_id
     *     联系人:link_man
     *     职位:job
     *     联系电话:link_phone
     *     天猫网店：tmail_url
     *     京东网店：jd_url
     *     其他网址：other_url
     *     产品描述：product_desc
     *     产品图片：product_pic
     *     营业执照：business_license
     *     其他证件照：other_license
     */
    public function merchantRefer(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['name']) || empty($_FILES['business_license']['name']) || empty($_POST['link_man']) || empty($_POST['link_phone']) || empty($_POST['job'])){
            apiResponse('0','商户信息不完整');
        }
        if(!isMobile($_POST['link_phone'])){
            apiResponse('0','联系人手机号格式不正确');
        }
        if(empty($_POST['tmail_url']) && empty($_POST['jd_url']) && empty($_POST['other_url'])){
            apiResponse('0','链接至少填一个');
        }
        if(empty($_POST['product_desc']) || empty($_FILES['product_pic']['name']) || empty($_POST['range_id'])){
            apiResponse('0','产品信息不完整');
        }
        D('User','Logic')->merchantRefer(I('post.'),$user_id);
    }

    /**
     * 获取经营范围
     * 请求方式：post
     */
    public function getRange(){
        $res = D('User','Logic')->getRange();
        if($res){
            apiResponse('1','获取成功',$res);
        }else{
            apiResponse('0','获取失败');
        }
    }

    /**
     * 获取推荐商家列表
     * 请求方式:post
     * 请求参数:无
     */
    public function referList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->referList($user_id);
    }
    /**
     * 获取推荐商家信息
     */
    public function referInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['refer_id'])){
            apiResponse('0','请输入推荐商家id');
        }
        D('User','Logic')->referInfo(I('post.'));
    }
    /**
     * 会员成长
     * 请求参数:wu
     */
    public function userDevelop(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->userDevelop($user_id);
    }
    /**
     * 成长明细
     */
    public function userDevelopLog(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->userDevelopLog(I('post.'),$user_id);
    }
    /**
     * 增加成长值
     *  reason
     *  get_point
     */
    public function addPoint(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['reason'])){
            apiResponse('0','请填写获得原因');
        }
        if(empty($_POST['get_point'])){
            apiResponse('0','请填写成长值');
        }
        $res = D('User','Logic')->addPoint(I('post.'),$user_id);
        if($res){
            apiResponse('1','成长值添加成功');
        }else{
            apiResponse('0','成长值添加失败');
        }
    }
    /**
     * 会员等级
     * 请求参数:wu
     */
    public function userRank(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->userRank($user_id);
    }
    /**
     * 获取注册码
     */
    public function getSignCode(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->getSignCode($user_id);
    }
    /**
     * 分享好友
     */
    public function shareFriend(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->shareFriend($user_id);
    }
    /**
     * 分享回调
     * 请求参数: 分享类型  type  1微信 2微博 3qq
     *          分享内容  content
     *          分享id id_val
     */
    public function shareBack(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['type'])){
            apiResponse('0','请输入分享平台类型');
        }
        if(empty($_POST['id_val'])){
            apiResponse('0','请输入分享内容id');
        }
        if(empty($_POST['url'])){
            apiResponse('0','请输入分享链接');
        }
        if(empty($_POST['share_type'])){
            apiResponse('0','请输入分享内容类型');
        }
        D('User','Logic')->shareBack(I('post.'),$user_id);
    }
    /**
     * 我的分享
     *    请求参数：分页参数ｐ
     */
    public function myShare(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('User','Logic')->myShare(I('post.'),$user_id);
    }
    /**
     * 我的推荐
     */
    public function myRecommend(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('User','Logic')->myRecommend(I('post.'),$user_id);
    }
    /**
     * 工作成绩
     * 请求参数: p 分页参数
     *          城市名称/城市id
     *          type
     */
    public function gradeRank(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        if(empty($_POST['city_id']) && empty($_POST['city_name'])){
            apiResponse('0','请输入城市名称或者ID');
        }
        D('User','Logic')->gradeRank(I('post.'),$user_id);

    }

    /**
     * 个人认证
     */
    public function personalAuth(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->personalAuth(I('post.'),$user_id);
    }

    /**
     * 获取个人认证详情
     */
    public function personalAuthInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->personalAuthInfo(I('post.'),$user_id);
    }

    /**
     * 企业认证
     */
    public function compAuth(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('User','Logic')->compAuth(I('post.'),$user_id);
    }

//    /**
//     * 企业认证详情
//     */
//    public function compAuthInfo(){
//        $user_id = $this->checkLogin();
//        $this->returnNotLoginMsg($user_id);
//        D('User','Logic')->compAuthInfo(I('post.'),$user_id);
//    }
}
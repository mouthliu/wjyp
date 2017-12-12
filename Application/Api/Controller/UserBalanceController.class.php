<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 余额模块控制器
 * Class UserBalanceController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class UserBalanceController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 余额首页
     */
    public function balanceIndex(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        $balance = M('User')->where("id = {$user_id}")->getField('balance');
        $money = $balance ? $balance : '0';
        apiResponse('1','获取成功',array('balance'=>$money));
    }
    /**
     * 线上充值
     * 参数 ：金额 money
     * pay_type 1微信 2支付宝
     * note 备注(可选)
     */
    public function upMoney(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['money'])){
            apiResponse('0','请输入充值金额');
        }
        if(empty($_POST['pay_type'])){
            apiResponse('0','请输入付款方式');
        }
       D('UserBalance','Logic')->upMoney(I('post.'),$user_id);
    }
    /**
     * 线下充值
     * 时间戳
     */
    public function underMoney(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['bank_card_id'])){
            apiResponse('0','请输入银行卡id');
        }
        if(empty($_POST['act_time'])){
            apiResponse('0','请输入汇款时间');
        }
        if(empty($_POST['money'])){
            apiResponse('0','请输入汇款金额');
        }
        if(empty($_POST['name'])){
            apiResponse('0','请输入汇款人');
        }
        if(empty($_FILES['pic']['name'])){
            apiResponse('0','请输入汇款凭证');
        }

        if(empty($_POST['pay_password'])){
            apiResponse('0','请输入安全密码');
        }
        D('UserBalance','Logic')->underMoney(I('post.'),$user_id);
    }
    /**
     * 银行卡类型获取
     */
    public function getBankType(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        $list = M('BankType')->field("id AS bank_type_id,bank_name,bank_pic")->order("sort DESC")->select();
        $count = M('BankType')->count();
        if(!$list){
            apiResponse('0','银行卡类型获取失败',$count);
        }
        foreach($list as $k=>$v){
            $list[$k]['bank_pic'] = C('API_URL').$v['bank_pic'];
        }
        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 添加银行卡
     */
    public function addBank(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['name'])){
            apiResponse('0','请输入持卡人姓名');
        }
        if(empty($_POST['bank_type_id'])){
            apiResponse('0','请输入银行卡类型');
        }
        if(empty($_POST['open_bank'])){
            apiResponse('0','请输入开户行');
        }
        if(empty($_POST['card_number'])){
            apiResponse('0','请输入银行卡号');
        }
        if(empty($_POST['phone'])){
            apiResponse('0','请输入银行卡预留手机号');
        }
        D('UserBalance','Logic')->addBank(I('post.'),$user_id);
    }
    /**
     * 获取银行卡列表
     */
    public function bankList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        $where['b.id_val'] = $user_id;
        $where['b.type'] = 1;
        $list = M('Bank')->alias('b')
            ->field("b.id as bank_card_id,b.card_number AS bank_card_code,t.bank_name,t.bank_pic,b.open_bank,b.name")
            ->join(C('DB_PREFIX').'bank_type t ON t.id=b.bank_type_id')
            ->where($where)
            ->select();
        $count = M('Bank')->alias('b')->where($where)->count();
        if(!$list){
            apiResponse('1','暂无银行卡');
        }
        foreach($list as $k=>$v){
             $list[$k]['bank_pic'] = C('API_URL').$v['bank_pic'];
        }
        apiResponse('1','获取银行卡成功',$list,$count);
    }
    /**
     * 删除银行卡
     * 银行卡id bank_card_id
     */
    public function delBank(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        $mod = D('Bank');
        $where['id'] = $_POST['bank_card_id'];
        $where['id_val'] = $user_id;
        $where['type'] = 1;
        $res = $mod->where($where)->delete();
        if(!$res){
            apiResponse('0','删除失败');
        }
        apiResponse('1','删除银行卡成功');
    }

    /**
     * 提现首页
     */
    public function cashIndex(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        //获取我的余额
        $balance = M('User')->where("id = {$user_id}")->getField('balance');
        $data['balance'] = $balance ? $balance : '0';
        //获取手续费率
        $rate = '5';
        $data['rate'] = $rate;
        //获取到帐周期
        $delay_time = '工作日T+1';
        $data['delay_time'] = $delay_time;
        apiResponse('1','获取成功',$data);
    }
    /**
     * 提现操作
     * 金额 money
     * 手续费率 rate
     * 银行卡id bank_card_id
     * 支付密码 pay_password
     */
    public function getCash(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['pay_password'])){
            apiResponse('0','请输入支付密码');
        }
        if(empty($_POST['money'])){
            apiResponse('0','请输入提现金额');
        }elseif($_POST['money'] > 50000){
            apiResponse('0','单笔提现最该额度为五万元整');
        }elseif($_POST['money']%100 != 0){
            apiResponse('0','单笔提现金额为100的整数');
        }
        if(empty($_POST['rate'])){
            apiResponse('0','请输入手续费率');
        }
        if(empty($_POST['bank_card_id'])){
            apiResponse('0','请选择银行卡');
        }
        D('UserBalance','Logic')->getCash(I('post.'),$user_id);
    }
    /**
     * 转账
     * money 金额
     * code 手机号或者会员ID
     * real_name 对方姓名
     * pay_password
     */
    public function changeMoney(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty(intval($_POST['code']))){
            apiResponse('0','请输入手机号或者会员ID');
        }
        if(empty($_POST['real_name'])){
            apiResponse('0','对方未实名认证或账号不存在');
        }
        if(empty($_POST['money'])){
            apiResponse('0','请输入提现金额');
        }
        if(empty($_POST['pay_password'])){
            apiResponse('0','请输入支付密码');
        }
        D('UserBalance','Logic')->changeMoney(I('post.'),$user_id);
    }

    /**
     * 获取余额明细列表
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function balanceLog()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        D('UserBalance', 'Logic')->balanceLog(I('post.'), $user_id);
    }

    /**
     * 设置余额记录表
     * 请求方式：post
     * 请求参数:
     * money: 金额
     * 操作类型: act_type  1=>获得 2=>兑换月 3=>消费 4=>退款
     * 操作原因: reason
     */
    public function addLog(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['money'])){
            apiResponse('0','请输入金额');
        }
        if(empty($_POST['act_type'])){
            apiResponse('0','请输入操作类型');
        }
        if(empty($_POST['reason'])){
            apiResponse('0','请输入原因');
        }
        $res = D('UserBalance','Logic')->addLog(I('post.'),$user_id);
        if($res){
            apiResponse('1','记录成功');
        }else{
            apiResponse('0','记录失败');
        }
    }
    /**
     * 根据id 或者手机号获取用户信息
     */
    public function getUserName(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['code']) && is_numeric($_POST['code'])){
            apiResponse('0','请输入会员id或者手机号');
        }
        //判断手机号格式
        if(isMobile(intval($_POST['code']))){
            //通过手机号查询信息
            $real_name = M('User')->where("phone = '{$_POST['code']}' AND auth_status = 2 AND status=1")->getField('real_name');
        }else{
            $real_name = M('User')->where("id = '{$_POST['code']}' AND auth_status = 2 AND status=1")->getField('real_name');
        }
        if($real_name){
            apiResponse('1','获取成功',array('real_name'=>$real_name));
        }else{
            apiResponse('0','获取失败，对方不存在或可能未进行实名认证。',array('real_name'=>''));
        }
    }
    /**
     * 根据线下充值id获取详情
     */
    public function getUnderInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['act_id'])){
            apiResponse('0','请输入线下充值id');
        }
        $mod = M("UserUnderMoney");
        $info = $mod->alias('m')
            ->field("m.id,m.status,m.bank_card_id,m.act_time,m.money,m.name,m.pic,m.desc,b.card_number,b.open_bank,b.name AS card_name")
            ->join(C('DB_PREFIX').'bank b ON b.id=m.bank_card_id')
            ->where("m.id = {$_POST['act_id']}")
            ->find();
        if(!$info){
            apiResponse('0','获取线下充值详情失败');
        }
        $info['pic'] = D('File')->getOneFilePath($info['pic']);
        apiResponse('1','获取成功',$info);

    }
}
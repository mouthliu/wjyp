<?php
namespace Api\Logic;

/**
 * Class UserBalanceLogic
 * @package Api\Logic
 * 逻辑层  主题街模块
 *
 */
class UserBalanceLogic extends BaseLogic{
    /**
     * 线上充值
     * @param array $request
     * @param int $user_id
     */
    public function upMoney($request = array(),$user_id = 0){
        $mod = D('UserMoney');
        //判断充值金额
        if(floatval($request['money']) > 999999.99){
            apiResponse('0','金额请在100万内的合法数字');
        }
        //判断充值金额
        $data['money'] = $request['money'];
        $data['pay_type'] = $request['pay_type'];
        $data['create_time'] = time();
        $data['user_id'] = $user_id;
        $data['note'] = $request['note'] ? $request['note'] : '线上充值';
        $id = $mod->add($data);
        if(!$id){
            apiResponse('0','充值失败');
        }
        $message = '充值审核中,本次充值'.$request['money'].'元';
        apiResponse('1',$message,array('money'=>$request['money'],'pay_type'=>$request['pay_type'],'order_id'=>$id));
    }

    /**
     * 线下充值
     * @param array $request
     * @param int $user_id
     */
    public function underMoney($request = array(),$user_id = 0){
        //判断支付密码
        $pay_pwd = M('User')->where("id = {$user_id}")->getField('pay_password');
        if(md5($request['pay_password']) != $pay_pwd){
            apiResponse('0','支付密码错误');
        }
        //根据银行卡id 获取到银行卡号
        $bank_card_code =  M('Bank')->where("id_val = {$user_id} AND id={$request['bank_card_id']}")->getField('card_number');
        if(!$bank_card_code){
            apiResponse('0','银行卡信息有误');
        }
        $data['bank_card_code'] = $bank_card_code;
        $data['bank_card_id'] = $request['bank_card_id'];
        $data['act_time'] = $request['act_time'];
        if(floatval($request['money']) > 999999.99){
            apiResponse('0','金额请在100万内的合法数字');
        }
        $data['money'] = floatval($request['money']);
        $data['name'] = $request['name'];
        $data['desc'] = $request['desc'] ? $request['desc'] : '';
        $data['create_time'] = time();
        $data['user_id'] = $user_id;
        //汇款凭证处理
        if(!empty($_FILES['pic']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'UserMoney')));
            foreach ($res as $value) {
                $data['pic'] = $value['id'];
            }
        }
        $res = D('UserUnderMoney')->add($data);
        if($res){
            apiResponse('1','提交成功,正在受理中');
        }else {
            apiResponse('0', '提交失败');
        }
    }

    /**
     * 添加银行卡
     * @param array $request
     * @param int $user_id
     */
    public function addBank($request = array(),$user_id = 0){
        $mod = D('Bank');
        $data['id_val'] = $user_id;
        $data['type'] = 1;//用户类型
        $data['bank_type_id'] = $request['bank_type_id'];
        $data['name'] = $request['name'];
        $data['card_number'] = $request['card_number'];
        $data['phone'] = $request['phone'];
        $data['open_bank'] = $request['open_bank'];
        $data['create_time'] = time();
        $id = $mod->add($data);
        if(!$id){
            apiResponse('0','添加银行卡失败');
        }
        apiResponse('1','添加成功',array('bank_card_id'=>$id));
    }

    /**
     * 提现操作
     * @param array $request
     * @param int $user_id
     */
    public function getCash($request = array(),$user_id = 0){
        //        判断提现金额
        $balance = M('User')->where("id = {$user_id}")->getField('balance');
        if($balance < $request['money']){
            apiResponse('0','提现金额过大,余额不足');
        }

        //判断支付密码
        $pay_pwd = M('User')->where("id = {$user_id}")->getField('pay_password');
        if(md5($request['pay_password']) != $pay_pwd){
            apiResponse('0','支付密码错误');
        }
        $data['money'] = floatval($request['money']);
        $data['rate'] = $request['rate'];
        $rate_money = (($request['rate']/100)*$request['money']);
        $true_rate_money = $rate_money > 100 ? 100 : $rate_money;
        $data['true_money'] = floatval($data['money'] - $true_rate_money);
        $data['bank_card_id'] = $request['bank_card_id'];
        //根据银行卡id 获取到卡号
        $bank_card_code = M('Bank')->where("id = {$request['bank_card_id']}")->getField('card_number');
        $data['bank_card_code'] = $bank_card_code;
        $data['id_val'] = $user_id;
        $data['type'] = 1;//身份类型 1用户 2商家
        $data['create_time'] = time();
        $res = D('UserCash')->add($data);
        if(!$res){
            apiResponse('0','提现失败');
        }
        apiResponse('1','提现申请成功',array('true_money'=>$data['true_money']));
    }

    /**
     * 转账
     */
    public function changeMoney($request = array(),$user_id = 0){
        //  判断金额
        $balance = M('User')->where("id = {$user_id}")->getField('balance');
        if($balance < $request['money']){
            apiResponse('0','转帐金额过大,余额不足');
        }
        //判断支付密码
        $pay_pwd = M('User')->where("id = {$user_id}")->getField('pay_password');
        if(md5($request['pay_password']) != $pay_pwd){
            apiResponse('0','支付密码错误');
        }
        //判断对方信息
        if(isMobile($request['code'])){
            $where['phone'] = $request['code'];
        }else{
            $where['id'] = $request['code'];
        }
        $my_phone = M('User')->where("id = {$user_id}")->getField('phone');
        if($request['code'] == $my_phone){
            apiResponse('0','不能转给自己');
        }else if($request['code'] == $user_id){
            apiResponse('0','不能转给自己');
        }
        $where['real_name'] = $request['real_name'];
        $where['status'] = array('lt',9);
//        $where['status'] = 1; //会员状态正常
//        $where['auth_status'] = 2;//已认证
        $info = M('User')->field("id,status,auth_status")->where($where)->find();
        if(!$info){
            apiResponse('0','对方账户不存在');
        }
        //判断会员状态
        if($info['status'] != '1'){
            apiResponse('0','对方账户暂不可用');
        }
        //判断会员认证状态
        if($info['auth_status'] != '2'){
            apiResponse('0','对方尚未实名认证');
        }
        $data['code'] = $request['code'];
        $data['real_name'] = $request['real_name'];
        $data['money'] = $request['money'];
        $data['user_id'] = $user_id;
        $data['create_time'] = time();
        $id = D('ChangeMoney')->add($data);
        if(!$id){
            apiResponse('0','转账失败');
        }
        apiResponse('1','转账申请中，请耐心等待');
    }
    /**
     * 余额明细列表
     * @param array $request
     * @param int $user_id
     * 操作类型 1线上充值 2线下充值 3消费 4提现 5退款 6转账出 7转账收入 8积分兑换余额
     */
    public function balanceLog($request = array(),$user_id = 0){
        $mod = M('BalanceLog');
        $list = $mod->alias('l')
            ->field('l.id AS log_id,l.act_type,l.reason,l.create_time,l.money,l.act_id')
            ->where("l.user_id = {$user_id}")
            ->order('l.create_time DESC')
            ->page($request['p'],20)
            ->select();
        $count = $mod->where("user_id = {$user_id}")->count();
        if(!$list){
            apiResponse('0','暂无明细');
        }
        //根据月份分组
        foreach($list as $k=>$v){
            $time = date('Y-m',$v['create_time']);
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $data_list[$time][] = $v;
        }
        $i = 0;
        foreach($data_list as $key=>$value){
            $res[$i]['time'] = $key;
            $res[$i]['list'] = $value;
            $i++;
        }
        apiResponse('1','获取成功',$res,$count);
    }

    /**
     * 添加记录
     * @param array $request
     * @param int $user_id
     * 操作类型 1线上充值 2线下充值 3消费 4提现 5退款 6转账出 7转账入 8积分兑换余额
     */
    public function addLog($request = array(),$user_id = 0){
        $mod                 = M('BalanceLog');
        $data['act_type']    = $request['act_type'];
        $data['act_id']    = $request['act_id'];//对应的操作id
        $data['money'] = $request['money'];
        $data['reason']      = $request['reason'];
        $data['user_id']     = $user_id;
        $data['create_time'] = time();
        $res = $mod->add($data);
        if($res){
            return true;
        }else{
            return false;
        }
    }
}
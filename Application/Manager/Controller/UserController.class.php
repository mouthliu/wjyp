<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class UserController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//

    public function getUpdateRelation(){
        $param['where']['id'] = $_GET['id'];
        //获取数据
        $row = D('User')->findRow($param);
        //判断是否获取成功
        if(!$row){
            $this->error('未查到此记录！');
        }else{
            $where['user_id'] = $row['id'];
            $where['status'] = array('neq',9);
            $where['end_time'] = array('gt',time());
            $where1['_complex'] = $where;
            $where1['type'] = 1;
            $where2['_complex'] = $where;
            $where2['type'] = 2;
            $where3['_complex'] = $where;
            $where3['type'] = 3;
            $discount_money = M('vouchers')->where($where1)->sum('money');
            $discount_use_money = M('vouchers')->where($where1)->sum('use_money');
            $rows['discount'] = $discount_money-$discount_use_money;

            $blue_discount_money = M('vouchers')->where($where3)->sum('money');
            $blue_discount_use_money = M('vouchers')->where($where3)->sum('use_money');
            $rows['blue_discount'] = $blue_discount_money-$blue_discount_use_money;

            $yellow_discount_money = M('vouchers')->where($where2)->sum('money');
            $yellow_discount_use_money = M('vouchers')->where($where2)->sum('use_money');
            $rows['yellow_discount'] = $yellow_discount_money-$yellow_discount_use_money;

            $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
            $this->assign('city',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['province_id'],'region_type'=>2,'is_show'=>1))->select());
            $this->assign('area',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['city_id'],'region_type'=>3,'is_show'=>1))->select());
            $this->assign('street',M('street')->field('street_id,street_name')->where(array('parent_id'=>$row['area_id'],'status'=>1))->select());
            $this->assign('row',$row);
            $this->assign('rows',$rows);
        }
    }


    function getAddRelation(){
        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
    }

    /**
     * 获取地区
     */
    public function ajaxGetRegion(){
        if(empty($_POST['id'])){
            $this->ajaxReturn(array(),'JSON');
        }
        $where['parent_id'] = $_POST['id'];
        $result = M('Region')->where($where)->field('id,region_name')->select();
        $this->ajaxReturn($result,'JSON');
    }
    /**
     * 获取街道
     */
    public function ajaxGetStreet(){
       
        $where['parent_id'] = $_POST['id'];

        $where['status'] = 1;
        $result = M('Street')->where($where)->field('street_id,street_name')->select();
        $this->ajaxReturn($result,'JSON');
    }
    function setPwd(){
        $request = I('request.');
        if(empty($request['id'])){
            $this->error('参数错误');
            return false;
        }
        if(empty($request['newPassword'])){
            $this->error('密码不能为空');
            return false;
        }
        $data['password'] = md5($request['newPassword']);
        $res = D("User")->where("id={$request['id']}")->save($data);
        if($res){
            $this->success("修改登录密码成功");
            return true;
        }else{
            $this->error('修改登录密码失败');
            return false;
        }
    }

    /**
     * @return bool
     * 修改支付密码
     */
    function setPayPwd(){
        $request = I('request.');
        if(empty($request['id'])){
            $this->error('参数错误');
            return false;
        }
        if(empty($request['newPayPassword'])){
            $this->error('支付密码不能为空');
            return false;
        }
        $data['pay_password'] = md5($request['newPayPassword']);
        $res = D("User")->where("id={$request['id']}")->save($data);
        if($res){
            $this->success("修改支付密码成功");
            return true;
        }else{
            $this->error('修改支付密码失败');
            return false;
        }
    }


    /**
     * 修改成长值
     */
    public function setLeve(){
        if(IS_POST){
            $add['user_id'] = $_POST['id'];
            if($_POST['type'] == 1){
                $add['get_point'] =$_POST['get_point'];
            }else{
                $add['get_point'] ='-'.$_POST['get_point'];
            }
            $save['grow_point'] = $add['get_point'];
            if(empty($_POST['reason'])){
                $this->error('请填写修改原因');die;
            }
            $add['reason'] = $_POST['reason'];
            $add['create_time'] = time();
            $user = M('user');
            $level_log = M('level_log');
            $user->startTrans();
            $data = $user->where(array('id'=>$_POST['id']))->setInc('grow_point',$add['get_point']);
            $res = $level_log->add($add);
            if($data && $res){
                $user->commit();
                $this->success('成长值修改成功');
            }else{
                $user->rollback();
                $this->error('成长值修改失败');
            }
        }
    }

    /**
     * 修改会员积分
     */
    public function setIntegra(){
        if(IS_POST){
            if($_POST['type'] == 1){
                $add['use_integral'] =$_POST['use_integral'];
            }else{
                $add['use_integral'] ='-'.$_POST['use_integral'];
            }
            if(empty($_POST['reason'])){
                $this->error('请填写修改原因');die;
            }
            $add['reason'] = $_POST['reason'];
            $add['act_type'] = 7;
            $save['update_time'] = time();
            $add['user_id'] = $_POST['id'];
            $add['create_time'] = time();
            $user = M('user');
            $integral_log = M('integral_log');
            $user->startTrans();
            $data = $user->where(array('id'=>$_POST['id']))->setInc('integral',$add['use_integral']);
            $res = $integral_log->add($add);
            if($data && $res){
                $user->commit();
                $this->success('会员积分修改成功');
            }else{
                $user->rollback();
                $this->error('会员积分修改失败');
            }
        }
    }

    /**
     * 修改账户余额
     */
    public function setBalance(){
        if(IS_POST){
            $save['update_time'] = time();
            if($_POST['type'] == 1){
                $add['money'] =$_POST['balance_log'];
            }else{
                $add['money'] ='-'.$_POST['balance_log'];
            }
            $add['act_type'] = 9;
            $add['user_id'] = $_POST['id'];
            if(empty($_POST['reason'])){
                $this->error('请填写修改原因');die;
            }
            $add['reason'] = $_POST['reason'];
            $add['act_type'] = 9;
            $add['create_time'] = time();
            $user = M('user');
            $balance = M('balance_log');
            $user->startTrans();
            $data = $user->where(array('id'=>$_POST['id']))->setInc('balance',$add['money']);
            $res = $balance->add($add);
            if($data && $res){
                $user->commit();
                $this->success('账户余额修改成功');
            }else{
                $user->rollback();
                $this->error('账户余额修改失败');
            }
        }
    }

    /**
     * 成长明细
     * @return bool
     */
    function levelLog(){
        $result = D('User','Logic')->levelLog(I('request.'));
        if ($result) {
            $this->assign('point',getName('User','grow_point',I('request.user_id')));
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error(D('User','Logic')->getLogicError());
        }
        $this->display('User/record');
    }
    /**
     * 积分明细
     * @return bool
     */
    function integralLog(){
        $result = D('User','Logic')->integralLog(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error(D('User','Logic')->getLogicError());
        }
        $this->display('User/integralRecord');
    }
    /**
     * 余额明细
     * @return bool
     */
    function balanceLog(){
        $result = D('User','Logic')->balanceLog(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error(D('User','Logic')->getLogicError());
        }
        $this->display('User/balanceRecord');
    }
    //针对某个字段快速修改
    function setField(){
        $request = I('request.');
        if(empty($request['model'])){
            $this->error('参数错误');
            return false;
        }
        if(empty($request['id'])){
            $this->error('参数错误');
            return false;
        }
        $model = D($request['model']);
        if(!$model->create($request)){
            exit($model->getError());
        }else{
            $res = $model->save($request);
            if($res){
                $this->success("修改成功");
                return true;
            }else{
                $this->error('参数错误');
                return false;
            }
        }
    }

    /**
     *修改推荐人手机号
     */
   function setParent(){
        if(IS_POST){
            if(empty($_POST['phone'])){
                $this->error('请输入手机号');die;
            }
            $parent_id = M('user')->where(array('phone'=>$_POST['phone']))->getField('id');
            if(empty($parent_id)){
                $this->error('手机号输入不正确,请检查');die;
            }else{
                $save['update_time'] = time();
                $save['parent_id'] = $parent_id;
                $data = M('user')->where(array('id'=>$_POST['id']))->save($save);
                if($data){
                    $this->success('修改成功');
                }
            }
        }
   }

    /**
     *修改隐藏推荐人手机号
     */
    function setParents(){
        if(IS_POST){
            if(empty($_POST['phone'])){
                $this->error('请输入手机号');die;
            }
            $hidden_parent_id = M('user')->where(array('phone'=>$_POST['phone']))->getField('id');
            if(empty($parent_id)){
                $this->error('手机号输入不正确,请检查');die;
            }else{
                $save['update_time'] = time();
                $save['parent_id'] = $hidden_parent_id;
                $data = M('user')->where(array('id'=>$_POST['id']))->save($save);
                if($data){
                    $this->success('修改成功');
                }
            }
        }
    }
    /**
     * 修改联盟商家
     */
    public function setMerchant(){
        if(IS_POST){
            if(empty($_POST['merchant_name'])){
                $this->error('请输入联盟商家名称');die;
            }
            $merchant_id = M('merchant')->where(array('merchant_name'=>$_POST['merchant_name']))->getField('id');
            if(empty($merchant_id)){
                $this->error('联盟商家名称不正确,请检查');die;
            }else{
                $save['update_time'] = time();
                $save['parent_alliance_merchant_id'] = $merchant_id;
                $data = M('user')->where(array('id'=>$_POST['id']))->save($save);
                if($data){
                    $this->success('修改成功');
                }
            }
        }
    }

    /**
     * 修改代金券
     */
    public function setDiscount(){
        if(IS_POST){
            if(empty($_POST['reason'])){

                $this->error('请填写修改原因');die;
            }
            if($_POST['type'] == 1){
                //增加
                $add['user_id'] = $_POST['id'];
                $add['type'] = $_POST['discount_type'];
                $add['money'] = $_POST['money'];
                $add['create_time'] = time();
                $add['end_time'] = strtotime($_POST['end_time']);
                $add['status']= 1;
                $vouchers_id = M('vouchers')->add($add);
                if($vouchers_id){
                    $this->success('添加成功');return true;
                }
            }else{
                //减少
                $where['user_id'] = $_POST['id'];
                $where['status'] = array('neq',9);
                $where['type'] = $_POST['discount_type'];
                $where['end_time'] = array('gt',time());
                $money = $_POST['money'];
                $vouchers = M('vouchers')->where($where)->order('end_time asc')->select();
                foreach($vouchers as $k => $v){
                    if($money>0){
                        $last_money = $v['money']-$v['use_money'];
                        if($money>=$last_money){
                            M('vouchers')->where(array('id'=>$v['id']))->data(array('use_money'=>$v['money']))->save();
                            $des_money = $last_money;//本次操作减少的钱;
                        }else{
                            M('vouchers')->where(array('id'=>$v['id']))->setInc('use_money',$money);
                            $des_money = $money;
                        }
                        $money = $money-$last_money;
                       //加明细
                        $add['user_id'] = $_POST['id'];
                        $add['vouchers_id'] = $v['id'];
                        $add['act_type']=5;
                        $add['reason'] = $_POST['reason'];
                        $add['create_time'] = time();
                        $add['money'] = $des_money;
                        M('vouchers_log')->add($add);
                        $this->success('修改成功!'); return true;
                    }else{
                        break;
                    }
                }
            }
        }
    }

    /**
     * 代金券明细
     */
    public function vouchersTotal(){
        $result = D('User','Logic')->vouchersTotal(I('request.'));
        $money['user_id'] = $_GET['user_id'];
        $money['money'] = M('vouchers')->where(array('user_id'=>$_GET['user_id']))->sum('money');
        $money['use_money'] = M('vouchers')->where(array('user_id'=>$_GET['user_id']))->sum('use_money');
        $money['end_money'] = M('vouchers')->where(array('user_id'=>$_GET['user_id'],'status'=>9))->sum('money');
        if ($result) {
            $this->assign('money',$money);
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error(D('User','Logic')->getLogicError());
        }
        $this->display('User/vouchersTotal');
    }

    /**
     * 新增优惠券
     */
    public function addVouchers(){
        if(IS_POST){
            $_POST['status'] = 1;
            $_POST['end_time'] = strtotime($_POST['end_time']);
            $data = D('Vouchers')->create($_POST);
            if($data){
                D('Vouchers')->data($data)->add();
                $this->success('代金券添加成功');
            }else{
                $this->error(D('Vouchers')->getError());
            }
        }
    }

    /**
     * 代金券详情
     */
    public function vouchersDetails(){
        $list = M('vouchers_log')->where(array('vouchers_id'=>$_GET['id']))->select();
        $this->assign('list',$list);
        $this->display('vouchersDetails');
    }
}

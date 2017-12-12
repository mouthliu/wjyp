<?php
namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class ChangeMoneyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }

        $param['where']['goods.status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('ChangeMoney')->getList($param);

        //dump(D('Goods')->getLastSql()) ;
       // dump($result);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['goods.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['goods.status'] = array('lt',9);
        $row = D('ChangeMoney')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['brand_logo'] = api('System/getFiles',array($row['brand_logo']));
//        dump($row);
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('ChangeMoney')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }
    //线下充值逻辑层
    function underMoney($request = array()){
//        if(!empty($request['alias_sn'])) {
//            $param['where']['goods.goods_sn'] = $request['goods_sn'];
//        }
        $param['where']['alias.status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('UserUnderMoney')->getList($param);
        return $result;
    }
    /**
     * @param array $request
     * @return boolean
     * 更新前执行
     */
    public function beforeUpdate($request = array()) {
        //判断是否是拒绝
        if($request['status'] == '2'){
            //判断理由
            if(!$request['refuse_desc']){
                $this->setLogicError('请填写拒绝认证理由');return false;
            }
        }elseif($request['status'] == '1'){
            //执行余额增加 对方减少
            //获取到金额
            $money = $request['money'];
            $user_id = $request['user_id'];
            //根据code获取到对方信息
            if(isMobile($request['code'])){
                $toUser = M('User')->where("phone = '{$request['code']}'")->getField('id');
            }else{
                $toUser = $request['code'];
            }
            $res = D('ChangeMoney','Logic')->doChange($money,$user_id,$toUser);
            if(!$res) {
                $this->setLogicError('转账操作失败');
                return false;
            }
            $user_name = getName('User','real_name',$user_id);
            $to_user_name = getName('User','real_name',$toUser);
            //余额明细表增加
            $data[0]['user_id'] = $user_id;
            $data[0]['money'] = $money;
            $data[0]['act_type'] = 6;
            $data[0]['reason'] = '转帐给'.$to_user_name.'金额'.$money;
            $data[0]['act_id'] = $request['id'];
            $msg = "您成功转账给".$to_user_name.$money.'元';

            //余额明细表增加
            $data[1]['user_id'] = $toUser;
            $data[1]['money'] = $money;
            $data[1]['act_type'] = 7;
            $data[1]['reason'] = $user_name.'转入金额'.$money;
            $data[1]['act_id'] = $request['id'];
            $msg1 = "您收到".$user_name."转账给您的".$money.'元';
            $res = M('BalanceLog')->addAll($data);
            if($res){
                //发送系统消息
                sendSystemMsg($msg,$user_id);
                sendSystemMsg($msg1,$toUser);
            }

        }
        return true;
    }
    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        //判断是否是拒绝认证
        if($result && $request['status'] == '2'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 12;//线下类型 12
            $data['create_time'] = time();//会员认证类型 1
            $data['action_admin'] = getManagerName();//会员认证类型 1
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }
        return true;
    }

    /**
     * 执行转账函数
     * 传入金额 甲方 乙方 id
     */
    public function doChange($money,$user_id,$toUser){
        $mod = D('User');
        $mod->startTrans();
        $res1 = $mod->where("id = {$user_id}")->setDec('balance',$money);
        $res2 = $mod->where("id = {$toUser}")->setInc('balance',$money);
        if($res1 && $res2){
            $mod->commit();
            return true;
        }else{
            $mod->rollback();
            return false;
        }
    }






}
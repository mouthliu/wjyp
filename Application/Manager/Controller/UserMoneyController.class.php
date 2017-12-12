<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class UserMoneyController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//underMoney
//    function getAddRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }

    //获取前台给的数据
    function getData(){
        $data['user_id'] = $_POST['user_id'];
        $data['create_time'] = time();
        $data['type'] = $_POST['type'];
        $data['money'] = $_POST['money'];
        $data['pay_type'] = $_POST['pay_type'];
        $data['status'] = 0;
    }
    //线下充值管理
    function underMoney(){
        $result = D('UserMoney', 'Logic')->underMoney(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error("暂无商品");
        }
        $this->display('underIndex');
    }
    function underUpdate(){
        $row = M('UserUnderMoney')->where("id = {$_GET['id']}")->select();
        if(!$row){
            $this->error('暂无记录');
        }
        $row = M('UserUnderMoney')->where("id = {$_GET['id']}")->find();
        $row['pic'] = M('File')->where("id = {$row['pic']}")->getField('path');
        $this->assign('row',$row);
        $this->display('underUpdate');
    }
    //线下充值执行更新
    function doUnderUpdate(){
        if(!$_POST['id']){
            $this->error('参数不足');die();
        }
        $mod = D('UserUnderMoney');
        $mod->startTrans();
        $id = $_POST['id'];
        $data['action_person'] = $_POST['action_person'];
        $data['status'] = $_POST['status'];
        //判断是否是拒绝认证
        if($_POST['status'] == '2'){
            //判断理由
            if(!$_POST['refuse_desc']){
                $this->error('请填写拒绝认证理由');return false;
            }
            $data['refuse_desc'] = $_POST['refuse_desc'];
        }
        $res = $mod->where("id = {$id}")->save($data);
        if($res){
            if($_POST['status'] == '1'){
                //获取到充值信息
                $info = $mod->where("id={$id}")->find();
                //给充值对象增加金额
                if($info){
                    $money = $info['money']?$info['money'] : '0';
                    $res = balanceChange($money,2,'线下充值到账'.$money.'元',$info['user_id']);
                    if($res){
                        $mod->commit();
                    }else{
                        $mod->rollback();
                        $this->error('执行失败');return false;
                    }
                }
            }
            $this->success('执行成功');
        }else{
            $this->error('执行失败');
        }
    }

}

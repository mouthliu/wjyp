<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class UserCashController extends BaseController {

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



}

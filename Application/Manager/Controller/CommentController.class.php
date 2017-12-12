<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class CommentController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//
//    function getAddRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }

    //接收数据函数
    function setComment(){
        $data['goods_id'] = $_POST['goods_id'];
        $data['goods_name'] = $_POST['goods_name'];
        $data['merchant_id'] = $_POST['merchant_id'];
        $data['user_id'] = $_POST['user_id'];
        $data['nickname'] = $_POST['nickname'];
        $data['picture'] = $_POST['picture'];
        $data['all_star'] = $_POST['all_star'];
        $data['content'] = $_POST['content'];
        $data['ip'] = $_POST['ip'];
        $data['product_id'] = $_POST['product_id'];
    }



}

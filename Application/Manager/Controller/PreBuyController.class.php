<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class PreBuyController extends BaseController {

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


    //从前台
    function setLog(){
        if(empty($_POST['id']) && empty($_POST['bid_user_id'])){
            $this->error("参数不足");
            return false;
        }

          //设置记录表
        $data['one_buy_id'] = $_POST['id'];
        $data['bid_user_id'] = $_POST['bid_user_id'];

        $data['phone'] = $_POST['phone'];

        $data['time_num'] = $_POST['time'];
        $data['append_num'] = 1; //参与次数统计出来
        $id = D('PreBuyLog')->add($data);
        if($id){
            //设置参与号码 活动id加上自己的id
            $addCode['add_code'] = zero($_POST['id'].zero($id,4),9);
            D('OnerBuyLog')->where("id={$id}")->save($addCode);
            //返回期号
            return $addCode['add_code'];
        }


    }

    function record(){
        if(empty($_GET['id'])){
            $this->error("没有此记录");
            return false;
        }
        $list = M('PreBuyLog')->alias('a')
            ->field('a.*,u.phone,u.nickname')->join(C('DB_PREFIX')."user AS u ON a.bid_user_id=u.id")->where("a.one_buy_id={$_GET['id']}")->order("a.bid_time DESC")->select();

        $this->assign("list",$list);
        $this->display('PreBuy/record');
    }



}

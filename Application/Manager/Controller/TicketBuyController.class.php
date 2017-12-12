<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class TicketBuyController extends BaseController {

    function getIndexRelation() {

    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//
//    function getAddRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }

    public function addTicketBuyGoods(){
        $request = I('request.');
        $result = D('TicketBuy','Logic')->addTicketBuyGoods($request);
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('TicketBuy/addGoods');
    }

    //批量移出主题活动
    function removeTicketBuyGoods(){
        $request = I('request.');
        if(empty($request['ids'])){
            $this->error('请选择商品');
        }

        $where['id'] = array('IN',$request['ids']);
        $res = D('Goods')->where($where)->save(array('TicketBuy_id'=>0));
        if($res){
            $this->success('移出成功');
        }else{
            $this->error('移出失败');
        }
    }
    //添加票全区商品
    function addShop(){
        $res = D('TicketBuy')->add($_GET);
        if($res){
            D('Goods')->where("id={$_GET['goods_id']}")->save(array('ticket_buy_id'=>$res));
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
}

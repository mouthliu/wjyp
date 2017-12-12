<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class IntegralBuyController extends BaseController {

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

    public function addIntegralBuyGoods(){
        $request = I('request.');
        $result = D('IntegralBuy','Logic')->addIntegralBuyGoods($request);
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('IntegralBuy/addGoods');
    }

    //添加兑换商品
    function addShop(){
        $res = D('IntegralBuy')->add($_GET);
        if($res){
            D('Goods')->where("id={$_GET['goods_id']}")->save(array('integral_buy_id'=>$res));
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

}

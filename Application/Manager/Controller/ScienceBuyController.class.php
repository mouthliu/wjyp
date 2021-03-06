<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class ScienceBuyController extends BaseController {

    function getIndexRelation() {
        $ads = M('Ads')->where("position = 15")->find();
        $ads['picture'] = api('System/getFiles',array($ads['picture']));
        $this->assign("ads",$ads);
    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//
//    function getAddRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }

    public function addScienceBuyGoods(){
        $request = I('request.');
        $result = D('ScienceBuy','Logic')->addScienceBuyGoods($request);
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('ScienceBuy/addGoods');
    }


    //添加品牌团商品
    function addShop(){
        $data['goods_id'] = $_GET['goods_id'];
        $data['create_time'] = time();
        $res = D('ScienceBuy')->add($data);
        if($res){
            D('Goods')->where("id={$_GET['goods_id']}")->save(array('is_ScienceBuy'=>1));
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

}

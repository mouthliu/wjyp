<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 管理员控制器
 */
class CheapGroupController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
    function getUpdateRelation() {

    }

    function getAddRelation() {


    }

    function addGoods(){
        if(empty($_GET['cheap_group_id'])){
            $this->error("请选择优惠组合");
            return false;
        }
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //处理分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $cates = getName('Merchant','cates',$mid);
        $this->assign('cate_list',$cate_list);
        $this->assign('cates',$cates);
        $this->display('CheapGroup/addGoods');
    }
    //优惠商品列表
    function editGoods(){
        if(empty($_GET['id'])){
            $this->error("没有此记录");
            return false;
        }

        $list = M('GroupGoods')->alias('a')
            ->field('a.*,g.shop_price,goods_img,goods_name')->join(C('DB_PREFIX')."goods AS g ON a.goods_id=g.id")->where("a.cheap_group_id={$_GET['id']}")->order("a.sort DESC")->select();
        foreach($list as $k=>$v){
            $list[$k]['goods_img'] = D('File')->where('id='.$v['goods_img'])->getField('path');
        }
        $this->assign("list",$list);
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //处理分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $cates = getName('Merchant','cates',$mid);
        $this->assign('cate_list',$cate_list);
        $this->assign('cates',$cates);
        $this->display('CheapGroup/goodsList');
    }
    //获取商品
    function getGoodsList(){
        $mid = getMerchantId();
        if(!$_POST['cate_id']){
            echo ''; return false;
        }
        $where['merchant_id'] = $mid;
        $where['cat_id'] = $_POST['cate_id'];
        $where['status'] = 2;
        $list = M('Goods')->where($where)->field("id,goods_name,shop_price,goods_num")->select();
        if($list){
            $str = '<option>--请选择商品--</option>';
            foreach($list as $k=>$v){
                $str .= "<option value='{$v['id']}' data-num='{$v['goods_num']}'>{$v['goods_name']}，¥ {$v['shop_price']}</option>";
            }
            echo $str;
        }else{
            echo '<option>暂无商品</option>';
        }
    }
    //getAttrGroupId1
    function getProductList(){
        if(!$_POST['goods_id']){
            echo ''; return false;
        }
        $where['goods_id'] = $_POST['goods_id'];
        $list = M('Products')->where($where)->select();

        if($list){
            $str = '';
            foreach($list as $k=>$v){
                $attr = getAttrGroupId1($_POST['goods_id'],$v['id']);
                $str .= "<option value='{$v['id']}'>{$attr}，库存:{$v['product_number']}</option>";
            }
            echo $str;
        }else{
            echo '<option>暂无货品</option>';
        }
    }
    //商品执行添加
    function doAdd(){
        if(empty($_POST['cheap_group_id']))return $this->error('参数不足');
        if(empty($_POST['goods_id']))return $this->error('参数不足');
        $data['cheap_group_id'] = $_POST['cheap_group_id'];
        $data['goods_id'] = $_POST['goods_id'];
        $data['sort'] = $_POST['sort'] ? $_POST['sort'] : '0';
        $res = M('GroupGoods')->add($data);
        if($res){
            $this->success('添加商品成功');
        }else{
            $this->error('添加商品失败');
        }

    }
}

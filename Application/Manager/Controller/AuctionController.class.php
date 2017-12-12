<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class AuctionController extends BaseController {

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

    //获取商品函数
    function getGoods(){
        $good = D('Goods');
        if(!empty($_POST['gid'])){
            $res = $good->field("id,goods_name,merchant_id")->where("id={$_POST['gid']}")->find();
            if($res){
                $sel = $this->createSel($res['id']);
                //创建节点
                $html = "<tr><td class='goods_id'>{$res['id']}</td><td class='goods_name' data-mid='{$res['merchant_id']}'>{$res['goods_name']}</td><td>{$sel}</td><td><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                echo $html;
            }else{
                return false;
            }
            exit;
        }
        if(!empty($_POST['gname'])){
            $res = $good->field("id,goods_name,merchant_id")->where("goods_name LIKE '%{$_POST['gname']}%'")->select();
            if($res){
                $html = '';
                //创建节点
                foreach($res as $k=>$v){
                    $sel = $this->createSel($v['id']);
                    $html .= "<tr><td class='goods_id'>{$v['id']}</td><td class='goods_name' data-mid='{$v['merchant_id']}'>{$v['goods_name']}</td><td>{$sel}</td><td><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                }
                echo $html;
            }else{
                return false;
            }
            exit;
        }
    }

    function recordAuct(){
        if(empty($_GET['auct_id'])){
            $this->error("没有此记录");
            return false;
        }
        $list = M('AuctionLog')->alias('a')
            ->field('a.*,u.phone,u.nickname')->join(C('DB_PREFIX')."user AS u ON a.bid_user_id=u.id")->where("a.auct_id={$_GET['auct_id']}")->order("a.bid_time DESC")->select();
        $this->assign("list",$list);
        $this->display('Auction/record');
    }

    //获取货品下拉列表函数   需要传入商品的ID,货品列表
    function createSel($goods_id){
        //获取货品信息
        $products = M('Products')->where("goods_id={$goods_id}")->select();
        $sel = '暂无货品';
        if(!$products){
            return $sel;
        }
        //获取到goods_attr属性值数组
        $attr = M('GoodsAttr')->where("goods_id={$goods_id}")->select();
        //创建属性值对应数组
        foreach($attr as $k1=>$v1){
            $attr_arr[$v1['id']] = $v1['attr_value'];
        }
        //将得到的货品信息做成下拉列表
        $sel = '<select name="product_id" style="width:auto;">';
        foreach($products as $k=>$v){
            $garr = explode('|',$v['goods_attr']);
            foreach($garr as $k1=>$v1){
                $garr[$k1] = $attr_arr[$v1];
            }
            $garr = implode(',',$garr);

            $sel .= "<option value='{$v['id']}'>{$garr}</option>";
        }

        $sel .= '</select> ';
        return $sel;
    }
//    function setField(){
//        $request = I(request.);
//        D('Auction','Logic')->setField($request);
//    }

}

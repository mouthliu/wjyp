<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 管理员控制器
 */
class AuctionController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
    function getUpdateRelation() {
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //处理分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $cates = getName('Merchant','cates',$mid);
        $this->assign('cate_list',$cate_list);
        $this->assign('cates',$cates);
    }

    function getAddRelation() {
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //处理分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $cates = getName('Merchant','cates',$mid);
        $this->assign('cate_list',$cate_list);
        $this->assign('cates',$cates);
    }

    //获取商品函数
    function getGoods(){
        $good = D('Goods');
        $where['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $where['status'] = 2;
        if(!empty($_POST['gid'])){
            $where['id'] = $_POST['gid'];

            $res = $good->field("id,goods_name,merchant_id")->where($where)->find();
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
            $where['goods_name'] = array("LIKE","%{$_POST['gname']}%");
            $res = $good->field("id,goods_name,merchant_id")->where($where)->select();
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
            $str = '<option>请选择商品</option>';
            foreach($list as $k=>$v){
                $str .= "<option value='{$v['id']}' data-num='{$v['goods_num']}'>{$v['goods_name']}，¥ {$v['shop_price']}</option>";
            }
            echo $str;
        }else{
            echo '<option>暂无商品</option>';
        }
    }

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
}

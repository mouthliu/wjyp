<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 优惠券控制器
 */
class AddTicketController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
    function getUpdateRelation() {
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //根据所选经营范围列出
        foreach(explode(',',$range_id) as $k=>$v){
            $range_list[] = M('GoodsCategory')->field('id,short_name,name')->where("id={$v}")->find();
        }
        $this->assign('range_list',$range_list);
    }

    function getAddRelation() {
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //根据所选经营范围列出
        foreach(explode(',',$range_id) as $k=>$v){
            $range_list[] = M('GoodsCategory')->field('id,short_name,name')->where("id={$v}")->find();
        }
        $this->assign('range_list',$range_list);
    }

    //获取商品函数
    function getGoods(){
        $good = D('Goods');
        $where['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $where['status'] = 2;
        if(!empty($_POST['gid'])){
            $where['id'] = $_POST['gid'];
            $res = $good->field("id,goods_name,merchant_id,shop_price")->where($where)->find();
//            dump($good->getLastSql());exit;
            if($res){
                //创建节点
                $html = "<tr><td class='goods_id'>{$res['id']}</td><td class='goods_name' data-mid='{$res['merchant_id']}'>{$res['goods_name']}</td><td data-price='{$res['shop_price']}'><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                echo $html;
            }else{
                return false;
            }
            exit;
        }
        if(!empty($_POST['gname'])){
            $where['goods_name'] = array("LIKE","%{$_POST['gname']}%");
            $res = $good->field("id,goods_name,merchant_id,shop_price")->where($where)->select();
            if($res){
                $html = '';
                //创建节点
                foreach($res as $k=>$v){
                    $html .= "<tr><td class='goods_id'>{$v['id']}</td><td class='goods_name' data-mid='{$v['merchant_id']}'>{$v['goods_name']}</td><td data-price='{$v['shop_price']}'><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                }

                echo $html;
            }else{
                return false;
            }
            exit;
        }
    }
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
        $id = D('TicketLog')->add($data);
        if($id){
            //设置参与号码 活动id加上自己的id
            $addCode['add_code'] = zero($_POST['id'].zero($id,4),9);
            D('OnerBuyLog')->where("id={$id}")->save($addCode);
            //返回期号
            return $addCode['add_code'];
        }


    }





}

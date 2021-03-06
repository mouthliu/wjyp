<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 管理员控制器
 */
class LimitBuyController extends BaseController {

    function getUpdateRelation() {
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //处理分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $cates = getName('Merchant','cates',$mid);
        $this->assign('cate_list',$cate_list);
        $this->assign('cates',$cates);
        $this->assign('stage_list',M('LimitStage')->select());

    }

    function getAddRelation() {
        $mid = getMerchantId();
        $range_id = getName('Merchant','range_id',$mid);
        //处理分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $cates = getName('Merchant','cates',$mid);
        $this->assign('cate_list',$cate_list);
        $this->assign('cates',$cates);
        $this->assign('stage_list',M('LimitStage')->select());

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
        $id = D('LimitBuyLog')->add($data);
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
        $list = M('LimitBuyLog')->alias('a')
            ->field('a.*,u.phone,u.nickname')->join(C('DB_PREFIX')."user AS u ON a.bid_user_id=u.id")->where("a.one_buy_id={$_GET['id']}")->order("a.bid_time DESC")->select();

        $this->assign("list",$list);
        $this->display('LimitBuy/record');
    }



}

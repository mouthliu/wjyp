<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class GroupBuyController extends BaseController {

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
            $res = $good->field("id,goods_name,merchant_id,shop_price")->where("id={$_POST['gid']}")->find();
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
            $res = $good->field("id,goods_name,merchant_id,shop_price")->where("goods_name LIKE '%{$_POST['gname']}%'")->select();
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
    //当开团以后自动创建这个表
    function record(){
        if(empty($_GET['id'])){
            $this->error("没有此记录");
            return false;
        }
        $list = M('GroupBuyLog')->alias('a')
            ->field('a.*')->where("group_buy_id = {$_GET['id']}")->order("a.start_time DESC")->select();
        $this->assign("list",$list);
        $this->display('GroupBuy/record');
    }

    //用来获取参团人信息
    function getLogUsers(){
        //根据得到的logID 获取到参团人数
        if(empty($_POST['id'])){
            $this->error("参数不足");
            return false;
        }
        $list = M("LogUsers")->alias('l')->field('l.*,u.phone,u.nickname')->join(C('DB_PREFIX').'user  u ON u.id=l.user_id')->where("log_id={$_POST['id']}")->select();
        if($list){
            $html = '';
            foreach($list as $k=>$v){
                $first = $v['is_first']==1?'是':'否';
                $html .= "<tr>";
                $html .=  "<td>{$v['id']}</td>";
                $html .=  "<td>{$v['log_id']}</td>";
                $html .=  "<td>{$v['phone']}</td>";
                $html .=  "<td>{$first}</td></tr>";

            }
            echo $html;
        }else{
            return false;
        }
    }

}

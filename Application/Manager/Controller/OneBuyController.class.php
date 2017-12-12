<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class OneBuyController extends BaseController {

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
//            dump($good->getLastSql());exit;
            if($res){
                //创建节点
                $html = "<tr><td class='goods_id'>{$res['id']}</td><td class='goods_name' data-mid='{$res['merchant_id']}'>{$res['goods_name']}</td><td><a href='javascript:;' class='xuan'>选定</a></td></tr>";
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
                    $html .= "<tr><td class='goods_id'>{$v['id']}</td><td class='goods_name' data-mid='{$v['merchant_id']}'>{$v['goods_name']}</td><td><a href='javascript:;' class='xuan'>选定</a></td></tr>";
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
        $id = D('OneBuyLog')->add($data);
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
        $list = M('OneBuyLog')->alias('a')
            ->field('a.*,u.phone,u.nickname')->join(C('DB_PREFIX')."user AS u ON a.bid_user_id=u.id")->where("a.one_buy_id={$_GET['id']}")->order("a.bid_time DESC")->select();

        $this->assign("list",$list);
        $this->display('OneBuy/record');
    }
    //积分夺宝配置
    function setting(){
        $row = M('OneBuyConfig')->find();
        $this->assign("row",$row);
        $this->display('OneBuy/setting');
    }
    //配置更新
    function setUpdate(){
        $data['integral_num'] = $_POST['integral_num'];
        $data['discount_num'] = $_POST['discount_num'];
        $data['yellow_discount_num'] = $_POST['yellow_discount_num'];
        $data['blue_discount_num'] = $_POST['blue_discount_num'];
        if($_POST['id']){
            $res = D('OneBuyConfig')->where("id={$_POST['id']}")->save($data);
        }else{
            $res = D('OneBuyConfig')->add($data);
        }
        if($res){
            $_POST['id'] ? $this->success('修改成功') : $this->success('添加成功');
        }else{
            $_POST['id'] ? $this->error('修改失败') : $this->error('添加失败');
        }

    }



}

<?php

namespace Manager\Controller;

/**
 * Class GoodsController
 * @package Manager\Controller
 * 管理员控制器
 */
class GoodsController extends BaseController {

    protected function getUpdateRelation(){
        $attr = M("Attribute")->query("SELECT a.id,v.id as aid,a.attr_name,v.attr_value FROM  db_attribute AS a LEFT JOIN db_goods_attr AS v ON v.attr_id = a.id  WHERE a.attr_type=1 AND a.status !=9 AND v.goods_id = {$_GET['id']} AND a.is_attr_gallery = 1 ");
        //查询出商品的所用图片
        if(!empty($_GET['id'])){
            //获取到这个商品的所有图片
            $allPic = M("GoodsGallery")->where("goods_id={$_GET['id']}")->select();
            foreach($allPic as $k=>$v){
                $attr_pic[$v['goods_attr_name']]['pic'] = api('System/getFiles', array(explode(",",$v['pictures'])));

                $attr_pic[$v['goods_attr_name']]['sort'] = $v['sort'];
                $attr_pic[$v['goods_attr_name']]['is_show'] = $v['is_show'];
            }
            //处理图片 根据不同的属性
            $this->assign('attr_pic',$attr_pic);
        }
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->select());
        $this->assign("attr_img",$attr);
        $this->assign('server_list',M('GoodsServer')->where("status =1 ")->order('sort DESC')->select());
    }

    // 获取商品类型下对应的属性  并ajax返回
    function getGoodsAttr(){
        //执行逻辑层
        D('Goods',"Logic")->getGoodsAttr();
    }

    /**
     * 修改商品函数
     * @return bool
     */
    function setStatus(){
        $data['server'] = implode(',',$_POST['server']);
        if(!$_POST['id']){
            $this->error('请选择商品id');
        }
        //判断是否是拒绝认证
        if($_POST['status'] == '3'){
            //判断理由
            if(!$_POST['refuse_desc']){
                $this->error('请填写拒绝理由');return false;
            }
            $data['refuse_desc'] = $_POST['refuse_desc'];
        }
        $goods_id = $_POST['id'];
        $data['status'] = $_POST['status'];
//        if(!empty($_POST['price_rate'])){//系统修改平台比例
//            $data['price_rate'] = $_POST['price_rate'];
//        }
        if(!empty($_POST['integral'])){//系统修改积分
            $data['integral'] = $_POST['integral'];
        }
        if(!empty($_POST['discount'])){//系统修改用券比例(红)
            $data['discount'] = $_POST['discount'];
        }
        if(!empty($_POST['yellow_discount'])){//系统修改用券比例(黄)

            $data['yellow_discount'] = $_POST['yellow_discount'];
        }
        if(!empty($_POST['blue_discount'])){//系统修改用券比例(蓝)
            $data['blue_discount'] = $_POST['blue_discount'];
        }
        $res = D("Goods")->where("id = {$goods_id}")->save($data);
        if($res){
            //往拒绝表中加入数据
            $data['id_val'] = $goods_id;
            $data['type'] = 2;//商品类型 2
            $data['create_time'] = time();//会员认证类型 1
            $data['action_admin'] = getManagerName();//会员认证类型 1
            $data['refuse_desc'] = $_POST['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
            $this->success("执行成功");
            return true;
        }else{
            $this->error("执行失败");
            return false;
        }
    }

    //强制上下架
    function setBuy(){
        $request = I('request.');
        if(!empty($request['id'])){
             if($res){
                $this->success("执行成功");
                return true;
            }else{
                $this->error("执行失败");
                return false;
            }
        }
        if(!empty($request['ids'])){
            $where['id'] = array("IN",$request['ids']);
            $res = D('Goods')->where($where)->save(array("is_buy"=>$request['is_buy'],'status'=>1));
            if($res){
                $this->success("执行成功");
                return true;
            }else{
                $this->error("执行失败");
                return false;
            }
        }
    }

    /**
     * 设置推荐
     */
    function setRecommend(){
        $data['is_recommend'] = isset($_POST['is_recommend'])?$_POST['is_recommend']: die('error');
        $res = D('Goods')->where("id={$_POST['goods_id']}")->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * 调价详情
     */
    public function adjustment(){
        if(IS_POST){
            $where['id'] = $_POST['id'];
            $adjustment_price_info = M('AdjustmentPrice')->where($where)->find();
            //更新商品的数据库
            if($_POST['adjustment_result']==1){
                //同意调价
                unset($where);
                $where['id'] = $adjustment_price_info['goods_id'];
                $data['market_price'] = $adjustment_price_info['market_price'];
                $data['shop_price'] = $adjustment_price_info['shop_price'];
                $data['settlement_price'] = $adjustment_price_info['settlement_price'];
                $data['is_doing_adjustment'] = 0;
                M('Goods')->where($where)->data($data)->save();
                M('AdjustmentPrice')->where(array('id'=>$_POST['id']))->data(array('status'=>1,'remark'=>$_POST['remark']))->save();
            }else{
                if(empty($_POST['remark'])){
                    $this->error('请填写拒绝原因');
                    return false;
                }
                //拒绝调价
                unset($where);
                unset($data);
                $where['id'] = $adjustment_price_info['goods_id'];
                $data['is_doing_adjustment'] = 0;
                $data['update_time'] = time();
                M('Goods')->where($where)->data($data)->save();
                M('AdjustmentPrice')->where(array('id'=>$_POST['id']))->data(array('status'=>2,'remark'=>$_POST['remark']))->save();
            }
            $this->success('操作成功', Cookie('__forward__'));
        }else{
            $goods_info = M('Goods')->where(array('id'=>$_GET['id']))->find();
            $rate = getName('GoodsCategory','min_rate',$goods_info['cat_id']);
            $info = M('AdjustmentPrice')->where(array('goods_id'=>$_GET['id'],'adjustment_sn'=>$goods_info['adjustment_sn']))->find();
            $info['adjustment_picture'] = api('System/getFiles', array($info['adjustment_picture']));
            //新增:计算会员价格(无优会员,优享会员)(参考api接口)
            $info['wy_price'] = sprintf('%.2f',$goods_info['shop_price']*(1-0.05+0.05*$rate));
            $info['yx_price'] = sprintf('%.2f',$goods_info['shop_price']*(1-0.1+0.1*$rate));
            $info['integral'] = $goods_info['integral'];
            $config = D('Config')->parseList();
            $info['price_desc'] = $config['price_desc'];
            //红 黄 蓝三种代金券处理(参考api接口)
            $dj_ticket = array();
            $index = 0;
            if($goods_info['discount']>0.01){
                $dj_ticket[$index]['discount_desc'] = '本产品最多可以使用'.$goods_info['discount']['discount'].'%红券抵扣现金';
                $dj_ticket[$index]['type'] = "1";//红券
                $index = $index+1;
            }
            if($goods_info['yellow_discount']>0.01){
                $dj_ticket[$index]['discount_desc'] = '本产品最多可以使用'. $goods_info['discount']['yellow_discount'].'%黄券抵扣现金';
                $dj_ticket[$index]['type'] = "2";//黄券
                $index = $index+1;
            }
            if($goods_info['blue_discount']>0.01){
                $dj_ticket[$index]['type'] = "3";//蓝券
                $dj_ticket[$index]['discount_desc'] = '本产品最多可以使用'.$goods_info['discount']['blue_discount'].'%蓝券抵扣现金';
            }
            $info['dj_ticket'] = $dj_ticket;
            $this->assign('row',$info);
            $this->display('adjustment');
        }
    }
}
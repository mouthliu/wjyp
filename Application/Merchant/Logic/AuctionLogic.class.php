<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class AuctionLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }
        $param['where']['status'] = array('lt',9);
        $param['where']['merchant_id'] = getMerchantId();
        $param['where']['order'] = "start_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Auction')->getList($param);

        //dump(D('Goods')->getLastSql()) ;

        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['t_status'] = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
            if($result['list'][$k]['product_id']){
                //根据货品id获取到属性值组合
                $result['list'][$k]['attr'] = $this->getAttrGroup($result['list'][$k]['goods_id'],$result['list'][$k]['product_id']);
            }
        }

//        dump($result);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['goods.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['merchant_id'] = getMerchantId();
        $row = D('Auction')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        if($row['product_id']){
            //根据货品id获取到属性值组合
            $row['attr'] = $this->getAttrGroup($row['goods_id'],$row['product_id']);
        }
        $row['cate_id'] = getName('Goods','cat_id',$row['goods_id']);
        $where['merchant_id'] = getMerchantId();
        $where['cat_id'] = $row['cate_id'];
        $where['status'] = 2;
        $row['goods_list'] = M('Goods')->where($where)->field("id,goods_name,shop_price")->select();
        $row['product_list'] = M('Products')->where("goods_id = {$row['goods_id']}")->select();
        //        dump($row);
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('Auction')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }
    //所用到processData
    protected function processData($data = array()) {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time'])+(3600*24)-1;
        $data['integral'] = getIntegral($data['start_price']);//系统计算积分
        $data['now_price'] = $data['start_price'];
        return $data;
    }

    function getAttrGroup($goods_id,$product_id){
        //获取到goods_attr属性值数组
        $attr = M('GoodsAttr')->where("goods_id={$goods_id}")->select();
        //创建属性值对应数组
        foreach($attr as $k1=>$v1){
            $attr_arr[$v1['id']] = $v1['attr_value'];
        }
        $goods_attr = M('Products')->field('goods_attr')->where("id={$product_id}")->find()['goods_attr'];
        $garr = explode('|',$goods_attr);
        foreach($garr as $k1=>$v1){
            $garr[$k1] = $attr_arr[$v1];
        }
        $garr = implode(',',$garr);
        return $garr;
    }

    /**
     * 验证该商品是否已经参加活动
     */
    function beforeUpdate($request){
        
        //判断时间
        if(strtotime($request['start_time']) < time()){
            $this->setLogicError("活动时间有误");
            return false;die();
        }
        if(strtotime($request['start_time']) > strtotime($request['end_time'])+3600){
            $this->setLogicError("结束时间不能小于开始时间");
            return false;die();
        }
        if(empty($request['goods_id'])){
            $this->setLogicError("请选择商品");
            return false;die();
        }
        if(empty($request['id'])){

        }else{
            $where['id'] = array('neq',$request['id']);
        }
        $where['goods_id'] = $request['goods_id'];
        if(!empty($request['product_id'])){
            $where['product_id'] = $request['product_id'];
        }else{
            $where['product_id'] = 0;
        }
        //参与活动的
        $where['end_time'] = array('egt',time());
        $res = M('Auction')->where($where)->find();
        if($res){
            $this->setLogicError("该商品已经参与该活动");
            return false;die();
        }
        return true;
    }
}
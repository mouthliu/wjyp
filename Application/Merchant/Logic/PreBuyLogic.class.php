<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class PreBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }
        $param['where']['goods.status'] = array('lt',9);
        $param['where']['goods.merchant_id'] = getMerchantId();
        $param['order'] = "start_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('PreBuy')->getList($param);

        //dump(D('Goods')->getLastSql()) ;

        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['t_status'] = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
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

        $param['where']['goods.merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $row = D('PreBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        $row['cate_id'] = getName('Goods','cat_id',$row['goods_id']);
        $where['merchant_id'] = getMerchantId();
        $where['cat_id'] = $row['cate_id'];
        $where['status'] = 2;
        $row['goods_list'] = M('Goods')->where($where)->field("id,goods_name,shop_price")->select();
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('PreBuy')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }

    //时间插件所用到processData
    protected function processData($data = array()) {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time']);
        $data['end_delivery_date']   = strtotime($data['end_delivery_date']);
        $data['integral'] = getIntegral($data['deposit']);
        return $data;
    }
    /**
     * 验证该商品是否已经参加活动
     */
    function beforeUpdate($request){
        if(empty($request['goods_id'])){
            $this->setLogicError("请选择商品");
            return false;die();
        }
        if(strtotime($request['start_time']) < time()){
            $this->setLogicError("活动时间有误");
            return false;die();
        }
        if(strtotime($request['start_time']) > strtotime($request['end_time'])){
            $this->setLogicError("活动时间有误");
            return false;die();
        }
        if(empty($request['id'])){
        }else{
            $where['id'] = array('neq',$request['id']);
        }
        $where['goods_id'] = $request['goods_id'];
        $where['money_end'] = array('lt',time());
        //参与活动的
        $res = M('PreBuy')->where($where)->find();
        if($res){
            $this->setLogicError("该商品已经参与该活动");
            return false;die();
        }
        return true;
    }
}
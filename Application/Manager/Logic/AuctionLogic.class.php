<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class AuctionLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['status'])) {
            $param['where']['status'] = $request['status'];
        }else{
            $param['where']['status'] = array(array('gt',0),array('lt',9));
        }
        if(isset($request['t_status'])){
            switch($request['t_status']){
                case 1:
                    $param['where']['start_time'] = array('elt',time());
                    $param['where']['end_time'] = array('egt',time());
                    break;
                case 2:
                    $param['where']['end_time'] = array('lt',time());
                    break;
                default:
                    $param['where']['start_time'] = array('gt',time());
            }
            $param['where']['status'] = 2;
        }
        $param['where']['order'] = "start_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Auction')->getList($param);
//        dump(D('Auction')->getLastSql()) ;
        foreach($result['list'] as $k=>$v){
            $t_status = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
            $result['list'][$k]['t_status'] = $t_status;
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
        $row = D('Auction')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        if($row['product_id']){
            //根据货品id获取到属性值组合
            $row['attr'] = $this->getAttrGroup($row['goods_id'],$row['product_id']);
        }
        if(!empty($row['goods_id'])){
            $row['goods_name'] = M('goods')->where(array('id'=>$row['goods_id']))->getField('goods_name');
        }
        //获取到原商品的使用券比例和积分
        $info = M('Goods')->field('integral,discount,yellow_discount,blue_discount')->where("id={$row['goods_id']}")->find();
        $row['goods_integral'] = $info['integral'];
        $row['goods_discount'] = $info['discount'];
        $row['goods_yellow_discount'] = $info['yellow_discount'];
        $row['goods_blue_discount'] = $info['blue_discount'];
//        p($row);
        return $row;
    }

    //设置状态
    function setField($request){

        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }else{
            $this->setLogicError("参数不足"); return false;
        }
        if(empty($request['model'])){
            $this->setLogicError("参数不足"); return false;
        }
        $mod = D($request['model']);
        $res = $mod->where($data)->save($request);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }
    //时间插件所用到processData
    protected function processData($data = array()) {
        if(!empty($data['start_time'])){
            $data['start_time'] = strtotime($data['start_time']);
        }
        if(!empty($data['end_time'])){
            $data['end_time']   = $data['start_time']+(3600*24)-1;
        }

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
    * @param array $request
    * @return boolean
    * 更新前执行
    */
    public function beforeUpdate($request = array()) {
        //判断是否是拒绝认证
        if($request['status'] == '3'){
            //判断理由
            if(!$request['refuse_desc']){
                $this->setLogicError('请填写拒绝认证理由');return false;
            }
        }
        return true;
    }
    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        //判断是否是拒绝认证
        if($result && $request['status'] == '3'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 8;//拍卖审核类型 8
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }
        return true;
    }


}
<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
/**
 * Class OneBuyLogic
 * @package Manager\Logic
 */

class OneBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['status'])) {
            //按管理员账号查询
            $param['where']['goods.status'] = $request['status'];
        }else{
            $param['where']['goods.status'] = array(array('gt',0),array('lt',9));
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
        $param['order'] = "start_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('OneBuy')->getList($param);
        //dump(D('Goods')->getLastSql()) ;
        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['t_status'] = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
        }
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


        $row = D('OneBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        if($row['product_id']){
            //根据货品id获取到属性值组合
            $row['attr'] = $this->getAttrGroup($row['goods_id'],$row['product_id']);
        }
        $uinfo = array();
        if($row['t_status']==2){
            if($row['winer_code']){
                //说明已经结束 查询到获奖者的信息(根据幸运号码)
                $uinfo = M('OneBuyLog')->where("one_buy_id={$row['id']} AND add_code={$row['winer_code']}")->find();

            }

        }
        $row['uinfo'] = $uinfo;
        return $row;
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

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('OneBuy')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }



    //时间插件所用到processData
    protected function processData($data = array()){
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time']);
        return $data;
    }
    /**
     * @param array $request
     * @return boolean
     * 更新前执行
     */
    public function beforeUpdate($request = array()) {
        $data['balance_lot'] = $request['balance_lot'];
        $data['integral_lot'] = $request['integral_lot'];
        $data['red_discount_lot'] = $request['red_discount_lot'];
        $data['yellow_discount_lot'] = $request['yellow_discount_lot'];
        $data['blue_discount_lot'] = $request['blue_discount_lot'];
        if(array_sum($data) != $request['person_num']){
            $this->setLogicError('各分类最大份数和不等于总份数,请检查!');return false;
        }
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
    protected function afterUpdate($result, $request = array()){
        if(empty($request['id'])){
            $id = $result['id'];//生成期号
            M('OneBuy')->where("id={$id}")->save(array('time_num'=>$id));
        }
        //判断是否是拒绝认证
        if($result && $request['status'] == '3'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 6;//积分夺宝（一元购）审核类型 6
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }
        return true;
    }
}
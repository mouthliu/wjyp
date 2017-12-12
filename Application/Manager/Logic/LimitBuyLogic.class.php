<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class LimitBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['status'])) {
            $param['where']['goods.status'] = $request['status'];
        }else{
            $param['where']['goods.status'] = array(array('gt',0),array('lt',9));
        }
//        if(isset($request['t_status'])){
//            switch($request['t_status']){
//                case 1:
//                    $param['where']['start_time'] = array('elt',time());
//                    $param['where']['end_time'] = array('egt',time());
//                    break;
//                case 2:
//                    $param['where']['end_time'] = array('lt',time());
//                    break;
//                default:
//                    $param['where']['start_time'] = array('gt',time());
//            }
//            $param['where']['status'] = 2;
//        }
        $param['order'] = "date DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('LimitBuy')->getList($param);
        //dump(D('Goods')->getLastSql()) ;
        foreach($result['list'] as $k=>$v){
            $s = M('LimitStage')->where("id = {$v['begin_stage']}")->find();
            $result['list'][$k]['begin_stage'] = $s['stage_name'].'：'.$s['start_time'].'点 - '.$s['end_time'].'点';
            $result['list'][$k]['t_status'] = $s['start_time']>date('H')?'0':($s['end_time']<date('H')?'2':'1');
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
        $param['where']['goods.status'] = array("gt",0);
        $row = D('LimitBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //获取到原商品的使用券比例和积分
        $info = M('Goods')->field('integral,discount,yellow_discount,blue_discount')->where("id={$row['goods_id']}")->find();
        $row['goods_integral'] = $info['integral'];
        $row['goods_discount'] = $info['discount'];
        $row['goods_yellow_discount'] = $info['yellow_discount'];
        $row['goods_blue_discount'] = $info['blue_discount'];

        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');

        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('LimitBuy')->where($data)->save($newdata);
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
        return $data;
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
            $data['type'] = 5;//限量购审核类型 5
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }
        return true;
    }


}
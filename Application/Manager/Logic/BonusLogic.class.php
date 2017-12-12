<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class BonusLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['merchant_id'])){
            $param['where']['merchant_id'] = $request['merchant_id'];
        }

        $param['order'] = 'merchant_id ASC,sort DESC';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Bonus')->getList($param);
//        dump(D('Bonus')->getLastSql());
//        dump($result);
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['bonus_ads'] = M('file')->where(array('id'=>$v['bonus_ads']))->getField('path');
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
        $param['where']['status'] = array("lt",9);
        $row = D('Bonus')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        return $row;
    }

    /**
    * @param array $request
    * @return boolean
    * 更新前执行
    */
    public function beforeUpdate($request = array()) {
        //判断是否是拒绝认证
        if($request['status'] == '2'){
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
        if($result && $request['status'] == '2'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 9;//红包审核类型 9
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }
        return true;
    }






}
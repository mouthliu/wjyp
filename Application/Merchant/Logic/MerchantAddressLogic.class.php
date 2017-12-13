<?php

namespace Merchant\Logic;

/**
 * 商家地址表 逻辑层
 * Class MerchantAddressLogic
 * @package Merchant\Logic
 */

class MerchantAddressLogic extends BaseLogic {

    /**
     * @desc:   地址列表展示
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-25 10:17
     * @param array $request
     * @return array
     */

    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);
        $param['where']['merchant_id'] = getMerchantId();
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('MerchantAddress')->getList($param);

        foreach($result['list'] as $k =>$v){
            $province_name = M('Region')->where(array('id'=>$v['province_id']))->getField('region_name');
            $city_name = M('Region')->where(array('id'=>$v['city_id']))->getField('region_name');
            $area_name = M('Region')->where(array('id'=>$v['area_id']))->getField('region_name');
            $street_name = M('Street')->where(array('street_id'=>$v['street_id']))->getField('street_name');
            $result['list'][$k]['address'] = $province_name.$city_name.$area_name.$street_name.$v['address'];
        }




        return $result;
    }

    /**
     * @desc:   查找一行数据
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-25 14:17
     * @param array $request
     * @return array
     */

    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('MerchantAddress')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @desc:   获取商家id
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-25 16:17
     * @param array $request
     * @return array
     */

    public function processData($data = array())
    {
        $merchant_id = getMerchantId();
        $data['merchant_id'] = $merchant_id;
        //处理默认冲突问题
        if($data['is_default'] == 1){
            M('MerchantAddress')->where(array('merchant_id'=>$merchant_id))->data(array('is_default'=>0))->save();
        }
        return $data;
    }

    /**
     * @desc:   修改状态
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-26 14:20
     * @param:  array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     */

    function setStatus($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        $d_id = M('MerchantTemplate')->field('d_id')->where(array('d_id'=> $request['ids']))->select();
        $b_id = M('MerchantTemplate')->field('b_id')->where(array('b_id'=> $request['ids']))->select();
        if(empty($b_id) && empty($d_id)){
            //执行前操作
            if(!$this->beforeSetStatus($request)) { return false; }
            //判断是数组ID还是字符ID
            if(is_array($request['ids'])) {
                //数组ID
                $where['id'] = array('in',$request['ids']);
                $ids = implode(',',$request['ids']);
            } elseif (is_numeric($request['ids'])) {
                //数字ID
                $where['id'] = $request['ids'];
                $ids = $request['ids'];
            }

            $data = array(
                'status'        => $request['status'],
                'update_time'   => time()
            );

            $result = D($request['model'])->where($where)->data($data)->save();

            if($result) {
                //行为日志
                //api('Merchant/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
                //执行后操作
                if(!$this->afterSetStatus($result,$request)) { return false; }
                $this->setLogicSuccess('操作成功！'); return true;
            } else {
                $this->setLogicError('操作失败！'); return false;
            }
        }else{
            $this->setLogicError('此地址已经被用,不可删除！'); return false;
        }

    }
}
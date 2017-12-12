<?php

namespace Merchant\Logic;

/**
 * Class MemberLogic
 * User mr.zhou
 * @package Merchant\Logic
 * 用户管理
 */
class MemberLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['account'])){
            $param['where']['account']   =  likeArr($request['account']); // 账号
        }
        if(!empty($request['nickname'])){
            $param['where']['nickname']   =  likeArr($request['nickname']); // 昵称
        }
        if(!empty($request['invite_code'])){
            $param['where']['invite_code']   =  likeArr($request['invite_code']); // 邀请码
        }
        if(!empty($request['type'])){
            $param['where']['type']   =  $request['type']; // 邀请码
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Member')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('Member')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}
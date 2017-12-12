<?php

namespace Manager\Logic;

/**
 * Class AcademyLogic
 * @package Manager\Logic
 */
class CarLabelLogic extends BaseLogic {
    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['id'])) {
            //按管理员账号查询
            $param['where']['contract_id'] = $request['id'];
        }
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('CarLabel')->getList($param);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {

        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        if(!empty($request['agreement_number'])){
            $param['where']['agreement_number'] = $request['agreement_number'];
        }
        if(!empty($request['agreement_name'])){
            $param['where']['agreement_name'] = $request['agreement_name'];
        }
//        $param['where']['status'] = array('lt',9);
        $row = D('CarLabel')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        return $row;
    }

    /**
     * @param string $field_name 隐藏文本框name名称
     * @param string $index_value 默认选中的值
     * @param string $index_field 默认选中字段
     * @return string
     * 获取分类树状下拉菜单
     */
    function getSelect($field_name = '', $index_value = '', $index_field = 'id') {
        //状态
        $param['where']['status']   = array('lt',9);
        $parent_id = D('GoodsCategory')->where(array('parent_id'=>0))->getField('id',true);
        $parent_id = implode(',',$parent_id);
//        p($parent_id);die;
        $where['parent_id'] = array('in',$parent_id);
        $g_id = D('GoodsCategory')->where($where)->getField('id',true);
        $g_id = implode(',',$g_id);
//        p($g_id);die;
        $id = $parent_id . ','.$g_id;
//        $param['where']['id'] = array(array('in',$parent_id),array('in',$g_id));
        $param['where']['id'] = array('in',$id);
        $result = D('Manager/GoodsCategory')->getList($param);
        return api('Manager/Category/getSelect',array($result,$field_name,$index_value,$index_field));
    }

}
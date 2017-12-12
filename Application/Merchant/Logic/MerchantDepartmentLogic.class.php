<?php

namespace Merchant\Logic;

/**
 * Class MerchantDepartmentLogic
 * @package Merchant\Logic
 * 商家部门逻辑层
 */
class MerchantDepartmentLogic extends BaseLogic{

    /**
     * @param array $data
     * @return array
     * 处理提交数据 进行加工或者添加其他默认数据
     */
    protected function processData($data = array()) {
        $data['merchant_id'] =  $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        return $data;
    }

    /**
     * @param array $request
     * @return array
     * 获取部门列表
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $result = D('MerchantDepartment')->getList($param);

        //将数据转换成树状结构  调用分类api 生成操作html
        $tree_data = list_to_tree(api('Manager/Category/handleOperate2',array($result,'MerchantDepartment')));

        //分类模板
        $template = "<tr>
                        <td>{id}</td>
                        <td>{top_class}{department_name}</td>
                        <td>{operate}</td>
                    </tr>";

        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','department_name','operate')));
        //生成树状页面
        $html = api('Tree/toFormatTree');

        return array('list'=>$html);
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
        $result = D('MerchantDepartment')->getList($param);
        return api('Manager/Category/getSelect2',array($result,$field_name,$index_value,$index_field));
    }

    /**
     * @param array $request
     * @return bool
     * 删除分类前操作 验证是否该分类下存在文章
     */
    protected function beforeSetStatus($request = array()) {
        if($request['status'] == 9) {
            //判断给分类下是否存在文章
            $where['department_id'] = $request['ids'];
            $where['status']  = array('lt',9);
            $count = D('MerchantBindUser')->where($where)->count();
            if($count > 0) {
                $this->setLogicError('该部门下有绑定的用户账号，请先解绑再删除'); return false;
            }
        }
        return true;
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
        $row = D('MerchantDepartment')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //获取文件
        $row['icon'] = api('System/getFiles',array($row['icon']));
        return $row;
    }
}
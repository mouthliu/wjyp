<?php
namespace Merchant\Model;
/**
 * Class MerchantTemplateModel
 * @package Merchant\Model
 */
class MerchantTemplateModel extends BaseModel
{

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('template_name', 'require', '模板名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('template_name', '1,15', '模板名称不能超过15个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('d_id', 'require', '发货地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('b_id', 'require', '退货地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param 综合条件参数
     * @return array
     */
    function getList($param = array())
    {
        if (!empty($param['page_size'])) {
            $total = $this->where($param['where'])->count();
            $Page = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show = $Page->show();
        }

        $model = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size']) ? $model = $model->limit($Page->firstRow, $Page->listRows) : '';

        $list = $model->select();

        return array('list' => $list, 'page' => !empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow( $param = array())
    {
        $row = $this->where($param['where'])->find();
        return $row;
    }
    function findRowOne( $param = array())
    {
        $row = M('Template_list')->where($param['where'])->find();
        return $row;
    }
    function findRowPostOne( $param = array())
    {
        $row = M('EfPostage')->where($param['where'])->find();
        return $row;
    }

}
<?php
namespace Merchant\Model;
/**
 * 商家地址数据层
 * Class MerchantAddressModel
 * @package Merchant\Model
 */
class MerchantAddressModel extends BaseModel
{

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('contact_name', 'require', '联系人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('contact_name', '1,15', '不能超过15个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('address', 'require', '详细地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('receiver_cellphone', 'require', '手机号码不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('receiver_cellphone', '/^0?(13[0-9]|15[0-9]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/', '手机格式不正确', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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

}
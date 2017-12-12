<?php
namespace Api\Model;
/**
 * 地址模型层处理
 * Class AddressModel
 * @package Api\Model
 */
class AddressModel extends BaseModel {
    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array(
//        array('receiver', 'require', '收货人未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('receiver', '0,15', '收货人长度在0--15位', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
//        array('phone', 'require', '收货人手机不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('phone', '/^1[3|4|5|8][0-9]\d{4,8}$/', '手机号格式不正确', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('address', 'require', '详细地址未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array();
    
}
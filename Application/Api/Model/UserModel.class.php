<?php
namespace Api\Model;
/**
 * 会员模型层处理
 * Class UserModel
 * @package Api\Model
 */
class UserModel extends BaseModel {
    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array(
//        array('receiver', 'require', '收货人未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('receiver', '0,15', '收货人长度在0--15位', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
//        array('phone', 'require', '收货人手机不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('phone', '/^1[3|4|5|8][0-9]\d{4,8}$/', '手机号格式不正确', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('User', 'require', '详细地址未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('id_card_num', '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/', '身份证格式有误', self::EXISTS_VALIDATE, 'regex', self::MODEL_UPDATE),
        array('real_name', '/^[\x{4e00}-\x{9fa5}]+$/u', '请输入中文名字', self::EXISTS_VALIDATE, 'regex', self::MODEL_UPDATE),
//        array('email', '/^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/', '请输入正确的邮箱格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_UPDATE),

    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );
    
}
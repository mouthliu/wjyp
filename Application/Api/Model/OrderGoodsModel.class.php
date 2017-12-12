<?php
namespace Api\Model;
/**
 * 会员模型层处理
 * Class UserModel
 * @package Api\Model
 */
class OrderGoodsModel extends BaseModel {
    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array(
    );
    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
//        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

}
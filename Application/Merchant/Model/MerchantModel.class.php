<?php
namespace Merchant\Model;

/**
 * Class AdministratorModel
 * @package Merchant\Model
 * 管理员模型
 */
class MerchantModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
//        array('account', 'require', '账号未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('account', '/^[a-zA-Z]\w{0,39}$/', '账号不合法', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('account', '0,15', '账号长度在0--15位', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
//        array('account', 'checkUnique', '该账号已经存在', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH, array('account')),
//        array('password', 'require', '密码不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
//        array('password', '6,18', '密码长度在6--18位', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),

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
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('admin')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('admin')
                        ->field('admin.*')
                        ->where($param['where'])
                        ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {

        $row = $this->alias('admin')
                    ->field('admin.*')
                    ->where($param['where'])
                    ->find();

        return $row;
    }
}
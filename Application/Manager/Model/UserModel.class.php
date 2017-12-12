<?php
namespace Manager\Model;

/**
 * Class AdministratorModel
 * @package Manager\Model
 * 管理员模型
 */
class UserModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('phone', 'require', '手机号必须填写'),
        array('phone', '', '该手机号已经注册过',0,'unique',3),
        array('id_card_num', '', '该身份证已被注册',2,'unique',3),

    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (

        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
        array('password', 'md5', self::MODEL_INSERT, 'function'),
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
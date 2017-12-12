<?php
namespace Manager\Model;

/**
 * Class AdministratorModel
 * @package Manager\Model
 * 管理员模型
 */
class VouchersModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('type', 'require', '请选择代金券种类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('money', 'require', '请添加代金券面值', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('end_time', 'require', '请选择过期时间', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $total      = $this->alias('alias')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('alias')
            ->field('alias.*')
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

        $row = $this->alias('alias')
            ->field('alias.*')
            ->where($param['where'])
            ->find();

        return $row;
    }
}
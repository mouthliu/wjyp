<?php
namespace Manager\Model;

/**
 * Class AcademyModel
 * @package Manager\Model
 */
class AdsModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('name', 'require', '标题不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('width', 'require', '内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('height', 'require', '内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(

    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}
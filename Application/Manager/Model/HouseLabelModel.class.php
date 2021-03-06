<?php
namespace Manager\Model;

/**
 * Class AnnounceModel
 * @package Manager\Model
 */
class HouseLabelModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
//        array('title', 'require', '标题不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('content', 'require', '内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
////        array('logo', 'require', '宣传图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
////        array('ac_type_id', 'require', '类型不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
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
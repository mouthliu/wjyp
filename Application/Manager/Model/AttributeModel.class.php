<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 商品类型模型
 */
class AttributeModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (

    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
//        array('add_time', 'time', self::MODEL_INSERT, 'function'),
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
            ->field('admin.*,gt.type_name')
            ->where($param['where'])
            ->join(C('DB_PREFIX')."goods_type gt ON admin.type_id=gt.id",'left')
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
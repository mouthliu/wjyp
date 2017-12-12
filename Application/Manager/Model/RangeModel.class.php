<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询模型
 */
class RangeModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
     
    protected $_validate = array (
        array('min_rate', 'require', '平台使用费未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('min_rate', '0,1', '比例填写不正确', self::EXISTS_VALIDATE, 'between', self::MODEL_BOTH),
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
            ->field('admin.*,cate.name AS cname')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'goods_category cate ON cate.id = admin.range_id ',
            ))
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
            ->field('admin.*,cate.name AS cname')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'goods_category cate ON cate.id = admin.range_id ',
            ))
            ->where($param['where'])
            ->find();

        return $row;
    }
}
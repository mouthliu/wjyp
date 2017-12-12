<?php
namespace Merchant\Model;

/**
 * Class AdministratorModel
 * @package Merchant\Model
 * 管理员模型
 */
class OneBuyModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('goods_id','number','请选择商品',1),
        array('person_num','number','请填写需参与人数',1),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('bid_time', 'time', self::MODEL_BOTH, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('goods')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('goods')
            ->field('goods.*')
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

        $row = $this->alias('goods')
            ->field('goods.*')
            ->where($param['where'])
            ->find();

        return $row;
    }
}
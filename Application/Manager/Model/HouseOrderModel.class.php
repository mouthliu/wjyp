<?php
namespace Manager\Model;

/**
 * Class AdministratorModel
 * @package Manager\Model
 * 房产购订单模型
 */
class HouseOrderModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
//        array('car_name', 'require', '车型名称必须填写'),
//        array('car_name', '', '该车名称重复',0,'unique',3),
//        array('car_style_id', 'require', '请选择车型'),
//        array('brand_id', 'require', '请选择品牌'),
//        array('car_img', 'require', '请上传汽车封面图'),
//        array('all_price', 'require', '请填写全价'),
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
<?php
namespace Merchant\Model;

/**
 * Class AdministratorModel
 * @package Merchant\Model
 * 管理员模型
 */
class GroupBuyModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('end_time', 'require', '请选择结束时间', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
//        array('one_price', 'require', '请添加单买价', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
//        array('group_price', 'require', '请添加团购价', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
//        array('group_num', 'require', '请添加团购人数', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('type', 'require', '请选择拼单类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('welfare', '0,1', '团长福利0-1之间', self::EXISTS_VALIDATE, 'between', self::MODEL_BOTH),
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
            $total      = $this->alias('goods')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('goods')
            ->field('goods.*,g.goods_name,g.shop_price,g.goods_num')
            ->join(C(DB_PREFIX).'goods g ON goods.goods_id=g.id')
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
            ->field('goods.*,g.goods_name,g.shop_price,g.goods_num')
            ->join(C(DB_PREFIX).'goods g ON goods.goods_id=g.id')
            ->where($param['where'])
            ->find();

        return $row;
    }
}
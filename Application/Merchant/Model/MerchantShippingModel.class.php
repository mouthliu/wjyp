<?php
namespace Merchant\Model;

/**
 * Class AdministratorModel
 * @package Manager\Model
 * 管理员模型
 */
class MerchantShippingModel extends BaseModel {

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
            ->field('goods.id AS gid,goods.merchant_id,goods.is_default,goods.status AS g_status,goods.shipping_id,s.*')
            ->join(C("DB_PREFIX")."shipping AS s ON goods.shipping_id=s.id",'left')
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
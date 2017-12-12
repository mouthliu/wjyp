<?php
namespace Merchant\Model;

/**
 * Class AdministratorModel
 * @packageMerchant\Model
 * 管理员模型
 */
class AuctionModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('auct_name', 'require', '请填写拍卖名称'),
        array('auct_desc', 'require', '请填写描述'),
        array('goods_id','number','请选择商品',1),
//        array('goods_id', 'require', '请选择商品',self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('start_price', 'require', '请填写起拍价'),
        array('one_price', 'require', '请填写一口价'),
        array('leave_price', 'require', '请填写保留价'),
        array('add_price', 'require', '请填写加价幅度'),
        array('base_money', 'require', '请填写保证金'),

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
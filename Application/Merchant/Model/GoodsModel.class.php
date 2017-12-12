<?php
namespace Merchant\Model;

/**
 * Class AdministratorModel
 * @package Merchant\Model
 * 管理员模型
 */
class GoodsModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('goods_name', 'require', '商品名称必须填写'),
        array('goods_name', '', '商品名称重复',0,'unique',3),
        array('cat_id', 'require', '请选择商品分类'),
        array('brand_id', 'require', '请选择商品品牌'),
        array('market_price', 'require', '请填写市场价'),
        array('shop_price', 'require', '请填写本店售价'),
        array('goods_img', 'require', '请上传商品略缩图'),
        array('goods_num', 'require', '请填写商品库存'),
        array('merchant_template_id', 'require', '请选择运费模板'),
//        array('goods_typeid', 'require', '请选择商品类型,并填写对应属性'),
    );

    /**
     *
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('fresh_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
        array('merchant_id','getMerchantId', self::MODEL_INSERT, 'function'),
        array('merchant_name','getMerchantName', self::MODEL_INSERT, 'function'),
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
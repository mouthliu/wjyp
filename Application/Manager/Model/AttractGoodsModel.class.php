<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询模型
 */
class AttractGoodsModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('goods_name', 'require', '商品名称未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('merchant_id', 'require', '未选择商家', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_code', 'require', '商品条码未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_code', 'number', '商品条码格式不正确', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_specification', 'require', '产品规格未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('shop_price', 'require', '销售价格未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('settlement_price', 'require', '结算价格未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('market_price', 'require', '市场价未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_opinion', 'require', '商品入驻意见未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lead_opinion', 'require', '领导审核意见未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

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
        foreach($list as $k => $v){
            if(!empty($v['goods_img'])){
                $where['id'] = array('in',$v['goods_img']);
                $list[$k]['path'] = M('file')->where($where)->getField('path',true);
            }
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
//        if(!empty($row['goods_img'])){
//            $where['id'] =  array('in',$row['goods_img']);
//            $row['path'] = M('file')->where($where)->getField('path',true);
//            foreach($row['path'] as $k ){
//                $row['path'] = __ROOT__.$k;
//            }
//            p($row);
//        }

//        p($row);
        $row['goods_img'] = api('System/getFiles',array($row['goods_img']));
        return $row;
    }
}
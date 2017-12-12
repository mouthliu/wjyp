<?php
namespace Manager\Model;

/**
 * Class AdministratorModel
 * @package Merchant\Model
 * 管理员模型
 */
class MerchantDetailModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('ads_pic', 'require', '请上传图片', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('url', 'require', '请填写链接', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_BOTH, 'function')
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
            ->field('goods.*,m.merchant_name,m.logo')
            ->join(C('DB_PREFIX').'merchant m ON m.id = goods.merchant_id')
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
            ->where($param['where'])
            ->find();

        return $row;
    }
}
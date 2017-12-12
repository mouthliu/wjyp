<?php
namespace Manager\Model;

/**
 * Class ActionModel
 * @package Manager\Model
 * 行为信息 模型
 */
class FeePriceModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
//        array('use_id', '', '该协议编号已经被使用',0,'unique',3),
//        array('agreement_name', 'require', '补充协议名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('agreement_number', 'require', '补充协议编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('serve_sum', 'require', '平台服务费最终总计不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('festival_sum', 'require', '节庆费最终总计不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('name', 'require', '供应商签署人不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('deposit_sum', 'require', '保证金最终总计不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('contract', 'require', '请上传合同图片', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
         array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
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
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}
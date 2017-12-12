<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询模型
 */
class ContractModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
     
    protected $_validate = array (
        array('agreement_number', 'require', '请填写协议编号', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('agreement_number', '', '该协议编号已经被使用',0,'unique',3),
        array('first_name', 'require', '甲方名称未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('first_address', 'require', '甲方地址未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('first_linkman', 'require', '甲方联系人未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('first_contact', 'require', '甲方联系方式未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('first_e_mail', 'require', '甲方邮箱未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('first_e_mail', '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i', '甲方邮箱格式错误', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('party_name', 'require', '乙方名称未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('party_address', 'require', '乙方地址未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('party_linkman', 'require', '乙方联系人未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('party_contact', 'require', '乙方联系方式未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('party_e_mail', 'require', '乙方邮箱未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('party_e_mail', '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i', '乙方邮箱格式错误', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('start_time', 'require', '开始时间未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('end_time', 'require', '结束时间未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $total      = $this->alias('contract')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('contract')
            ->field('contract.*')
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

        $row = $this->alias('contract')
            ->field('contract.*')
            ->where($param['where'])
            ->find();

        return $row;
    }
}
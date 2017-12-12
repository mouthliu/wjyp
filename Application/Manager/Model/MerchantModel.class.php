<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询模型
 */
class MerchantModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('account', 'require', '请填写账户', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('account', '', '该账号已经被使用',0,'unique',3),
        array('merchant_name', 'require', '店铺名称未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('a_id', 'require', '请选择招商人员', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('merchant_name', '', '该店铺名称已经被使用',0,'unique',3),
        array('range_id', 'require', '请选择经营范围', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cates', 'require', '请选择店铺分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('brands', 'require', '请选择则经营品牌', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('countrys', 'require', '请选择商品产地', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('logo', 'require', '请上传店铺logo', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('province_id', 'require', '门店地址信息不完整', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('city_id', 'require', '门店地址信息不完整', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('area_id', 'require', '门店地址信息不完整', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('address', 'require', '门店地址信息不完整', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('real_name', 'require', '请填写真实姓名', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('phone', 'require', '请填写手机号', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('card_code', 'require', '身份证号码不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('business_license', 'require', '请上传营业执照', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_name', 'require', '请填写企业名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('enterprise_scope', 'require', '请填写经营范围', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_start_time', 'require', '请填写营业开始时间', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_number', 'require', '注册号不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_establish_time', 'require', '成立日期不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_fund', 'require', '请填写注册资金', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('enterprise_person', 'require', '法人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_type', 'require', '企业类型不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('enterprise_address', 'require', '请填写企业住所', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
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
            $total      = $this->alias('admin')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('admin')
            ->field('admin.*,cate.name AS cname')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'goods_category cate ON cate.id = admin.range_id ',
            ))
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

        $row = $this->alias('admin')
            ->field('admin.*,cate.name AS cname')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'goods_category cate ON cate.id = admin.range_id ',
            ))
            ->where($param['where'])
            ->find();

        return $row;
    }
}
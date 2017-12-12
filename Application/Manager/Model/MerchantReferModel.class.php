<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询模型
 */
class MerchantReferModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('a_id', 'require', '请选择招商人员', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('title', '0,90', '标题长度在0--90位', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
//        array('cate_id', 'require', '文章分类未选择', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('content', 'require', '文章内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('link', '/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', '连接地址非法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),

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
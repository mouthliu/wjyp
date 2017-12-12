<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询分类模型
 */
class GoodsCategoryModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('name', 'require', '分类名称未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('short_name', 'require', '分类简称未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('name', '', '该分类名称已经存在',0,'unique',3),
        array('name', '0,30', '名称长度在0--30位', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
//        array('link', '/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', '连接地址非法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('min_rate', '0,100', '比例填写不正确', self::EXISTS_VALIDATE, 'between', self::MODEL_BOTH),

    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('create_ip', 'get_client_ip', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
        array('update_ip', 'get_client_ip', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        $list  = $this->where($param['where'])->order($param['order'])->select();
        return $list;
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}
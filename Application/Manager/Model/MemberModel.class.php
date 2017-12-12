<?php
namespace Manager\Model;

/**
 * Class MemberModel
 * User zhouwei
 * @package Manager\Model
 * 用户
 */
class MemberModel extends BaseModel {

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
        foreach($list as $k=>$v){
            $list[$k]['invite'] = M('Relationship')->where(array('type'=>3,'parent_id'=>$v['id']))->count();
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

 
    function findRow($param = array()) {}
}
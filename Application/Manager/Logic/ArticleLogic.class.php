<?php

namespace Manager\Logic;

/**
 * Class ArticleLogic
 * @package Manager\Logic
 * 文章咨询 逻辑处理
 */
class ArticleLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取文章列表
     */
    function getList($request = array()) {
        //标题模糊查询
        if(!empty($request['title'])) {
            $param['where']['art.title']   = array('like','%'.$request['title'].'%');
        }
        //分类查询
        if(!empty($request['cate_id'])) {
            $param['where']['art.cate_id']   = $request['cate_id'];
        }
        $param['where']['art.status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Article')->getList($param);

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processData($data = array()) {
        if(empty($data['id'])) {
            $data['a_id'] = AID;
        }
        $data['content'] = $_POST['content'];
        return $data;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['art.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['art.status'] = array('lt',9);
        $row = D('Article')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        //获取封面文件
        $row['cover'] = api('System/getFiles',array($row['cover']));
        return $row;
    }

    /**
     * @param string $field_name 隐藏文本框name名称
     * @param string $index_value 默认选中的值
     * @param string $index_field 默认选中字段
     * @return string
     * 获取分类树状下拉菜单
     */
    function getSelect($field_name = '', $index_value = '', $index_field = 'id') {
        //状态
        $param['where']['status'] = 1;
        $result = D('ArticleCategory')->getList($param);
        return api('Manager/Category/getSelect', array($result, $field_name, $index_value, $index_field));
    }

    /**
     * @param array $request
     * @return bool
     */
    function move($request = array()) {
        //判断参数
        if(empty($request['ids'])) {
            $this->setLogicError('未选择要移动的文章！'); return false;
        }
        if(empty($request['cate_id'])) {
            $this->setLogicError('未选择目标分类！'); return false;
        }
        //创建修改参数
        $where['id']     = array('IN', $request['ids']);
        $data['cate_id'] = $request['cate_id'];
        //更新数据
        $result = D('Article')->where($where)->data($data)->save();
        if(!$result) {
            $this->setLogicError('移动出错！'); return false;
        }
        $this->setLogicSuccess('移动成功'); return true;
    }
}
<?php

namespace Manager\Logic;

/**
 * Class ArticleLogic
 * @package Manager\Logic
 * 商家咨询 逻辑处理
 */
class MerchantReferLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取文章列表
     */
    function getList($request = array()) {
        $id = getManagerId();
//        p($id);die;
        //标题模糊查询
        if(!empty($request['name'])) {
            $param['where']['admin.name']   = array('like','%'.$request['name'].'%');
        }
//        if(!empty($request['status']) || $request['status'] == '0') {
//            $param['where']['admin.status']   = $request['status'];
//        }else{
//            $param['where']['admin.status']   = array('lt',9);    //状态
//        }
        $param['where']['_string'] = "(admin.service_id = $id) OR (admin.attract_id= $id AND admin.status=1)";
//        $param['where']['admin.status']   = 1;
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('MerchantRefer')->getList($param);
        //处理经营范围
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
            $param['where']['admin.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['admin.status'] = array('lt',9);
        $row = D('MerchantRefer')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理头像
        $imgArr = array('product_pic','other_license','business_license');
        if($row['product_pic']){
            foreach(explode(',',$row['product_pic']) as $k=>$v){
                $row['product'][] = M('File')->field('path')->where("id={$v}")->find()['path'];
            }
        }
        if($row['other_license']){
            foreach(explode(',',$row['other_license']) as $k=>$v){
                $row['other'][] = M('File')->field('path')->where("id={$v}")->find()['path'];
            }
        }
        if($row['business_license']){
            $row['business_license'] = M('File')->field('path')->where("id={$row['business_license']}")->find()['path'];
        }
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

    /**
     * @param array $request
     * @return boolean
     * 更新前执行
     */
    public function beforeUpdate($request = array()) {

        //判断是否是拒绝认证
        if($request['status'] == '2'){
            //判断理由
            if(!$request['refuse_desc']){
                $this->setLogicError('请填写拒绝理由');return false;
            }
        }
        return true;
    }
    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        //判断是否是拒绝认证
        if($result && $request['status'] == '2'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 11;//会员推荐 11
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }

        return true;
    }



}
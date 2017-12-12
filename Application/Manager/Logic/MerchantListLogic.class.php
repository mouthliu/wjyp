<?php

namespace Manager\Logic;

/**
 * Class ArticleLogic
 * @package Manager\Logic
 * 商家咨询 逻辑处理
 */
class MerchantListLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取文章列表
     */
    function getList($request = array()) {
        //标题模糊查询
        if(!empty($request['name'])) {
            $param['where']['admin.name']   = array('like','%'.$request['name'].'%');
        }
        //审核状态查询
        if(!empty($request['act'])) {
            $param['where']['admin.status']   = array(array('neq',1),array('lt',9),'and');
        }else{
            $param['where']['admin.status']   = 1;
        }

        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Merchant')->getList($param);
        //处理经营范围
        foreach($result['list'] as $k=>$v){
            $range = '';
            foreach(explode(',',$v['range_id']) as $k1=>$v1){
                $range .= getName('GoodsCategory','name',$v1).'，';
            }
            $result['list'][$k]['range_id'] = rtrim($range,'，');
        }
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
        $row = D('Merchant')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理头像
        $imgArr = array('card_before','card_after','card_img','logo','business_license','food_license','health_license');
        foreach($row as $k=>$v){
            if(in_Array($k,$imgArr)){
                $row[$k] = api('System/getFiles', array($v));
//                $row[$k] = M('file')->field('path')->where("id={$v}")->find()['path'];
            }
        }
        if($row['brands']){
            //获取到所有【品牌
            $row['brand_list'] = M('GoodsBrand')->field("id,brand_name")->where("id IN ({$row['brands']})")->select();
        }
        if($row['countrys']){
            //获取国家列表
            $row['country_list'] = M('Country')->field("id,Country_name")->where("id IN ({$row['countrys']})")->select();
        }
        $range = '';
        //处理经营范围
//        foreach(explode(',',$row['range_id']) as $k1=>$v1){
//            $range .= getName('GoodsCategory','name',$v1).'，';
//        }
//        $row['range_id'] = rtrim($range,'，');
        $license_arr = json_decode($row['other_license'],true);
        if(!empty($license_arr)){
            foreach($license_arr as $k=>$v){
                $license_arr[$k]['license_pic'] = api('System/getFiles', array($v['license_pic']));
            }
            $row['other_license'] = $license_arr;
        }
        return $row;
    }

    function getRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['admin.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['admin.status'] = array('lt',9);
        $row = D('Merchant')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理头像
        $imgArr = array('card_before','card_after','card_img','logo','business_license');
        foreach($row as $k=>$v){
            if(in_Array($k,$imgArr)){
                $row[$k] = api('System/getFiles',array($v));
            }
        }

        $license_arr = json_decode($row['other_license'],true);
        foreach($license_arr as $k=>$v){
            $license_arr[$k]['license_pic'] = api('System/getFiles',array($v['license_pic']));
        }
        $row['other_license'] = $license_arr;

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
     * @return boolean
     * 更新前执行
     */
    public function beforeUpdate($request = array()) {
        //判断是否是拒绝认证
        if($request['status'] == '2'){
            //判断理由
            if(!$request['refuse_desc']){
                $this->setLogicError('请填写拒绝认证理由');return false;
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
            $data['type'] = 3;//商家审核类型 3
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
        }
        return true;
    }
//获取数据类别
    public function getArrayCates($pids,$cates = ''){
        $mod=M("GoodsCategory");
        $where['status'] = 1;
        $where['parent_id'] = $pids;
        $where['id'] = $cates ? array('IN',$cates) : array('gt','0');
        $list=$mod->where($where)->select();
        $data = array();
        //遍历
        if($list){
            foreach($list as $k=>$v){
                //$v['underCate'] 存放二级分类
                $v['underCate'] = $this->getArrayCates($v['id']);
                $data[]=$v;
            }
        }
        return $data;
    }

}
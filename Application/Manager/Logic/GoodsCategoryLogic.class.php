<?php

namespace Manager\Logic;

/**
 * Class GoodsCategoryLogic
 * @package Manager\Logic
 * 商品分类 逻辑处理
 */
class GoodsCategoryLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取商品分类列表
     */
    function getList1($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态

        //判断是否存在缓存  缓存在分类 修改或添加后清空
        //$result = S('GoodsCategory_Cache');
//        if(!$result) {
            $result = D('GoodsCategory')->getList($param);
//
//            //设置缓存
////            S('GoodsCategory_Cache', $result);
//        }
        //获取所有的顶级分类
        $list = D('GoodsCategory')->field("id")->select(array('where'=>array('parent_id'=>0)));
        foreach($list as $k=>$v){
            $onelist[] = $v['id'];
        }
        //获取到所有的分类名称
        foreach($result as $k=>$v){
            $namelist[$v['id']] = $v['name'];
        }
        foreach($result as $k=>$v){
            $result[$k]['parent_name'] = $namelist[$v['parent_id']]?$namelist[$v['parent_id']]:'顶级分类';
            $result[$k]['is_show'] = $v['status']==1?'<font color="green">启用</font>':'<font color="red">禁用</font>';
            //判断父id是否在顶级分类数组中
            if(in_Array($v['parent_id'],$onelist)){
                $result[$k]['hotBrand'] = 1;
            }
        }
        //将数据转换成树状结构  调用分类api 生成操作html
        $tree_data = list_to_tree(api('Manager/Category/handleOperateG',array($result,'GoodsCategory')));
        //处理时间

        // 获取某分类下的所有子分类

        //获取某分类的所有父分类
        //var_dump(api('Tree/getAllParent',array($result, 45)));

        //分类模板
        $template = "<tr>
                        <td><input type=\"checkbox\" name=\"ids[]\" value=\"{id}\" class=\"ids\"/></td>
                        <td>{id}</td>
                        <td>{short_name}</td>
                        <td><span class='cate{parent_id}'></span>{top_class}{name}</td>
                        <td class='parent'>{parent_name}</td>

                        <td >{min_rate}</td>
                        <td>{is_show}</td>
                        <td>{operate}</td>
                    </tr>";
        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','short_name','name','parent_name','min_rate','is_show','operate')));
        //生成树状页面
        $html = api('Tree/toFormatTree');
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        return array('list'=>$html);
    }

    function getList($request = array())
    {
        if($request['parent_id']){
            $param['where']['parent_id']   = $request['parent_id'];
        }else{
            $param['where']['parent_id']   = 0;
        }
        $param['where']['status']   = array('lt',9);
        $param['order']         = 'parent_id,sort DESC';    //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('GoodsCategory')->getList($param);
        //获取所有的顶级分类
        $list = D('GoodsCategory')->field("id")->select(array('where'=>array('parent_id'=>0)));
        foreach($list as $k=>$v){
            $onelist[] = $v['id'];
        }
        //获取到所有的分类名称

        foreach($result as $k=>$v){
            $p_name = getCatePath2('GoodsCategory',$v['id']);
            $result[$k]['parent_name'] = $p_name ? $p_name : '顶级分类';
            $result[$k]['is_show'] = $v['status']==1?'<font color="green">启用</font>':'<font color="red">禁用</font>';
            //判断父id是否在顶级分类数组中
            if(in_Array($v['parent_id'],$onelist)){
                $result[$k]['hotBrand'] = 1;
            }elseif(!in_Array($v['parent_id'],$onelist) && $v['parent_id'] !=0){
                $result[$k]['is_three'] = 1;
            }
            $num = M('GoodsCategory')->where("parent_id = {$v['id']} AND status=1")->count();
            $result[$k]['child_num'] = $num ? $num : '0';
        }
        //获取到当前分类下的子分类数量

        return $result;
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
        $param['where']['status']   = array('lt',9);
        $result = D('Manager/GoodsCategory')->getList($param);
        return api('Manager/Category/getSelect',array($result,$field_name,$index_value,$index_field));
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('GoodsCategory')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['cate_img'] = api('System/getFiles',array($row['cate_img']));
        return $row;
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }
    public function processData($data = array()){

        return $data;
    }
    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    function setStatus($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }
        $arr =  M($request['model'])->where(array('parent_id'=>array('in',$request['ids']),'status'=>array('neq','9')))->select();

        if($arr){
            $this->setLogicError('请先删除该分类下的子分类'); return false;
        }
        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

}
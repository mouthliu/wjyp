<?php
namespace Manager\Logic;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-12 0012
 * Time: 16:22
 * 城市相关 逻辑处理
 */
class RegionLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    public function getList($request = array()){
        //标题模糊查询
        if(!empty($request['region_name'])) {
            $param['where']['r.region_name']   = array('like','%'.$request['region_name'].'%');

        }
        if(!empty($request['type'])) {
            $param['where']['r.region_type']   = $request['type'];
        }else{
            $param['where']['r.region_type'] = 1;
        }
        $param['where']['r.parent_id'] = $request['id'] ? $request['id'] : '1';
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
//        $param['order'] = 'r.is_hot desc,r.sort desc';
        $result = D('Region')->getList($param);
        if($request['type'] == '3'){
            foreach($result['list'] as $k=>$v){
                $result['list'][$k]['street_num'] = M('Street')->where("parent_id={$v['id']}")->count();
            }
        }
        return $result;
    }

    /**
     * @param array $request
     * @return boolean
     * @return array
     * 返回一行数据
     */
    public function findRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['r.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('Region')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
    function street($request = array()){
        $param['where']['r.parent_id'] = $request['id'] ? $request['id'] : '1';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Street')->getList($param);
//        dump(D('Goods')->getLastSql());
        return $result;
    }


}
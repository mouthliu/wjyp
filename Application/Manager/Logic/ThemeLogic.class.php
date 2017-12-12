<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class ThemeLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }
//        $param['where']['goods.merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['where']['status'] = array("lt",9);
        $param['order'] = "sort DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Theme')->getList($param);

        //dump(D('Goods')->getLastSql()) ;
        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['t_status'] = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
        }
//        dump($result);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['goods.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array("lt",9);
        $row = D('Theme')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        //处理图片
        $row['theme_img'] = api('System/getFiles',array($row['theme_img']));
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('Theme')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }



    //时间插件所用到processData
    protected function processData($data = array()) {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time']);
        return $data;
    }

    function showGoods($request){
            if(!empty($request['goods_sn'])) {
                $param['where']['goods.goods_sn'] = $request['goods_sn'];
            }
            if(!empty($request['goods_name'])) {
                $param['where']['goods.goods_name'] = array("LIKE","%{$request['goods_name']}%");
            }
            if(!empty($request['id'])) {
                $param['where']['goods.theme_id'] = $request['id'];
            }else{
                $param['where']['goods.theme_id'] = array("gt",0);
            }
            $param['where']['goods.status'] = array('lt',9);//状态
//        $param['where']['goods.status'] = $request['status'];//状态
            $param['order'] = 'create_time DESC';//排序
            $param['page_size'] = C('LIST_ROWS'); //页码
            $param['parameter'] = $request; //拼接参数

            $result = D('Goods')->getList($param);

            //获取到主题名称
            foreach($result['list'] as $k=>$v){
                $result['list'][$k]['theme_name'] = M("Theme")->field('theme_name')->where("id={$v['theme_id']}")->find()['theme_name'];
            }

            return $result;

    }

    function addThemeGoods($request = array()){
        $param['where']['goods.status'] = array('lt',9);//状态
        if(!empty($request['merchant_name'])){
            $param['where']['merchant_name'] = array('LIKE','%'.$request['merchant_name'].'%');
        }
        if(!empty($request['goods_name'])){
            $param['where']['goods_name'] = array('LIKE','%'.$request['goods_name'].'%');
        }
        if(!empty($request['goods_sn'])){
            $param['where']['goods_sn'] = $request['goods_sn'];
        }
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Goods')->getList($param);
//        dump(D('Goods')->getLastSql());
        return $result;
    }


}
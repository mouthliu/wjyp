<?php

namespace Merchant\Logic;

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
        if(!empty($request['theme_id'])) {
            //按管理员账号查询
            $param['where']['goods.theme_id'] = $request['theme_id'];
        }else{
            $param['where']['goods.theme_id'] = array("gt",0);
        }
        $param['where']['goods.merchant_id'] = getMerchantId();
        $param['where']['goods.status'] = array('lt',9);//状态
//        $param['where']['goods.status'] = $request['status'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Goods')->getList($param);
        //获取到主题名称
        foreach($result['list'] as $k=>$v){
            $theme = M("Theme")->field('theme_name,theme_img,theme_desc,start_time,end_time')->where("id={$v['theme_id']}")->find();
            $result['list'][$k]['theme_name'] = $theme['theme_name'];
            $result['list'][$k]['theme_img'] = getPath($theme['theme_img']);
            $result['list'][$k]['theme_desc'] = $theme['theme_desc'];
            $result['list'][$k]['theme_start_time'] = date('Y-m-d',$theme['start_time']);
            $result['list'][$k]['theme_end_time'] = date('Y-m-d',$theme['end_time']);
            $apply = M('ThemeApply')->where("goods_id = {$v['id']} AND status = 0")->getField('id');
            $result['list'][$k]['is_apply'] = $apply ? '1' :'0';
        }

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

        $param['where']['goods.merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['where']['goods.status'] = array('lt',9);

        $row = D('Goods')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        //处理略缩图
        $row['goods_img'] = api('System/getFiles', array($row['goods_img']));

        return $row;
    }




}
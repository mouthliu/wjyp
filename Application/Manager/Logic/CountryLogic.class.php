<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class CountryLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }
        if(!empty($request['country_name'])) {
            //按管理员账号查询
            $param['where']['goods.country_name'] = array("like","%{$request['country_name']}%");
        }
        $param['where']['goods.status'] = array('lt',9);//状态
        $param['order'] = 'sort DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Country')->getList($param);

       // dump($result) ;

        foreach($result['list'] as $k=>$v){
            if(!empty($v['country_logo'])){
                $result['list'][$k]['country_logo'] = M('File')->field('path')->where("id={$v['country_logo']}")->find()["path"];

            }
            if(!empty($v['house_img'])){
                $result['list'][$k]['house_img'] = M('File')->field('path')->where("id={$v['house_img']}")->find()["path"];
            }
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


        $param['where']['goods.status'] = array('lt',9);
        $row = D('Country')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['country_logo'] = api('System/getFiles',array($row['country_logo']));
        $row['house_img'] = api('System/getFiles',array($row['house_img']));
//        dump($row);
        return $row;
    }








}
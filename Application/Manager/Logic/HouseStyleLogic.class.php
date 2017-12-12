<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class HouseStyleLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {

        $param['where']['alias.status'] = array('lt',9);//状态
        if(!empty($request['house_id'])) {
            //按管理员账号查询
            $param['where']['alias.house_id'] = $request['house_id'];
        }
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('HouseStyle')->getList($param);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['alias.id'] = $request['id'];


        } else {
            $this->setLogicError('参数错误！'); return false;
        }


        $param['where']['alias.status'] = array('lt',9);

        $row = D('HouseStyle')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理略缩图
        $row['house_style_img'] = api('System/getFiles',array($row['house_style_img']));
        $row['pictures'] = api('System/getFiles',array($row['pictures']));

//        dump($row);
        return $row;
    }







}
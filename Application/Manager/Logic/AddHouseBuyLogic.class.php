<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class AddHouseBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        $param['where']['alias.status'] = array('lt',9);//状态
        if(!empty($request['house_name'])) {
            //按管理员账号查询
            $param['where']['alias.house_name'] = array("LIKE","%{$request['house_name']}%");
        }
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('HouseBuy')->getList($param);
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

        $row = D('HouseBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理略缩图
        $row['house_img'] = api('System/getFiles',array($row['house_img']));
        $row['pictures'] = api('System/getFiles',array($row['pictures']));
//        dump($row);
        return $row;
    }

//    function processData($data = array())
//    {
//        $data['content'] = $_POST['content'];
//        return $data;
//    }





}
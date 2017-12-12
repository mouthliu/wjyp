<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 汽车购订单
 */
class CarOrderLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {

        $param['where']['alias.status'] = array('lt',9);//状态
        if(!empty($request['car_style_id'])) {
            //按管理员账号查询
            $param['where']['alias.car_style_id'] = $request['car_style_id'];
        }
        if(!empty($request['brand_id'])) {
            //按管理员账号查询
            $param['where']['alias.brand_id'] = $request['brand_id'];
        }
        if(!empty($request['car_name'])) {
            //按管理员账号查询
            $param['where']['alias.car_name'] = array("LIKE","%{$request['car_name']}%");
        }
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('CarOrder')->getList($param);
        return $result;
    }
    function processData($data = array())
    {
        $data['content'] = $_POST['content'];
        $data['car_desc'] = $_POST['car_desc'];
        return $data;
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
        $row = D('CarOrder')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理略缩图
//        $row['car_img'] = api('System/getFiles',array($row['car_img']));
//        $row['pictures'] = api('System/getFiles',array($row['pictures']));
//        $row['brand_path'] = getPath(getName('CarBrand','brand_logo',$row['brand_id']));
//        $row['style_path'] = getPath(getName('CarStyle','style_img',$row['car_style_id']));
//        dump($row);
        return $row;
    }

}
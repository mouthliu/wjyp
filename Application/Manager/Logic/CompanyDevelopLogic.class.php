<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class CompanyDevelopLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {



        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('CompanyDevelop')->getList($param);
        foreach($result['list'] as $k=>$v){
            switch($v['status']){
                case '2':
                    $result['list'][$k]['t_status'] = '已通过';
                    break;
                case '3':
                    $result['list'][$k]['t_status'] = '已拒绝';
                    break;
                default:
                    $result['list'][$k]['t_status'] = '待审核';
            }

        }
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

        $row = D('CompanyDevelop')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理略缩图
        $row['face_img'] = api('System/getFiles',array($row['face_img']));

//        dump($row);
        return $row;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processData($data = array()) {
        $data['content'] = $_POST['content'];
        return $data;
    }






}
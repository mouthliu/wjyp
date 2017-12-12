<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class GoodsBrandLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }

        $param['where']['goods.status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('GoodsBrand')->getList($param);

        //dump(D('Goods')->getLastSql()) ;

        foreach($result['list'] as $k=>$v){
            if(!empty($v['brand_logo'])){
                $result['list'][$k]['brand_logo'] = M('file')->field('path')->where("id={$v['brand_logo']}")->find()["path"];
            }

        }

       // dump($result);
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
        $row = D('GoodsBrand')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['brand_logo'] = api('System/getFiles',array($row['brand_logo']));
//        dump($row);
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('GoodsBrand')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }






}
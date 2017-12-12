<?php

namespace Manager\Logic;

/**
 * Class AcademyLogic
 * @package Manager\Logic
 */
class AdsListLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取广告列表
     */

    function getList($request = array()) {

        if(!empty($request['position'])){
            $param['where']['position'] = $request['position'];
        }

        $param['where']['status']   = array('lt',9);
        $param['order']             = 'position,sort DESC';
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Ads')->getList($param);


        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['position'] = M('AdPosition')->where(array('id'=>$v['position']))->getField('name');
        }
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['picture'] = M('file')->where(array('id'=>$v['picture']))->getField('path');
        }
        return $result;
    }

    function processData($data = array())
    {
        $data['status'] = 1;
        $data['content'] = $_POST['content'];
        return $data;
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

        $row = D('Ads')->findRow($param);

        $row['picture'] = api('System/getFiles',array($row['picture']));

        //获取已选择图片的长宽
        $ad_position = M('AdPosition')->where(array('id'=>$row['position']))->find();
        $row['width'] = $ad_position['width'];
        $row['height'] = $ad_position['height'];
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    function updatePosition($request){
        if(empty($request['id'])){
            $res = D('AdPosition')->create($request);
            if($res){
                $result = D('AdPosition')->add($request);
                if($result){
                    return true;
                }else{
                    return false;
                }
            }else{
                D('AdPosition')->getError();
                return false;
            }

        }else{
            $res = D('AdPosition')->create($request);
            if($res){
                $result = D('AdPosition')->where("id={$request['id']}")->save($request);
                if($result){
                    return true;
                }else{
                    return false;
                }
            }else{
                exit(D('AdPosition')->getError());
            }
        }
    }
}
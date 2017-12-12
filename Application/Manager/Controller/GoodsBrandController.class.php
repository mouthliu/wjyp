<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class GoodsBrandController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//
//    function getAddRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }

    function update()
    {
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('update');
        } else {
            $Object = D(CONTROLLER_NAME, 'Logic');
            $request = I('post.');
            $request = $this->setProcess($request);
            $result = $Object->update($request);
            if ($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * 设置传过来的post数组信息
     */
    function setProcess($request){
        $license_arr = array();
        // 处理其他证件的信息
        foreach($request['license_pic'] as $k=>$v){
            $license_arr[$k]['license_pic'] = $v;
            $license_arr[$k]['license_name'] = $request['license_name'][$k];
        }
        $other_license = json_encode($license_arr);
        $request['license_pic'] = $other_license ? $other_license : '';
        return $request;
    }

}

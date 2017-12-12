<?php

namespace Manager\Controller;

/**
 * Class AcademyController
 * @package Manager\Controller
 * 无界书院文章
 */
class AdsListController extends BaseController {
    public function getIndexRelation()
    {
        $this->assign('position_list',M('AdPosition')->where(array('status'=>array('eq',1)))->order('sort ASC')->select());
    }
    public function getAddRelation()
    {
        $this->assign('position_list',M('AdPosition')->where(array('status'=>array('eq',1)))->order('sort ASC')->select());
    }

    public function getUpdateRelation()
    {
        $this->assign('position_list',M('AdPosition')->where(array('status'=>array('eq',1)))->order('sort ASC')->select());
    }

    // 用于跳转位置列表
    function position(){
        //   $request = I('request.');
        $where['status'] = array('lt',9);
        $list = M('AdPosition')->where($where)->order('sort ASC')->select();
        foreach($list as $k=>$v){
            $list[$k]['adNum'] = M('Ads')->where("position={$v['id']} AND status=1")->count();
        }

        $this->assign('list',$list);
        $this->display('Ads/position');
    }
    //修改位置列表
    function updatePosition(){
        $request = I('request.');
        if(empty($request['model'])){
            $this->error('参数不足');return false;
        }
        $res = D('Ads','Logic')->updatePosition($request);
        if($res){
            $this->success("执行成功");
        }else{
            $this->error("执行失败");
        }

    }
    /**
     * 禁用操作
     */
    function pforbid()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 启用操作
     */
    function presume()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    function pdelete()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }
}

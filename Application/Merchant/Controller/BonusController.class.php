<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 优惠券控制器
 */
class BonusController extends BaseController {

    //红包封面列表
    function faceIndex(){

        $result = D('Bonus', 'Logic')->faceIndex(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error("暂无红包");
        }
        $this->display('faceIndex');
    }
    /**
     * 修改红包封面
     */
      function updateFace(){
          if($_GET['id']){
              $row = M('BonusFace')->where("id = {$_GET['id']}")->find();
              if($row){
                  $row['bonus_face'] = api('System/getFiles',array($row['bonus_face']));
              }
          }
          $this->assign('row',$row);
          $this->display('Bonus/face');
      }

    function doUpdateFace() {
        $request = I('request.');
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            D($model)->getError(); return false;
        }
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $result = D($model)->data($data)->add();

            if(!$result) {

                $this->error('新增时出错！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->error('您未修改任何值！'); return false;
            }
        }

        $this->success($data['id'] ? '更新成功！' : '新增成功！');

    }




}

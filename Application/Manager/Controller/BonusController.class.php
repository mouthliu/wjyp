<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 优惠券控制器
 */
class BonusController extends BaseController {

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

    /**
     * 红包封面
     */
    function face(){
        if(!empty($_GET['status'])) {
            //按管理员账号查询
            $param['where']['status'] = $_GET['status'];
        }else{
            $param['where']['status'] = array(array('gt',0),array('lt',9));
        }
        $param['order'] = 'sort DESC';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = I('request.'); //拼接参数
        $result = D('BonusFace')->getList($param);
//        dump(D('BonusFace')->getLastSql());
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['bonus_face'] = M('File')->where(array('id'=>$v['bonus_face']))->getField('path');
            if($v['send_money'] >= $v['total_money']){
                $result['list'][$k]['t_status'] = "已被领完";
            }else{
                $result['list'][$k]['t_status'] = '剩余金额'.($v['total_money']-$v['send_money']);
            }
        }
        $this->assign('page', $result['page']);
        $this->assign('list', $result['list']);
        $this->display('Bonus/face');
    }

    function update(){
        $request = I('request.');
        //获取广告
        $ads = M('Bonus')->where("bonus_face_id = {$request['id']} AND merchant_id = {$request['merchant_id']}")->select();
        foreach($ads as $k=>$v){
            $ads[$k]['bonus_ads'] = D('File')->where("id = {$v['bonus_ads']}")->getField('path');
        }
        $this->assign('ads_list', $ads);
        //获取到封面信息
        $face = M('BonusFace')->where("id = {$request['id']}")->find();
        $row = $face;
        $row['diff'] = $face['total_money']-$face['send_money'];
        $row['bonus_ads'] = M('file')->where(array('id'=>$row['bonus_ads']))->getField('path');
        $row['count'] = getBonusCount($row['total_money'],$row['min_val'],$row['max_val']);
        $this->assign('row',$row);
        $this->display('Bonus/update');
    }
    function doUpdate(){
        $request = I('request.');
        //判断是否是拒绝认证
        if($request['status'] == '3'){
            //判断理由
            if(!$request['refuse_desc']){
                $this->error('请填写拒绝认证理由');return false;
            }
        }
        $data['status'] = $request['status'];
        $data['refuse_desc'] = $request['refuse_desc'];
        $result = D('BonusFace')->where("id = {$request['id']}")->save($data);
        if($result){
            //判断是否是拒绝认证
            if($result && $request['status'] == '3'){
                //往拒绝表中加入数据
                $data['id_val'] = $request['id'];
                $data['type'] = 9;//红包审核类型 9
                $data['create_time'] = time();
                $data['action_admin'] = getManagerName();
                $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
                D('RefuseLog')->add($data);
            }
            $this->success('修改成功');return false;
        }else{
            $this->error('修改失败');return true;
        }
    }
    function record(){
        $param['where']['admin.bonus_id'] = $_GET['id'];
        $param['order'] = 'create_time DESC';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = I('request.'); //拼接参数
        $result = D('UserBonusLog')->getList($param);
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['bonus_face'] = M('File')->where(array('id'=>$v['bonus_face']))->getField('path');
        }
        $this->assign('page', $result['page']);
        $this->assign('list', $result['list']);

        $this->display('Bonus/record');
    }





}

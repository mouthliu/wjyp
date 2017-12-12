<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class UserLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['phone'])) {
            //按管理员账号查询
            $param['where']['admin.phone'] = $request['phone'];
        }
        if(!empty($request['nickname'])) {
            //按管理员账号查询
            $param['where']['admin.nickname'] = array('like',"%{$request['nickname']}%");
        }
        if(!empty($request['uid'])) {
            //按管理员账号查询
            $param['where']['admin.id'] = $request['uid'];
        }
        if(!empty($request['auth_status'])) {
            //按管理员账号查询
            $param['where']['admin.auth_status'] = $request['auth_status'];
        }
        if(!empty($request['comp_auth_status'])) {
            //按管理员账号查询
            $param['where']['admin.comp_auth_status'] = $request['comp_auth_status'];
        }
        $param['where']['admin.status'] = array('lt',9);//状态
        $param['order'] = 'update_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('User')->getList($param);
        //dump(D('admin')->getLastSql()) ;
        foreach($result['list'] as $k=>$v){
            $str = '';
            //查询bind表中用户绑定过的第三方
            $type = M('UserBind')->field('type')->where("user_id={$v['id']}")->select();

            foreach($type as $k1=>$v1){
                $str .= registerType($v1['type']).'，';
            }
            $result['list'][$k]['other_login'] = rtrim($str,'，');
        }

        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['admin.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['admin.status'] = array('lt',9);
        $row = D('User')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        $row['parent_name'] = M('User')->where(array('id'=>$row['parent_id']))->getField('phone');
        $row['hidden_parent_name'] = M('User')->where(array('id'=>$row['hidden_parent_id']))->getField('phone');
        $row['parent_alliance_merchant_name'] = M('Merchant')->where(array('id'=>$row['parent_alliance_merchant_id']))->getField('merchant_name');
        if($row['auth_status']=='3'){
            $row['refuse_desc'] = M('RefuseLog')->where("id_val = {$row['id']} AND type =1")->order('create_time DESC')->find()['refuse_desc'];
        }
        //处理头像
        $row['head_pic'] = api('System/getFiles',array($row['head_pic']));
        $row['back_id_card'] = D('File')->where("id={$row['back_id_card']}")->getField('path');

        $row['positive_id_card'] = D('File')->where("id={$row['positive_id_card']}")->getField('path');
        $row['comp_business_license'] = D('File')->where("id={$row['comp_business_license']}")->getField('path');
//        p($row);die;
        //统计代金券总数
        $row['vouchers_total'] = M('Vouchers')->where(array(
            'user_id'=>$row['id'],
            'status'=>1,
            'end_time'=>array('egt',time())
            ))->sum('money');
        //处理会员等级
        unset($where);
        $where['max_points'] = array('egt',$row['grow_point']);
        $where['min_points'] = array('elt',$row['grow_point']);
        $row['user_level_name'] = M('UserLevel')->where($where)->getField('level_name');
        //处理会员类型
        unset($where);
        $where['id'] = $row['rank_id'];
        $row['user_rank_name'] = M('UserRank')->where($where)->getField('rank_name');
        $row['auth_province_id'] = M('Region')->where(array('id'=>$row['auth_province_id']))->getField('region_name');
        $row['auth_city_id'] = M('Region')->where(array('id'=>$row['auth_city_id']))->getField('region_name');
        $row['auth_area_id'] = M('Region')->where(array('id'=>$row['auth_area_id']))->getField('region_name');
        $row['auth_street_id'] = M('street')->where(array('street_id'=>$row['auth_street_id']))->getField('street_name');
        $row['comp_province_id'] = M('Region')->where(array('id'=>$row['comp_province_id']))->getField('region_name');
        $row['comp_city_id'] = M('Region')->where(array('id'=>$row['comp_city_id']))->getField('region_name');
        $row['comp_area_id'] = M('Region')->where(array('id'=>$row['comp_area_id']))->getField('region_name');
        $row['comp_street_id'] = M('street')->where(array('street_id'=>$row['comp_street_id']))->getField('street_name');
        $row['auth_address'] = $row['auth_province_id'].'  '.$row['auth_city_id'].'  '.$row['auth_area_id'].'  '.$row['auth_street_id'];
        $row['comp_address'] = $row['comp_province_id'].'  '.$row['comp_city_id'].'  '.$row['comp_area_id'].'  '.$row['comp_street_id'];
//        p($row);die;
        return $row;
    }
    /**
     * @param array $request
     * @return boolean
     * 更新前执行
     */
   public function beforeUpdate($request = array()) {
       //判断是否是拒绝认证
//       if($request['auth_status'] == '3'){
//           //判断理由
//           if(!$request['auth_desc']){
//               $this->setLogicError('请填写拒绝认证理由');return false;
//           }
//       }
//
//       if($request['comp_auth_status'] == '3'){
//           //判断理由
//           if(!$request['comp_desc']){
//               $this->setLogicError('请填写拒绝认证理由');return false;
//           }
//       }
       //判断时候否跟之前的认证状态相同
       return true;
   }
    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        //判断是否是拒绝认证
        if($result && $request['auth_status'] == '3'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 1;//会员认证类型 1
            $data['create_time'] = time();//会员认证类型 1
            $data['action_admin'] = getManagerName();//会员认证类型 1
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
            $msg = '您的身份认证审核未通过，原因：'.$request['refuse_desc'];
            sendSystemMsg($msg,$request['id']);
        }elseif($result && $request['auth_status'] == '2'){
            $msg = '恭喜您的身份认证审核通过';
            sendSystemMsg($msg,$request['id']);
        }
        return true;
    }
    function levelLog($request){
        $param['where']['alias.user_id'] = $request['user_id'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('LevelLog')->getList($param);
        return $result;
    }
    function integralLog($request){
        $param['where']['alias.user_id'] = $request['user_id'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('IntegralLog')->getList($param);
        return $result;
    }
    function balanceLog($request){
        $param['where']['alias.user_id'] = $request['user_id'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('BalanceLog')->getList($param);
        return $result;
    }
    function vouchersTotal($request){
//        $param['where']['alias.status'] = 1;
        $param['where']['alias.user_id'] = $request['user_id'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Vouchers')->getList($param);
        return $result;
    }
    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('User')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }






}
<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class MerchantReportLogic extends BaseLogic {

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

        $result = D('MerchantReport')->getList($param);

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
        $row = D('MerchantReport')->findRow($param);

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
        $res = D('MerchantReport')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }

    /**
     * @param array $request
     * @return boolean
     * 更新前执行
     */
    public function beforeUpdate($request = array()) {
        //判断是否是拒绝认证
        if($request['status'] == '2'){
            //判断理由
            if(!$request['refuse_desc']){
                $this->setLogicError('请填写拒绝认证理由');return false;
            }
        }elseif($request['status'] == '1'){
            if(!$request['type']){
                $this->setLogicError('请选择奖励类型');return false;
            }
        }
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
        if($result && $request['status'] == '2'){
            //往拒绝表中加入数据
            $data['id_val'] = $request['id'];
            $data['type'] = 10;//举报商家类型 10
            $data['create_time'] = time();
            $data['action_admin'] = getManagerName();
            $data['refuse_desc'] = $request['refuse_desc'];//拒绝理由
            D('RefuseLog')->add($data);
            $msg = '由于'.$request['refuse_desc'].',举报商家未通过,感谢您的支持,我们会做得更好';
            sendSystemMsg($msg,$_POST['user_id']);
        }elseif($result && $request['status'] == '1'){
            if($request['bonus_val']){
                switch($request['type']){
                    case '1':
                        balanceChange($request['bonus_val'],9,'举报商家成功奖励红包'.$request['bonus_val'].'元',$request['user_id']);
                        break;
                    case '2':
                        integralChange($request['bonus_val'],1,'举报商家成功奖励积分'.$request['bonus_val'],$request['user_id']);
                        break;
                    case '3':
                        voucherChange($request['bonus_val'],1,1,'举报商家成功奖励红券'.$request['bonus_val'],$request['user_id']);
                        break;
                    case '4':
                        voucherChange($request['bonus_val'],2,1,'举报商家成功奖励黄券'.$request['bonus_val'],$request['user_id']);
                        break;
                    case '5':
                        voucherChange($request['bonus_val'],3,1,'举报商家成功奖励蓝券'.$request['bonus_val'],$request['user_id']);
                        break;

                }
            }

        }

        return true;
    }






}
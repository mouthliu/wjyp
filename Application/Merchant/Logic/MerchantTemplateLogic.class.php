<?php

namespace Merchant\Logic;

/**
 * Class MerchantTemplateLogic
 * @package Merchant\Logic
 */
class MerchantTemplateLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        $param['where']['merchant_id'] = getMerchantId();
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('MerchantTemplate')->getList($param);
        foreach($result['list'] as $k => $v){
            $shipping_address = M('MerchantAddress')->where(array('id'=>$v['d_id']))->find();
            if($shipping_address){
                $province_name = M('region')->where(array('id'=>$shipping_address['province_id']))->getField('region_name');
                $city_name = M('region')->where(array('id'=>$shipping_address['city_id']))->getField('region_name');
                $area_name = M('region')->where(array('id'=>$shipping_address['area_id']))->getField('region_name');
                $street_name = M('street')->where(array('street_id'=>$shipping_address['street_id']))->getField('street_name');
                $result['list'][$k]['shipping_address'] = $province_name.$city_name.$area_name.$street_name.$shipping_address['address'];
            }else{
                $result['list'][$k]['shipping_address'] = '';
            }

            $back_address = M('MerchantAddress')->where(array('id'=>$v['b_id']))->find();
            if($back_address){
                $province_name = M('region')->where(array('id'=>$back_address['province_id']))->getField('region_name');
                $city_name = M('region')->where(array('id'=>$back_address['city_id']))->getField('region_name');
                $area_name = M('region')->where(array('id'=>$back_address['area_id']))->getField('region_name');
                $street_name = M('street')->where(array('street_id'=>$back_address['street_id']))->getField('street_name');
                $result['list'][$k]['back_address'] = $province_name.$city_name.$area_name.$street_name.$back_address['address'];
            }else{
                $result['list'][$k]['back_address'] = '';
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
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('MerchantTemplate')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }


    public function processData($data = array())
    {
       $data['merchant_id'] = getMerchantId();
        return $data;
    }


    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */

    function setStatus($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();
        M('TemplateList')->where(array('tem_id'=>$request['ids']))->data($data)->save();
        M('NoPostage')->where(array('tem_id'=>$request['ids']))->data($data)->save();
        M('EfPostage')->where(array('t_id'=>$request['ids']))->data($data)->save();

        if($result) {
            //行为日志
            //api('Merchant/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

    /**
     * @desc:   新增和修改母模板数据
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-12 11:31
     * @param:  $request 获取到的数据
     */

    function update($request = array()) {
//
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        // die;
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $result = D($model)->data($data)->add();
            //自定义运费 新增数据
            if($request['is_postage'] == 1){
               for( $i=0; $i<count($request['trans_method']); $i++ ){
                   if(!empty($request['first_piece'][$i]) && !empty($request['first_price'][$i])
                   && !empty($request['another_piece'][$i]) && !empty($request['another_price'][$i])
                       && !empty($request['trans_method'][$i])){
                       $list['tem_id'] = $result;
                       $list['unit']   = $request['unit'];
                       $list['trans_method'] = $request['trans_method'][$i];
                       $list['first_piece']  = $request['first_piece'][$i];
                       $list['first_price']  = $request['first_price'][$i];
                       $list['another_piece']= $request['another_piece'][$i];
                       $list['another_price']= $request['another_price'][$i];
                       $list['create_time']  = time();

                       M('TemplateList')->data($list)->add();
                   }else{
                       continue;
                   }
               }

            } else {
                //卖家包邮 新增数据
                for( $i=0; $i<count($request['tran_method']); $i++ ){
                        $list['tem_id'] = $result;
                        $list['trans_method'] = $request['tran_method'][$i];
                        $list['create_time']  = time();
                        if($request['tran_method'][$i] == 1){
                            for( $j=0;$j<count($request['express_company']);$j++) {
                                $list['trans_company'] .= $request['express_company'][$j].',';
                            }
                        }elseif($request['tran_method'][$i] == 4){
                            for( $j=0;$j<count($request['logistics_company']);$j++) {
                                $list['trans_company'] .= $request['logistics_company'][$j].',';
                            }
                        }else{
                            $list['trans_company']='';
                        }
                        M('NoPostage')->data($list)->add();
                }
            }

            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            if($request['is_postage'] == 1){
                $datas = array('status'=>9,'update_time'=>time());
                M('TemplateList')->where(array('tem_id'=>$request['id']))->data($datas)->save();
                for( $i=0;$i<count($request['first_piece']); $i++ ){
                    if(!empty($request['first_piece'][$i]) && !empty($request['first_price'][$i])
                        && !empty($request['another_piece'][$i]) && !empty($request['another_price'][$i])
                        && !empty($request['trans_method'][$i])){
                        $list['tem_id'] = $request['id'];
                        $list['unit']   = $request['unit'];
                        $list['trans_method'] = $request['trans_method'][$i];
                        $list['first_piece']  = $request['first_piece'][$i];
                        $list['first_price']  = $request['first_price'][$i];
                        $list['another_piece']= $request['another_piece'][$i];
                        $list['another_price']= $request['another_price'][$i];
                        $list['create_time']  = $datas['update_time'];
                        $list['update_time']  = time();

                        M('TemplateList')->data($list)->add();
                    }
                }
            } else {
                //卖家包邮 修改数据
                $datas = array('status'=>9,'update_time'=>time());
                M('NoPostage')->where(array('tem_id'=>$request['id']))->data($datas)->save();
                for( $i=0; $i<count($request['tran_method']); $i++ ){
                    $list['tem_id'] = $request['id'];
                    $list['trans_method'] = $request['tran_method'][$i];
                    $list['create_time']  = $datas['update_time'];
                    $list['update_time']  = time();
                    if($request['tran_method'][$i] == 1){
                        for( $j=0;$j<count($request['express_company']);$j++) {
                            $list['trans_company'] .= $request['express_company'][$j].',';
                        }
                    }elseif($request['tran_method'][$i] == 4){
                        for( $j=0;$j<count($request['logistics_company']);$j++) {
                            $list['trans_company'] .= $request['logistics_company'][$j].',';
                        }
                    }else{
                        $list['trans_company']='';
                    }
                    M('NoPostage')->data($list)->add();
                }
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
//        return $data['id']? true : false;
    }

    /**
     * 添加 修改默认运费模板
     *
     * time:2017年11月30日09:16:00
     * user： 刘凯龙
     */

    public function defaultUpdate($request=array()){
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = M('template_list')->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $data['create_time']=time();
            $result = M('template_list')->data($data)->add();

            if(!$result) {

                $this->setLogicError('新增时出错！'); return false;
            }

            //行为日志
            api('Merchant/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $data['tem_id']=$request['template_id'];
            $data['update_time']=time();
            $result = M('template_list')->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！');return true;
    }


    /**
     * 查找一行数据
     *
     * time:2017年11月30日17:16:00
     * user： 刘凯龙
     */

    function findRowOne($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row =  D('MerchantTemplate')->findRowOne($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * 查找一行数据
     *
     * time:2017年11月30日17:16:00
     * user： 刘凯龙
     */

    function findRowPostOne($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row =  D('MerchantTemplate')->findRowPostOne($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
//
    /**
     * 设置运费模板的状态 软删除status 为9
     *
     * time:2017年11月30日17:16:00
     * user： 刘凯龙
     */

    public function setDefaultStatus($request = array())
    {
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );

        $result = M('Template_list')->where($where)->data($data)->save();

        if($result) {
            //行为日志
            //api('Merchant/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

    /**
     * 设置运费模板的状态 软删除status 为9
     *
     * time:2017年11月30日17:16:00
     * user： 刘凯龙
     */

    public function setPostStatus($request = array())
    {
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );

        $result = M('EfPostage')->where($where)->data($data)->save();

        if($result) {
            //行为日志
            //api('Merchant/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

    /**
     * 添加 修改默认运费模板
     *
     * time:2017年11月30日09:16:00
     * user： 刘凯龙
     */

    public function areaUpdate($request=array()){
        $request['trans_address'] = implode(',', $request['city_id']);
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = M('template_list')->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $data['create_time']=time();

            $result = M('template_list')->data($data)->add();

            if(!$result) {

                $this->setLogicError('新增时出错！'); return false;
            }

            //行为日志
            api('Merchant/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $data['tem_id']=$request['template_id'];
            $data['update_time']=time();
            $result = M('template_list')->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

//        return $data['id']? true : false;
        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！');return true;
    }

    /**
     * 添加 修改默认运费模板
     *
     * time:2017年11月30日09:16:00
     * user： 刘凯龙
     */

    public function postUpdate($request=array()){
        $request['ef_postage_area'] = implode(',', $request['city_id']);
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = M('ef_postage')->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $data['create_time']=time();
            $result = M('ef_postage')->data($data)->add();

            if(!$result) {

                $this->setLogicError('新增时出错！'); return false;
            }

            //行为日志
            api('Merchant/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $data['t_id']=$request['template_id'];
            $data['update_time']=time();
            $result = M('ef_postage')->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

//        return $data['id']? true : false;
        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！');return true;
    }

}
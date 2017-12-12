<?php
namespace Api\Logic;

/**
 * Class VouchersLogic
 * @package Api\Logic
 * 逻辑层  购物券模块
 *
 */
class VouchersLogic extends BaseLogic{

    /**
     * 获取购物券列表
     * @param array $request
     */
    public function vouchersList($request = array(),$user_id = 0){
        $mod = M('Vouchers');
        $list = $mod
            ->where("user_id = {$user_id} AND status=1")
            ->order('create_time DESC')
            ->select();
        if(!$list){
            apiResponse('1','暂无购物券');
        }
        $count = $mod->where('status = 1 AND user_id='.$user_id)->count();
        //根据购物券失效情况来分组
        foreach($list as $k=>$v){
            $v['logo'] = C('API_URL').'/Uploads/Vouchers/default.png';

            if($v['end_time'] < time() ){
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                $v['end_time'] = date('Y-m-d H:i',$v['end_time']);
                //将未使用的放一组
                $res['out'][] = $v;
            }else{
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                $v['end_time'] = date('Y-m-d H:i',$v['end_time']);
                $res['normal'][] = $v;
            }
        }
        apiResponse('1','获取数据成功',$res,$count);
    }

    /**
     * 购物券明细列表
     * @param array $request
     * @param int $user_id
     */
    public function vouchersLog($request = array(),$user_id = 0){
        $mod = M('VouchersLog');
        $list = $mod->alias('l')
            ->field('l.id AS log_id,l.act_type,l.reason,l.create_time,l.money')
            ->where("v.user_id = {$user_id}")
            ->join(C('DB_PREFIX').'vouchers v ON v.id=l.vouchers_id','LEFT')
            ->order('l.create_time DESC')
            ->page($request['p'],20)
            ->select();
        $count = $mod->where("user_id = {$user_id}")->count();
        if(!$list){
            apiResponse('0','暂无明细');
        }

        //根据月份分组
        foreach($list as $k=>$v){
            $time = date('Y-m',$v['create_time']);
            $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            $data_list[$time][] = $v;
        }
        $i = 0;
        foreach($data_list as $key=>$value){

            $res[$i]['time'] = $key;
            $res[$i]['list'] = $value;
            $i++;
        }
        apiResponse('1','获取成功',$res,$count);
    }

    /**
     * 添加记录
     * @param array $request
     * @param int $user_id
     */
    public function addLog($request = array(),$user_id = 0){
        $mod                 = M('VouchersLog');
        $data['act_type']    = $request['act_type'];
        $data['vouchers_id'] = $request['vouchers_id'];
        $data['reason']      = $request['reason'];
        $data['user_id']     = $user_id;
        $data['create_time'] = time();
        $data['money'] = $request['money'];
        $res = $mod->add($data);
        if($res){
            return false;
        }else{
            return true;
        }
    }

    public function addVoucher($request = array(),$user_id = 0){
        $mod = M('Vouchers');
        $data['money'] = $request['money'];
        $data['end_time'] = time()+C('DELAY_TIME');
        $data['user_id'] = $user_id;
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['type'] = $request['type'];
        $id = $mod->add($data);
        if($id){
            //进行记录
            $listData['act_type'] = 1;
            $listData['vouchers_id'] = $id;
            $listData['reason'] = '积分转余额赠送';
            $listData['money'] = $request['money'];
            $this->addLog($listData,$user_id);
            apiResponse('1','增加购物券成功');
        }else{
            apiResponse('0','添加购物券失败');
        }
    }
    /**
     * 使用购物券函数
     * 价格
     * user_id
     * type 购物券类型
     */
    function useVoucher($price,$user_id,$type){
        //首先判断该用户的购物券金额是否足够
        $mod = M('Vouchers');
        $mod->startTrans();
        $where['user_id'] = $user_id;
        $where['type'] = $type;
        $where['status'] = 1;//正常
        $where['end_time'] = array('gt',time());//未过期
        $all_money = $mod->where($where)->sum('money');
        if(floatval($all_money) < $price){
            apiResponse('0','此类代金券余额不足');
        }
        $my_voucher = $mod->field('id,money,use_money')->where($where)->order('end_time DESC')->select();
        //进行挑选使用代金券（根据过期时间使用）
        $need_money = 0;
        $use_array = array();

        foreach($my_voucher as $k=>$v){
            $need_money += $v['money'];
            //判断金额是否达到
            if($need_money >= $price){
                //如果超过，记录下这张券的id 和这张券需要多少钱
                $now_money = $v['money']-($need_money-$price);
                $use_array[] = array(
                    'id' => $v['id'],
                    'use_money'=>$v['use_money']+$now_money,
                    'money'=>$v['money']-$now_money,
                    'status'=> ($v['money']-$now_money) >0 ? 1 : 0
                );
//                $out_ids[] = $v['id'];
                break;
            }elseif($need_money < $price){
                $use_array[] = array(
                    'id' => $v['id'],
                    'use_money'=>$v['use_money']+$v['money'],
                    'money'=>0,
                    'status'=>9
                );
//                $out_ids[] = $v['id'];
            }
        }
        //将数组中的信息批量修改
        foreach($use_array as $k1=>$v1){
             $res = $mod->where("id={$v1['id']}")->save($v1);
             if(!$res){
                $mod->rollback();
                apiResponse('0','出现错误');
             }else{
                 //代金券记录表中增加记录
                 $data['money'] = $v1['use_money'];
                 $data['user_id'] = $user_id;
                 $data['act_type'] = 3;
                 $data['reason'] = '消费使用'.$v1['use_money'];
                 $data['create_time'] = time();
                 $data['vouchers_id'] = $v1['id'];
                 $res1 = M('VouchersLog')->add($data);
                 if(!$res1){
                     $mod->rollback();
                     apiResponse('0','记录出现错误');
                 }
             }
        }
        $mod->commit();
        return $use_array;
//        apiResponse('0','扣除代金券成功',$use_array);
    }
}
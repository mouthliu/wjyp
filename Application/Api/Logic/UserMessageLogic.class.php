<?php
namespace Api\Logic;

/**
 * Class UserMessageLogic
 * @package Api\Logic
 * 逻辑层  会员模块
 *
 */
class UserMessageLogic extends BaseLogic{
    /**
     * 消息中心
     * @param int $user_id
     */
    public function newMsg($request = array(),$user_id = 0){
        //1.获取最新一条通知消息
        $mod = M('Message');
        $where['user_id'] = array('IN',array('0',$user_id));
        $msg = $mod->alias('m')
            ->where($where)
            ->join(C('DB_PREFIX').'message_text t ON t.id=m.text_id')
            ->order('t.create_time DESC')
            ->limit(1)
            ->select()[0];
        $res['msg_title'] = $msg['content'] ? $msg['content']:'暂无通知消息';
        $res['msg_time'] = $msg['create_time'] ? date('Y-m-d H',$msg['create_time']):'';
        //获取未读消息
        $where['status'] = 0;
        $res['msg_count'] = $mod->where($where)->count();
        //2.获取最新公告
        $announce = M('Announce')->where('status = 1 AND parent_id=0')->order('create_time DESC')->limit(1)->select()[0];
        $res['announce_title'] = $announce['title'] ? $announce['title']:'暂无公告消息';
        $res['announce_time'] = $announce['create_time'] ? date('Y-m-d H',$announce['create_time']):'';
        $res['announce_count'] = M('Announce')->where('status = 1 AND parent_id=0')->count();
        //3.获取订单最新消息
        $order = M('OrderMessage')->where("user_id = {$user_id}")->order('create_time DESC')->limit(1)->select()[0];
        $res['order_title'] = $order['content'] ? $order['content']:'暂无订单消息';
        $res['order_time'] = $order['create_time'] ? date('Y-m-d H',$order['create_time']):'';
        $res['order_count'] = M('OrderMessage')->where("user_id = {$user_id} AND status=0")->count();
        //4.获取环信消息
        //获取用户消息
        if(empty($_POST['account_json'])){
            $res['chat_list'] = array();
        }else{
            $user_list = json_decode($_POST['account_json'],true);
            if(!$user_list){
                $res['chat_list'] = array();
            }else{
                $account_arr = array();
                foreach($user_list as $k=>$v){
                    $account_arr[] = $v['easemob_account'];
                    $new_arr[$v['easemob_account']]['msg_count'] = $v['msg_count'];
                    $new_arr[$v['easemob_account']]['last_content'] = $v['last_content'];
                    $new_arr[$v['easemob_account']]['last_time'] = $v['last_time'];
                }
                $e_where['easemob_account'] = array('IN',$account_arr);
                $list = M('User')
                    ->field("nickname,head_pic,id AS user_id,easemob_account")
                    ->where($e_where)
                    ->page($request['p'],10)
                    ->select();
                if(!$list){
                    $res['chat_list'] = array();
                    $msg = $request['p']==1 ? '暂无消息':'无更多消息';
                    apiResponse('0',$msg,$res);
                }
                //处理头像
                foreach($list as $k1=>$v1){
                    $list[$k1]['head_pic'] = D('File')->getOneFilePath($v1['head_pic']);
                    $list[$k1]['msg_count'] = $new_arr[$v1['easemob_account']]['msg_count'];
                    $list[$k1]['last_content'] = $new_arr[$v1['easemob_account']]['last_content'];
                    $list[$k1]['last_time'] = date('Y-m-d H:i',ceil($new_arr[$v1['easemob_account']]['last_time']/1000));
                }
                $res['chat_list'] = $list;
            }
        }
        apiResponse('1','获取成功',$res);
    }

    /**
     * 获取通知消息列表
     * @param array $request
     * @param int $user_id
     */
    public function msgList($request=array(),$user_id = 0){
        $mod = M('Message');
        $where['user_id'] = array('IN',array('0',$user_id));
        $list = $mod->alias('m')
            ->where($where)
            ->join(C('DB_PREFIX').'message_text t ON t.id=m.text_id')
            ->order('t.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        foreach($list as $k=>$v){
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        //设置消息已读
        $mod->where($where)->save(array('status'=>1));
        $count = $mod->where($where)->count();
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取公告消息列表
     * @param array $request
     * @param int $user_id
     */
    public function announceList($request=array(),$user_id = 0){
        $mod = M('Announce');
        $list = $mod
            ->where('status = 1 AND parent_id=0')
            ->order('create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }

        foreach($list as $k=>$v){
            unset($list[$k]['update_time']);
            unset($list[$k]['sort']);
            unset($list[$k]['parent_id']);
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        //设置消息已读
        $count = $mod->where('status = 1')->count();
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取订单消息列表
     * @param array $request
     * @param int $user_id
     */
    public function orderMsgList($request=array(),$user_id = 0){
        $mod = M('OrderMessage');
        $where['user_id'] = $user_id;
        $list = $mod
            ->where($where)
            ->order('create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        foreach($list as $k=>$v){
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        //设置消息已读
        $mod->where($where)->save(array('status'=>1));
        $count = $mod->where($where)->count();
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取消息提醒数
     */
    public function getTips($user_id = 0){
        if(!$user_id){
            return '0';
        }
        //通知消息
        $sysMsg = M('Message')->where("status = 0 AND user_id={$user_id}")->count();
        //订单消息
        $orderMsg = M('OrderMessage')->where("status = 0 AND user_id={$user_id}")->count();
        $num = ($sysMsg+$orderMsg)?''.($sysMsg+$orderMsg):'0';
        return $num;
    }
}
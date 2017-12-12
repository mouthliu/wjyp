<?php
namespace Api\Logic;

/**
 * Class UserLogic
 * @package Api\Logic
 * 逻辑层  会员模块
 *
 */
class UserCollectLogic extends BaseLogic{

    /**
     * 个人收藏列表
     * @param array $request
     * @param int $user_id
     */
    public function collectList($request = array(),$user_id = 0){
//        p($request);die;
        $mod = M('UserCollect');
        $param['where']['c.user_id'] = $user_id;
        $param['where']['type'] = empty($request['type'])?1:$request['type'];
        $param['where']['c.status'] = 1;

        switch($param['where']['type']){
            case 1:
                //表名
                $table = 'goods';
                //要查的字段
                $field = 'c.id AS collect_id,t.id AS goods_id,t.goods_img,t.goods_name,t.market_price,t.shop_price,t.integral,t.ticket_buy_id,t.country_id,t.sell_num';
                //图片的字段名
                $logo = 'goods_img';
                break;
            case 2:
                $table = 'merchant';
                $field = 'c.id AS collect_id,t.id AS mid';
                $logo = 'logo';
                break;
            case 3:
                $table = 'academy';
                $field = 'c.id AS collect_id,t.id AS aid,t.title,t.logo,t.collect_num,t.page_views';
                $logo = 'logo';
                break;
        }
        //根据类型连表查询
        $list = $mod->alias('c')
            ->field($field)
            ->join(C('DB_PREFIX').$table.' AS t ON c.id_val=t.id' ,'LEFT')
            ->where($param['where'])
            ->order('c.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        $count = count($list);
        foreach($list as $k=>$v){
            if($param['where']['type'] == 2){
                //获取封面商品
                $list[$k]['merchantFace'] = D('Merchant','Logic')->getFace($v['mid']);
            }elseif($param['where']['type'] == 1){
                //商品图片
                $list[$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
                //判断如果是外国显示图标
                if($v['country_id'] > 0){
                    $list[$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
                }else{
                    $list[$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
                }
                //如果是票券区商品显示可使用的票券
                if($v['ticket_buy_id']){
                    $list[$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
                }else{
                    $list[$k]['ticket_buy_discount'] = '0';
                }
            }else{
                $list[$k][$logo] = D('File')->getOneFilePath($v[$logo]);
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 添加到个人收藏
     * @param array $request
     * @param int $user_id
     */
    public function addCollect($request = array(),$user_id = 0){
        $mod = D('UserCollect');
        $data['user_id'] = $user_id;
        $data['type'] = $request['type'];
        $data['id_val'] = $request['id_val'];
        //判断收藏品存不存在
        switch($request['type']){
            case 1:
                $r = M("Goods")->where("id = {$request['id_val']}")->find();
                if(!$r){apiResponse('0','该收藏品不存在');};
                break;
            case 2:
                $r = M("Merchant")->where("id = {$request['id_val']}")->find();
                if(!$r){apiResponse('0','该收藏品不存在');};
                break;
            case 3:
                $r = M("Academy")->where("id = {$request['id_val']}")->find();
                if(!$r){apiResponse('0','该收藏品不存在');};
                break;
        }
        //判断这个商品是否收藏过
        $id = $mod->where($data)->getField('id');
        if($id){
            //设置状态为1
            $s = $mod->where("id={$id}")->save(array('status'=>1));
            if($s){
                //如果是文章收藏数加1
                if($request['type'] == '3'){
                    M('Academy')->where("id = {$request['id_val']}")->setInc('collect_num');
                }
                apiResponse('1','收藏成功');
            }else{
                apiResponse('0','已被收藏');
            }
        }
        $mod->checkCreate($data);
        $res = $mod->add();
        if($res){
            //如果是文章收藏数加1
            if($request['type'] == '3'){
                M('Academy')->where("id = {$request['id_val']}")->setInc('collect_num');
            }
            apiResponse('1','收藏成功');
        }else{
            apiResponse('0','收藏失败');
        }
    }

    /**
     * 删除收藏品
     * @param string $collect_ids
     */
    public function delCollect($collect_ids = ''){
        $mod = D('UserCollect');
        $where['id'] = array('IN',$collect_ids);
        $res = $mod->where($where)->delete();
        if($res){
            //如果是文章收藏数加1
            $ids = explode(',',$collect_ids);
            foreach($ids as $k=>$v){
                $type = M('UserCollect')->where("id = {$v}")->find();
                if($type['type'] == '3'){
                    M('Academy')->where("id = {$type['id_val']}")->setDec('collect_num');
                }
            }
            apiResponse('1','取消收藏成功');
        }else{
            apiResponse('0','取消收藏失败');
        }
    }
    /**
     * 取消收藏
     * @param string $collect_ids
     */
    public function delOneCollect($request = array(),$user_id = 0){
        $mod = D('UserCollect');
        $where['id_val'] = $request['id_val'];
        $where['type'] = $request['type'];
        $where['user_id'] = $user_id;
        $res = $mod->where($where)->save(array('status'=>9));
        if($res){
            //如果是文章收藏数减1
            if($request['type'] == '3'){
                M('Academy')->where("id = {$request['id_val']}")->setDec('collect_num');
            }
            apiResponse('1','取消收藏成功');
        }else{
            apiResponse('0','取消收藏失败');
        }
    }
}
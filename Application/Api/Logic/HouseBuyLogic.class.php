<?php
namespace Api\Logic;

/**
 * Class HouseBuyLogic
 * @package Api\Logic
 * 逻辑层  房产购 模块
 *
 */
class HouseBuyLogic extends BaseLogic{
    /**
     * 楼盘列表
     * @param array $request
     */
    public function houseList($request = array()){
        $order = 'create_time DESC';
        if(!empty($request['sort']) && $request['sort']=='1'){
            $order = 'create_time DESC';
        }
        if(!empty($request['integral_sort']) && $request['integral_sort']=='1'){
            $order = 'integral DESC';
        }elseif($request['integral_sort']=='2'){
            $order = 'integral ASC';
        }
        if(!empty($request['price_sort']) && $request['price_sort']=='1'){
            $order = 'min_price ASC';
        }elseif($request['price_sort'] == '2'){
            $order = 'min_price DESC';
        }
        //1.获取轮播
        $list['ads'] = D('Ads','Logic')->adsList(array('num'=>1,'position'=>'28'))[0];
        $mod = M('HouseBuy');
        $where['status'] = 1;
        $list['car_list'] = $mod->field('id AS house_id,house_name,house_img,lng,lat,min_price,max_price,now_num,developer')
            ->where($where)
            ->order($order)
            ->page($request['p'],10)
            ->select();
        if(!$list['car_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('0',$msg,$list);
        }
        $count = $mod->where($where)->count();

        foreach($list['car_list'] as $k=>$v){
            $list['car_list'][$k]['house_img'] = D('File')->getOneFilePath($v['house_img']);
            //根据经纬度算出当前距离
            $distance = getDistance($request['lat'],$request['lng'],$v['lat'],$v['lng']);
            $list['car_list'][$k]['distance'] = $distance ? $distance :'0';
        }
        if(!empty($request['distance_sort']) && $request['distance_sort']=='1'){
            //说明距离排序
            array_multisort(array_column($list['car_list'],'distance'),SORT_ASC,$list['car_list']);
        }
        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 楼盘详情
     */
    public function houseInfo($request = array(),$user_id = 0){
        $mod = M('HouseBuy');
        $where['id'] = $request['house_id'];
        $info_all = $mod->where($where)->find();
        if(!$info_all){
            apiResponse('0','获取失败');
        }
        unset($info_all['status']);
        unset($info_all['house_img']);
        unset($info_all['create_time']);
        $info['house_id'] = $request['house_id'];
        $info['banner'] = D('File')->getArrayFilePath(explode(',',$info_all['pictures']));
        $info['house_name'] = $info_all['house_name'];
        $info['province_name'] = getName('Region','region_name',$info_all['province_id']);
        $info['city_name'] = getName('Region','region_name',$info_all['city_id']);
        $info['area_name'] = getName('Region','region_name',$info_all['area_id']);
        $info['address'] = $info_all['address'];
        $info['lng'] = $info_all['lng'];
        $info['lat'] = $info_all['lat'];
        $info['min_price'] = ($info_all['min_price']/10000).'万';
        $info['max_price'] = ($info_all['max_price']/10000).'万';
        $info['sell_address'] = $info_all['sell_address'];
        $info['now_num'] = $info_all['now_num'];
        $info['link_man'] = $info_all['link_man'];
        $info['link_phone'] = $info_all['link_phone'];
        $arr = array();
        $arr[0]['key'] = '开盘';
        $arr[0]['value'] = $info_all['start_desc'].'';
        $arr[1]['key'] = '交房';
        $arr[1]['value'] = $info_all['finished_desc'].'';
        $arr[2]['key'] = '物业类别';
        $arr[2]['value'] = $info_all['server_type'].'';
        $arr[3]['key'] = '装修状况';
        $arr[3]['value'] = $info_all['fix_status'].'';
        $arr[4]['key'] = '住户数';
        $arr[4]['value'] = $info_all['person_num'].'';
        $arr[5]['key'] = '容积率';
        $arr[5]['value'] = $info_all['rongji_rate'].'';
        $arr[6]['key'] = '绿化率';
        $arr[6]['value'] = $info_all['green_rate']? $info_all['green_rate'].'%':'';
        $arr[7]['key'] = '停车位';
        $arr[7]['value'] = $info_all['stop_area'].'';
        $arr[8]['key'] = '产权年限';
        $arr[8]['value'] = $info_all['year_limit'] ? $info_all['year_limit'].'年' : '';
        $arr[9]['key'] = '开发商';
        $arr[9]['value'] = $info_all['developer'].'';
        $arr[10]['key'] = '预售许可';
        $arr[10]['value'] = $info_all['premit'].'';
        $arr[11]['key'] = '物业公司';
        $arr[11]['value'] = $info_all['service_company'].'';
        $arr[12]['key'] = '物业费';
        $arr[12]['value'] = $info_all['service_fee'] ? $info_all['service_fee'].'元/m²▪月' : '' ;
        $info['house_attr'] = $arr;

        //获取房产购评价
        unset($where);
        $count = M('HouseComment')->where(array('house_id'=>$info['house_id'],'status'=>1))->count();
        $info['comment_num'] = $count?''.$count:'0';
        $comment_list = M('HouseComment')->where(array('house_id'=>$info['house_id'],'status'=>1))
            ->field('user_id,price,lot,supporting,traffic,environment,label_str,pictures,content,create_time')
            ->order('create_time desc')
            ->limit(1)
            ->select();
        foreach($comment_list as $k => $v){
            $comment_list[$k]['comment_star'] = ''.number_format(($v['price']+$v['lot']+$v['supporting']+$v['traffic']+$v['environment'])/5,1);
            if($v['pictures']){
                $comment_list[$k]['pictures_arr'] = D('File')->getArrayFilePath(explode(',',$v['pictures']));
            }else{
                $comment_list[$k]['pictures_arr'] = array();
            }
            if($v['label_str']){
                $label_arr = explode(',',substr($v['label_str'],1,-1));
                $label = array();
                foreach($label_arr as $key =>$value){
                    $label_name = M('HouseLabel')->where(array('id'=>$value))->getField('house_label');
                    $label[$key]['label'] = $label_name?$label_name:"";
                }
                $comment_list[$k]['label_arr'] = $label;
            }else{
                $comment_list[$k]['label_arr'] = array();
            }
            $comment_list[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
            $user_info = M('User')->where(array('id'=>$v['user_id']))->find();
            if($user_info){
                $comment_list[$k]['nickname'] = $user_info['nickname']?$user_info['nickname']:"无界优品用户";
                $comment_list[$k]['head_pic']= D('File')->getOneFilePath($user_info['head_pic'],C('API_URL').'/Uploads/Member/default.png');
            }else{
                $comment_list[$k]['nickname'] = '无界优品用户';
                $comment_list[$k]['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
            }
        }
        $info['comment_new'] = $comment_list;

        $is_col = array();
        if(!empty($user_id)){
            //判断该商品是否被收藏
            $is_col = M('UserCollect')->where("user_id={$user_id} AND type=5 AND id_val={$request['house_id']} AND status = 1")->find();
        }
        $info['is_collect'] = empty($is_col)?'0':'1';

        apiResponse('1','获取成功',$info);
    }

    /**
     * 户型列表
     */
    public function houseStyleList($request = array()){

        $mod = M('HouseStyle');
        $where['house_id'] = $request['house_id'];
        if(!empty($request['w'])){
            $where['id'] = array('neq',$request['w']);
        }
        $where['status'] = 1;
        $list = $mod
            ->field("id AS style_id,style_name,house_style_img,pre_money,true_pre_money,one_price,all_price,integral,ticket_discount,sell_num,total")
            ->where($where)
            ->page($request['p'],10)
            ->select();
        if(!$list){
            $msg = $request['p']==1?'暂无户型数据':'无更多数据';
            if(!empty($request['w'])){
                return '';
            }else{
                apiResponse('0',$msg);
            }
        }
        $count = $mod->where($where)->count();
        $developer = getName('HouseBuy','developer',$request['house_id']);
        foreach($list as $k=>$v){
            $list[$k]['house_style_img'] = D('File')->getOneFilePath($v['house_style_img']);
            $list[$k]['developer'] = $developer;

            $phone = M('HouseBuy')->where("id = {$request['house_id']}")->getField('link_phone');
            $list[$k]['link_phone'] = $phone.'';
            $list[$k]['country_id'] = '85';
//            $list[$k]['country_logo'] = D('File')->getOnefilePath('7867');
            $list[$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
        }

        return array('list'=>$list,'count'=>$count);

    }

    /**
     * 户型详情
     * @param array $request
     * @param int $user_id
     */
    public function styleInfo($request = array(),$user_id = 0){
        $mod = M('HouseStyle');
        $where['id'] = $request['style_id'];
        $info = $mod->where($where)->find();
        if(!$info){
            apiResponse('0','获取失败');
        }
        unset($info['status']);
//        unset($info['house_style_img']);
        unset($info['create_time']);
        unset($info['update_time']);
        $info['banner'] = D('File')->getArrayFilePath(explode(',',$info['pictures']));
        $info['house_style_img'] = D('File')->getOneFilePath($info['house_style_img']);
        $info['tags'] = implode(' ',explode(',',$info['tags']));
        //楼盘地址
        $house = M('HouseBuy')->where("id={$info['house_id']}")->find();
        $info['house_name'] = $house ? $house['house_name'] : '';
        $address = getName('Region','region_name',$house['province_id']);
        $city_name = getName('Region','region_name',$house['city_id']);
        $area_name = getName('Region','region_name',$house['area_id']);
        $address = $house['address'];
        $info['house_address'] = $address.$city_name.$area_name.$address.'';
        //获取公司名
        $info['developer'] = $house['developer']?$house['developer']:'';

        //通楼盘其他户型
        $o_where['status'] = 1;
        $o_where['id'] = array('neq',$request['style_id']);
        $o_where['is_recommend'] = 1;
        $list = $mod
            ->field("id AS style_id,style_name,house_style_img,pre_money,true_pre_money,one_price,all_price,integral,ticket_discount,sell_num,total")
            ->where($o_where)
            ->select();
        if(!$list){
            apiResponse('0','获取成功,但无其他户型数据',$info);
        }
        $count = $mod->where($o_where)->count();
        $developer = getName('HouseBuy','developer',$info['house_id']);
        foreach($list as $k=>$v){
            $list[$k]['house_style_img'] = D('File')->getOneFilePath($v['house_style_img']);
            $list[$k]['developer'] = $developer;
            $phone = M('HouseBuy')->where("id = {$info['house_id']}")->getField('link_phone');
            $list[$k]['link_phone'] = $phone.'';
            $list[$k]['country_id'] = '85';
            $list[$k]['country_logo'] = D('File')->getOnefilePath('7867');
        }
        $info['other_style'] = $list;
        $info['cart_num'] = '0';
        if(!empty($user_id)){
            //判断购物车数量
            $num = M('Cart')->where("user_id = {$user_id}")->count();
            $info['cart_num'] = $num ? $num :'0';
        }
        //获取环信客服的账号信息
        $config = D('Config')->parseList();
        $customer_service_id = $config['customer_service_id'];
        $customer_service_info = M('User')->where(array('id'=>$customer_service_id))->find();
        $info['easemob_account'] = $customer_service_info['easemob_account']?$customer_service_info['easemob_account']:'';
        $info['server_name'] = $customer_service_info['nickname']?$customer_service_info['nickname']:'无界优品客服';
        if($customer_service_info){
            $path = M('File')->where(array('id'=>$customer_service_info['head_pic']))->getField('path');
            $info['server_head_pic'] =  $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        } else{
            $info['server_head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }


        apiResponse('1','获取成功',$info,$count);
    }

    /**
     * 获取评论
     */
    public function comment($request = array(),$limit=1){
        $phone = M('HouseBuy')->where("id = {$request['house_id']}")->getField('link_phone');
        $info['link_phone'] = $phone.'';
        $where['status'] = 1;
        $where['type'] = 2;
        $page = $request['p'] ? "{$request['p']},10" : $limit;
        //获取评论//获取楼盘下所有的户型
        $id = M('HouseStyle')->field('id')->where("house_id = {$request['house_id']}")->select();
        foreach($id as $k1=>$v1){
            $ids[] = $v1['id'];
        }
        $where['id_val'] = array('IN',$ids);
        $count = M('OtherComment')->where($where)->count();
        $comment = M('OtherComment')
            ->field('id AS comment_id,user_id,content,nickname,pictures,all_star,create_time')
            ->where($where)
            ->order('create_time DESC')
            ->page($page)
            ->select();
        if(!$comment){
            return false;
        }
        foreach($comment as $k=>$v){
            $comment[$k]['head_pic'] = D('File')->getOneFilePath(getName('User','head_pic',$v['user_id']));
            $comment[$k]['pictures'] = D('File')->getArrayFilePath(explode(',',$v['pictures']));
            $comment[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        $info['comment'] = $comment;
        return array('info'=>$info,'count'=>$count);

    }
}
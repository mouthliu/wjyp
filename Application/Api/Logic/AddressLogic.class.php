<?php
namespace Api\Logic;

/**
 * Class AddressLogic
 * @package Api\Logic
 * 逻辑层  收货地址模块
 *
 */
class AddressLogic extends BaseLogic{
    /**
     * 新增收货地址
     * @param array $request
     * @param int $user_id
     */
    public function addAddress($request = array(),$user_id = 0){
        $mod = D('Address');
        $data['user_id']     = $user_id;
        $data['receiver']    = $request['receiver'];
        $data['phone']       = $request['phone'];

        $data['province'] = $request['province'];
        $data['city'] = $request['city'];
        $data['area'] = $request['area'];
        $data['street']     = $request['street'];
        $data['province_id'] = $request['province_id'];
        $data['city_id'] = $request['city_id'];
        $data['area_id'] = $request['area_id'];
        $data['street_id']     = $request['street_id'];

        $data['address']     = $request['address'];
        $city_name = getName("Region",'region_name',$request['city_id']);
        $address = $request['province'].$request['city'].$request['area'].$request['street'].$request['address'];
        $getLA = $this->getLA($address,$city_name);
        $data['lng']         = $getLA['lng'];
        $data['lat']         = $getLA['lat'];
        $data['create_time'] = time();
        //模型层验证数据
        $mod->checkCreate($request);
        $res = $mod->data($data)->add();
        if($res){
            apiResponse('1','新增收货地址成功');
        }else{
            apiResponse('0','新增收货地址失败');
        }
    }

    /**
     * 收货地址列表
     * @param array $request
     * @param int $user_id
     */
    public function addressList($request = array(),$user_id = 0){
        $where['user_id'] = $user_id;
        $where['is_default'] = '0';
        $count = M('Address')->where($where)->count();
        $address_list = M('Address')
            ->where($where)
            ->field('id as address_id,receiver,phone,address,province,city,area,street,lng,lat,is_default')
            ->order('create_time desc')
            ->page($request['p'],10)
            ->select();
        $new_list['common_address'] = array();
        foreach($address_list as $k=>$v){
                unset($v['is_default']);
                $new_list['common_address'][] = $v;
        }
        $default = M('Address')->field('id as address_id,receiver,phone,address,province,city,area,street,lng,lat,is_default')->where("user_id = {$user_id} AND is_default=1")->find();
        if(empty($default)){
            $new_list['default_address'] = array(
                'address_id'=>'',
                'receiver'=>'',
                'phone'=>'',
                'address'=>'',
                'province'=>'',
                'city'=>'',
                'area'=>'',
                'street'=>'',
                'lng'=>'0',
                'lat'=>'0'
            );
        }else{
            unset($default['is_default']);
            $new_list['default_address'] = $default;
        }
        if(!$address_list){
            $message = $request['p']==1?'您暂未添加收货地址':'无更多收货地址';
            apiResponse('1',$message,$new_list,$count);
        }

        apiResponse('1','请求成功',$new_list,$count);
    }

    /**
     * 编辑收货地址
     * @param array $request
     * @param int $user_id
     */
    public function editAddress($request = array(), $user_id = 0){
        $mod = D('Address');
        $where['user_id'] = $user_id;
        $where['id'] = $request['address_id'];
        //模型层验证数据
        $mod->checkCreate($request);
        $res = $mod->where($where)->save($request);
        if($res){
            apiResponse('1','地址修改成功');
        }else{
            apiResponse('0','地址修改失败');
        }
    }

    /**
     * 设置默认收货地址
     * @param array $request
     * @param int $user_id
     */
    public function setDefault($request=array(),$user_id=0){
        $mod = D('Address');
        $where['user_id'] = $user_id;
        $where['id'] = $request['address_id'];
        $info = $mod->where($where)->save(array('is_default'=>1));
        if($info){
            $where2['user_id'] = $user_id;
            $where2['id'] = array('neq',$request['address_id']);
            $mod->where($where2)->save(array('is_default'=>0));
            apiResponse('1','设置成功');
        }else{
            apiResponse('0','设置失败');
        }
    }

    /**
     * 获取一条地址
     * @param array $request
     * @param int $user_id
     */
    public function getOneAddress($request=array(),$user_id=0){
        $mod = D('Address');
        $where['user_id'] = $user_id;
        $where['id'] = $request['address_id'];
        $info = $mod->where($where)->find();
        if($info){
            apiResponse('1','获取成功',$info);
        }else{
            apiResponse('0','获取地址失败');
        }
    }

    /**
     * 删除收货地址
     * @param array $request
     * @param int $user_id
     */
    public function delAddress($request=array(),$user_id=0){
        $mod = D('Address');
        $where['user_id'] = $user_id;
        $where['id'] = $request['address_id'];
        $info = $mod->where($where)->delete();
        if($info){
            apiResponse('1','删除成功');
        }else{
            apiResponse('0','删除失败');
        }
    }

    /**
     * 获取地区
     * @param int $parent_id
     */
    public function getRegion($request = array()){
        $mod = M('Region');
        if(empty($request['region_id'])){
            //默认
            $address['province_list'] = $mod->where("parent_id = 1")->field('id as region_id,region_name')->select();
            $address['city_list'] = $mod->where("parent_id = 2")->field('id as region_id,region_name')->select();
            $address['area_list'] = $mod->where("parent_id = 52")->field('id as region_id,region_name')->select();
        }else{
            $address['province_list'] = $mod->where("parent_id = 1")->field('id as region_id,region_name')->select();
            $where['parent_id'] = $request['region_id'];
            if(getName('Region','parent_id',$request['region_id']) == '1'){
                //判断父级属于省级别
                $address['city_list'] = $mod->where($where)->field('id as region_id,region_name')->select();
                $address['area_list'] = $mod->where("parent_id = {$address['city_list'][0]['region_id']}")->field('id as region_id,region_name')->select();
            }else if(getName('Region','parent_id',getName('Region','parent_id',$request['region_id']))=='1'){
                //判断父级属于市级别
                $pid = getName('Region','parent_id',$request['region_id']);
                $address['city_list'] = $mod->where("parent_id={$pid}")->field('id as region_id,region_name')->select();
                $address['area_list'] = $mod->where($where)->field('id as region_id,region_name')->select();
            }
        }

        if(!$address){
            apiResponse('1','暂无数据');
        }else{
            apiResponse('1','获取成功',$address);
        }
    }

    /**
     * 获取街道列表
     * @param int $area_id
     */
    public function getStreet($area_id = 0){
        $info = M('Street')->field('street_id,street_name')->where("parent_id={$area_id}")->select();
        if(!$info){
            apiResponse('1','暂无数据');
        }
        apiResponse('1','获取成功',$info,count($info));
    }

    /**
     * 逆编码获取经纬度
     */
    function getLA($address,$city=''){
        $ak = C('BD_AK');
        $url = "http://api.map.baidu.com/cloudgc/v1?ak={$ak}&address={$address}&city={$city}";
        $res = file_get_contents($url);
        $data = json_decode($res,true);
        if($data){
            return $data['result'][0]['location'];
        }else{
            return array('lat'=>'0','lng'=>'0');
        }

    }
}
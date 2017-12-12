<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 文章咨询 控制器
 */
class MerchantListController extends BaseController {
    /**
     * 禁用启用
     * User: Vernon
     * Date: 2017-12-1
     */
    public function forbidden(){
        $save['is_pass'] = $_GET['is_pass'];
        $save['update_time'] = time();
        $data = M('merchant')->where(array('id'=>$_GET['id']))->save($save);
        if($data){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 修改密码
     * User: Vernon
     * Date: 2017-12-1
     */
    public function setPwd(){
        if(IS_POST){
            if($_POST['newPassword'] != $_POST['newPasswords']){
                $this->error('两次密码输入不一致,请检查!');return false;
            }else{
                $save['password'] = MD5($_POST['newPassword']);
                M('merchant')->where(array('id'=>$_POST['id']))->save($save);
                $this->success('修改成功!');return true;
            }
        }
    }
    public function getUpdateRelation(){
        $range_id = M('Merchant')->where("id={$_GET['id']}")->getField('range_id');
        $cate_list = D('Goods','Logic')->getArrayCates('0',$range_id);
        $this->assign('cate_list',$cate_list);
        $contract = M('contract')->where(array('m_id'=>$_GET['id']))->find();
        $contract['start_time'] = date('Y-m-d H:i:s',$contract['start_time']);
        $contract['end_time'] = date('Y-m-d H:i:s',$contract['end_time']);
        if(!empty($contract['contract'])){
            $contract['contract'] = explode(',',$contract['contract']);
        }
        foreach($contract['contract'] as $k => $v){
            $contract['path'][] = M('file')->field('path')->where("id={$v}")->find()['path'];
        }
        $this->assign('contract',$contract);
    }
    //重写方法，跳转新增商家页面 Merchant/index?act=apply 这个方法跳转到商家生情列表 可以修改注册信息
    function add(){
        //获取分类信息
        //获取经营范围
        $range = M("GoodsCategory")->field("id,short_name")->where("parent_id=0")->select();
        $this->assign("range",$range);
        $brand_list = M("GoodsBrand")->field('id,brand_name')->where("status=1")->select();
        $country_list = M('Country')->field('id,country_name')->where("status=1")->order('sort DESC')->select();
        //获取招商人员列表
        $group = M('administrator')->where(array('group_id'=>6,'status'=>array('neq',9)))->field('id as a_id,account')->select();
        $this->assign('group',$group);
        $this->assign('country_list',$country_list);
        $this->assign("brand_list",$brand_list);
        $this->assign('cate_list',$this->getCateList('0'));

        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
        if(!empty($_GET['id'])){
            $row = M('merchant_refer')->where(array('id'=>$_GET['id']))->find();
            $this->assign('row',$row);
        }
        $this->display("Merchant/add");
    }
    function doAdd(){
        $countrys = implode(',',$_POST['countrys']);
        if(empty($countrys)){
            $this->error('请选择商品产地!');return false;
        }
        $Object = D(CONTROLLER_NAME, 'Logic');
        $request = I('request.');
        //处理数据
        $request['countrys'] = implode(',',$request['countrys']);
        $request['brands'] = implode(',',$request['brands']);
        $request['cates'] = implode(',',$request['cates']);
        //根据获取到的经营的顶级分类拼接
        $request['range_id'] = implode(',',$request['range_id']);
        //设置密码
        $request['password'] = md5('wjyp123456');
        if(!$request['id']){
            //注册环信
            $option = $this->_createEasemob();
            $request['easemob_account'] = $option['username'];
            $request['easemob_pwd'] = $option['password'];
        }
        // 处理其他证件的信息
        foreach($request['license_pic'] as $k=>$v){
            $license_arr[$k]['license_pic'] = $v;
            $license_arr[$k]['license_name'] = $request['license_name'][$k];
        }
        $other_license = json_encode($license_arr);
        $request['other_license'] = $other_license ? $other_license : '';
        $request['create_time'] = time();

        $result = D('Merchant')->create($request);
        if($result){
            $add['agreement_number'] = $_POST['agreement_number'];
            $add['agreement_name'] = $_POST['agreement_name'];
            $add['type'] = $_POST['type'];
            $add['first_address'] = $_POST['first_address'];
            $add['first_contact'] = $_POST['first_contact'];
            $add['first_linkman'] = $_POST['first_linkman'];
            $add['first_e_mail'] = $_POST['first_e_mail'];
            $add['first_name'] = $_POST['first_name'];
            $add['party_name'] = $_POST['party_name'];
            $add['party_address'] = $_POST['party_address'];
            $add['party_linkman'] = $_POST['party_linkman'];
            $add['party_contact'] = $_POST['party_contact'];
            $add['party_e_mail'] = $_POST['party_e_mail'];
            $add['start_time'] = strtotime($_POST['start_time']);
            $add['end_time'] = strtotime($_POST['end_time']);
            $add['contract'] = $_POST['contract'];
            $add['status'] = 1;
            $add['create_time'] = time();
            $data = D('Contract')->create($add);
            if($data){
                if(!empty($_POST['cates'])){
                    foreach ($_POST['cates'] as $k => $v){
                        if($v){
                            $adds['min_rate'] = $_POST['min_rate'][$k];
                            $min_rate = M('goods_category')->where(array('id'=>$v))->getField('min_rate');
                            if($adds['min_rate'] > $min_rate){
                                $this->error('比例不能超过分类设置的比例');return false;
                            }
                            $adds['cates_id'] = $v;
                            $adds['create_time'] = time();
                            $res = D('Range')->create($adds);
                            if($res){
                                $range[] = D('Range')->data($res)->add();
                            }else{
                                $this->error(D('Range')->getError());return false;
                            }
                        }
                    }
                }
                $merchant_id = M('merchant')->add($request);
                $where['id'] = array('in',implode(',',$range));
                $save['merchant_id'] = $merchant_id;
                D('Range')->where($where)->save($save);
                $data['merchant_id'] = $merchant_id;
                D('Contract')->data($data)->add();
                $this->success('添加成功','index');
            }else{
                $this->error(D('Contract')->getError());
            }
        }else{
            $this->error(D('Merchant')->getError());
        }
    }

    /**
     * @param int $pid
     * @return array
     */
    function getCateList($pid = 0){
        //根据传过来的pid获取子集
        $list = M("GoodsCategory")->field('id,name,short_name,parent_id')->where("parent_id={$pid} AND status=1")->select();
        if($list){
            $data = [];
            foreach($list as $k=>$v){
                $v['under'] = $this->getCateList($v['id']);
                $data[] = $v;
            }
        }
        return $data;
    }
    //获取分类函数
    function getChild(){
        if(empty($_POST['rid'])){
            echo "no";
            return false;
        }
        $list = $this->getCateList($_POST['rid']);
        echo json_encode($list);
    }
    /**
     * 创建环信账户
     */
    public function _createEasemob(){
        $option['username'] = time().rand(10000,99999).'';
        $option['password'] = time().'';
        while(1){
            $res = D('Easemob','Service')->createUser($option);
            if(empty($res['error'])){
                break;
            }
        }
        return $option;
    }

    /**
     * 修改(重写)
     */
    function updates()
    {
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelations($row['id']);
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('updates');
        } else {
            $Object = D(CONTROLLER_NAME, 'Logic');
            //处理数据
            $_POST['countrys'] = implode(',',$_POST['countrys']);
            $_POST['brands'] = implode(',',$_POST['brands']);
            $_POST['cates'] = implode(',',$_POST['cates']);
//            p($_POST['cates']);die;
            //根据获取到的经营的顶级分类拼接
            $_POST['range_id'] = implode(',',$_POST['range_id']);
            $result = $Object->update(I('post.'));
            $request = I('post.');

            if ($result) {
                if(!empty($_POST['cates'])){
                    $_POST['cates'] = explode(',',$_POST['cates']);

                    D('Range')->where(array('merchant_id'=>$_POST['id']))->delete();
                    foreach ($_POST['cates'] as $k => $v){
                        if($v){
                            $adds['min_rate'] = $_POST['min_rate'][$k];
                            $min_rate = M('goods_category')->where(array('id'=>$v))->getField('min_rate');
                            if($adds['min_rate'] > $min_rate){
                                $this->error('比例不能超过分类设置的比例');return false;
                            }
                            $adds['cates_id'] = $v;
                            $adds['merchant_id'] = $_POST['id'];
                            $adds['create_time'] = time();
                            M('range')->add($adds);
                        }
                    }
                }
                if($request['status']){
                    $this->afterProcess(array('model'=>$request['model'],'id'=>$request['id'],'field'=>'status','value'=>$request['status']));
                }
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * 重写
     * User: Vernon
     * @param:
     * @mode:post
     * Date: 2017-11-21
     */
    public function getUpdateRelations($request){
        //获取招商人员列表
        $group_where['group_id'] = 6;
        $group_where['status'] = array('neq',9);
        $group = M('administrator')->where($group_where)->field('id as a_id,account')->select();
        $this->assign('group',$group);
        //品牌列表
        $brand_list = M("GoodsBrand")->field('id,brand_name')->where("status=1")->select();
        $this->assign('brand_list',$brand_list);
        //商品产地列表
        $country_list = M('Country')->field('id,country_name')->where("status=1")->order('sort DESC')->select();
        $this->assign('country_list',$country_list);
        $address = M('merchant')->where(array('id'=>$request))->field('province_id,city_id,area_id,street_id')->find();

        //省
        $province = M('Region')->where(array('region_type'=>1))->field('id,region_name')->select();
        $this->assign('province',$province);
        //市
        $where['parent_id'] = $address['province_id'];
        $city = M('Region')->where($where)->field('id,region_name')->select();
        $this->assign('city',$city);
        //区
        $where_['parent_id'] = $address['city_id'];
        $area = M('Region')->where($where_)->field('id,region_name')->select();
        $this->assign('area',$area);
        //街道
        $wheres['parent_id'] = $address['area_id'];
        $street = M('street')->where($wheres)->field('street_id,street_name')->select();
        $this->assign('street',$street);

        $this->assign('cate_list',$this->getCateList('0'));
//        p($this->getCateList('0'));
        $range = M('range')->where(array('merchant_id'=>$request))->field('id as range_id,cates_id,min_rate,merchant_id')->select();
//        p($range);
        $this->assign('range',$range);
    }
}

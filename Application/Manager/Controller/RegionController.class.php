<?php
namespace Manager\Controller;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-12 0012
 * Time: 16:26
 * 城市相关 控制器
 */
class RegionController extends BaseController{

    function getIndexRelation(){ }
    public function street(){
        //获取到所有的主题

        $request = I('request.');
        $result = D('Region','Logic')->street($request);
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('Region/street');
    }
    public function addStreet(){
        $this->display('Region/add');
    }

    public function doAddStreet(){
        $parent_id = $_POST['parent_id'];
        foreach($_POST['street_name'] as $k=>$v){
            $street[] = array(
                'parent_id' => $parent_id,
                'street_name'=>$v,
                'create_time'=>time(),
                'create_ip'=>get_client_ip(),
            );
        }
        $res = D('Street')->addAll($street);
        if($res){
            //行为日志
            api('Manager/ActionLog/actionLog', array('add','Street',$res,AID));
            return $this->success("新增成功");
        }else{
            return $this->error("新增失败");
        }
    }
    //修改某个字段的值
    function setField(){
        $id = $_POST['id']?$_POST['id']:die('参数不足');
        $model = $_POST['model']?$_POST['model']:die('参数不足');
        $field = $_POST['field']?$_POST['field']:die('参数不足');
        $value = isset($_POST['value'])?$_POST['value']:die('参数不足');
        $data[$field] = $value;
        $data['update_time'] = time();
        $data['update_ip'] = get_client_ip();
        $res = D($model)->where("street_id={$id}")->save($data);
        if($res){
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit','Street',$res,AID));
        }
        echo $res ? '1' : '0';
    }
    public function delStreet(){
        $res = M('Street')->where("street_id = {$_GET['id']}")->delete();
        if($res){
            //行为日志
            api('Manager/ActionLog/actionLog', array('remove','Street',$res,AID));
        }
        return $res ? $this->success("删除成功") : $this->error("删除失败");
    }

}
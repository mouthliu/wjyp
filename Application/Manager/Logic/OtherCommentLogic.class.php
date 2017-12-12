<?php
namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class OtherCommentLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['type'])) {
            //按管理员账号查询
            $param['where']['goods.type'] = $request['type'];
        }
        $param['where']['goods.status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('OtherComment')->getList($param);

        //dump(D('Goods')->getLastSql()) ;

        foreach($result['list'] as $k=>$v){
            if(!empty($v['product_id'])){
                $result['list'][$k]['goods_attr'] = $this->getAttrGroup($v['goods_id'],$v['product_id']);
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
        $row = D('OtherComment')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['pictures'] = M('file')->field('path')->where("id={$row['pictures']}")->find()['path'];
        if($row['product_id']){
            $row['goods_attr'] = $this->getAttrGroup($row['goods_id'],$row['product_id']);
        }

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
        $res = D('OtherComment')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }

    function getAttrGroup($goods_id,$product_id){
        //获取到goods_attr属性值数组
        $attr = M('GoodsAttr')->where("goods_id={$goods_id}")->select();
        //创建属性值对应数组
        foreach($attr as $k1=>$v1){
            $attr_arr[$v1['id']] = $v1['attr_value'];
        }
        $goods_attr = M('Products')->field('goods_attr')->where("id={$product_id}")->find()['goods_attr'];
        $garr = explode('|',$goods_attr);
        foreach($garr as $k1=>$v1){
            $garr[$k1] = $attr_arr[$v1];
        }
        $garr = implode(',',$garr);
        return $garr;
    }




}
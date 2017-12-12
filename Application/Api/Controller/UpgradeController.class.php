<?php
namespace Api\Controller;
use Think\Controller;

class UpgradeController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 版本检测
     * 传递参数的方式：post
     * 需要传递的参数：
     */
    public function upgrade(){
        $result_data = array();
        $result_data['code'] = '1';
        $result_data['uri']  = C('API_URL').'/index.php/Api/Upgrade/memberUpgrade';
        $result_data['message'] = '用户端正式版';
        $result_data['name'] = 'V1.0';
        apiResponse('1','请求成功',$result_data);
    }

    public function memberUpgrade(){
        $file = "./Uploads/Version/wjyp.apk";
        header("Content-type: application/vnd.android.package-archive;");
        header('Content-Disposition: attachment; filename="' . 'wjyp.apk' . '"');
        header("Content-Length: ". filesize($file));
        readfile($file);
    }
}
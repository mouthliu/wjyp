<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller{
    function index() {
        $this->display('index');
    }
    /***
     * 下载安装包
     * cml
     */
    public function downLoad(){
        if($this->isWeiXin()){
            //echo "请点击右上角，在浏览器中打开!";
            $this->display('download');
        }else{
            $file = "./Uploads/Version/wjyp.apk";
            header("Content-type: application/vnd.android.package-archive;");
            header('Content-Disposition: attachment; filename="' . '无界优品.apk' . '"');
            header("Content-Length: ". filesize($file));
            readfile($file);
        }
    }

    /**
     * 判断是否为微信浏览器
     */
    public function isWeiXin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }
}
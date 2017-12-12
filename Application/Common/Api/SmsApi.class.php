<?php
namespace Common\Api;
vendor('aliyun-php-sdk-core.api_sdk.vendor.autoload');
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

// 加载区域结点配置
Config::load();

/**
 * Class SmsApi
 * @package Common\
 * 消息发送接口Api
 */
class SmsApi {

    public static function sendSms($signName,$templateCode,$phoneNumbers,$templateParam = null, $outId = null)
    {
        $accessKeyId = C('SMS_ACCESS_ID');//阿里云短信keyId
        $accessKeySecret = C('SMS_ACCESS_KEY');//阿里云短信keysecret

        //短信API产品名
        $product = "Dysmsapi";
        //短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region
        $region = "cn-hangzhou";

        //初始化访问的acsCleint
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new DefaultAcsClient($profile);

        $request = new SendSmsRequest;

        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($phoneNumbers);

        // 必填，设置签名名称
        $request->setSignName($signName);

        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数
        if($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }
        // 可选，设置流水号
        if($outId) {
            $request->setOutId($outId);
        }
        //发起访问请求
        $resp = $acsClient->getAcsResponse($request);
        //短信发送成功返回True，失败返回false
        if ($resp && $resp->Code == 'OK') {
            return true;
        } else {
            return array('error' => '发送失败');
        }
    }
//    public  static function sendSms22($receiver,$content){
//        $username = 'dacheng';		//用户账号
//        $password = 'nEv7iP98';		//密码
//        $mobile	 = $receiver;	//号码
//        $content = $content;		//内容
//        $content=iconv("UTF-8", "UTF-8", $content);
//        $dstime = '';		//为空代表立即发送  如果加了时间代表定时发送  精确到秒
//        $productid = '676767';		//内容
//        $xh = '';		//留空
//
//        $url='http://www.ztsms.cn:8800/sendXSms.do?username='.$username.'&password='.$password.'&mobile='.$mobile.'&content='.$content.'&dstime=&productid='.$productid.'&xh=';
//        $sms_content = file_get_contents($url);
//
//        $sms_response   = explode(",",$sms_content);  //处理返回信息
//        if($sms_response[0] != 1) {
//            return array('error' => '发送失败');
//        } else {
//            return true;
//        }
//    }

}
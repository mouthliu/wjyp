<?php

require_once "Alipay.Config.php";
require_once 'aop/AopClient.php';
require_once 'home/AlipayTradeService.php';
require_once 'home/AlipayTradePagePayContentBuilder.php';



/**
 * Class Alipay
 * liulei
 */
class Homepay{
    public $notify_url; //回调url

    public $return_url; //同步url

    public $out_trade_no;//订单编号

    public $total_amount;//订单金额

    public $signType;//签名方式
    /**
     * Alipay constructor.
     * @param $notify_url
     * 构造方法
     */
    public function __construct($notify_url,$out_trade_no,$total_amount,$signType,$return_url)
    {
        $this->notify_url   = $notify_url;
        $this->return_url   = $return_url;
        $this->out_trade_no = $out_trade_no;
        $this->total_amount = $total_amount;
        $this->signType     = $signType;
    }

    /**
     * 支付宝支付
     */
    public function homePay(){
        $config = [
            //应用ID,您的APPID。
            'app_id' => AlipayConfig::appId,

            //商户私钥
            'merchant_private_key' => AlipayConfig::rsaPrivateKey,

            //异步通知地址
            'notify_url' =>  $this->notify_url ,

            //同步跳转
            'return_url' =>  $this->return_url ,

            //编码格式
            'charset' => "UTF-8",

            //签名方式
            'sign_type'=>$this->signType,

            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

            //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
            'alipay_public_key' => AlipayConfig::alipayrsaPublicKey,
        ];
        //构造参数
        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody("pay");
        $payRequestBuilder->setSubject("pay");
        $payRequestBuilder->setTotalAmount($this->total_amount);
        $payRequestBuilder->setOutTradeNo($this->out_trade_no);
        $aop = new AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
//        apiResponse("110",$response);
        //输出表单
        return $response ;
    }

}
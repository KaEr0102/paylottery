<?php

namespace payment;

class Didapay extends Payment
{
    private $baseUrl = 'https://api.didapay.club';
    private $merchantNo = 'G5431';
    private $private_key = "-----BEGIN RSA PRIVATE KEY-----\nMIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBANngdNbj4/I9pZwRsyDDWJOHfxwNY5QFJNTG1v4z6qxO1SRGrOMH4LmSd23yptcr1L+JRIJZBzm9F9BhMGqBgwK+ScmPTcd7bR2yChzlRR44eZWQiuudnfEG0DPjKIqNjo3xVzD8hFLWNgZBHkBt9+ByExRStD3uvLuE9xItBbN7AgMBAAECgYBTLAYuSkyoGrRvwan55diYaO8zDEFpLhWDTGyiGbuKD4X6FSjGeillbe49gJYEKe1LOOF4SPgjKHZAy/kpj0stOiS2/tECceq5MOXIKPEcNVABFOOkPqY/jqRgLd5z5re39Nf9A9ySTPUAIEM8+cG1D0Z3NTDe5Tg/MfJ3V2Bk8QJBAP2sj/n3G9vW3SBwfwZEdXk+iOT9YE9Ok1P9hqvrRiH4C6vFEF136T+v/oCBP2l91O6onotwr14Y4SyQJFnFRC8CQQDb395f3N5jYvmzj/y4XyKx940PYASPQwC8dnjiMx5svOUSgrK0MX5tK5/GoveX4YzISU0oiNS4EJ8d6nIyNpZ1AkEArtL0TsL8kh+cObUN9dXMWAi+84GjlESEyIea/nSg2txFvtWLF7+CIoA6F3n7p8ouq2POEC9SzLi8xqd4Rd3rxQJAJNIdrFIRj/VAObjQKpQL/F+naL68pL0kv2rbnY3P94e+mNX4VULAmEmo7RvXeMDV0ais2i/n55co/lqHmy8XDQJBAJUeMC7gHSrlFMwVAGqIqu2Kj+2lVszGN9IOhpvgwKdrdqfkeCNnPcSg7KDrdxzubGHOScTMXBxogc0dgu8j6gY=\n-----END RSA PRIVATE KEY-----";

    /**
     * 获取签名
     * @param $params
     * @return string
     */
    public function getSign($params)
    {
        // 按键名ASCII字典序排序
        ksort($params);
        // 初始化一个空字符串用于拼接值
        $request_data = '';
        // 遍历排序后的数组，拼接值
        foreach ($params as $value) {
            if (!empty($value)){
                $request_data .= $value;
            }
        }
        //解析私钥
        $res = openssl_pkey_get_private($this->private_key);
        $content = '';
        //使用私钥加密
        foreach (str_split($request_data, 117) as $str1) {
            openssl_private_encrypt($str1, $crypted, $res);
            $content .= $crypted;
        }
        //编码转换
        $encrypted = base64_encode($content);
        return $encrypted;
    }

    /**
     * 充值
     * @return void
     */
    public function recharge($model)
    {
        $order = $model->toArray();
        $params = [
            'merchantNo'=>$this->merchantNo,
            'method'=>'UPI',
            'merchantOrderNo'=>$order['id_order'],
            'payAmount'=>$order['money'],
            'mobile'=>$order['phone'],
            'name'=>'username',
            'email'=>'pay@gmail.com',
            'notifyUrl'=> $this->callbackUrl('recharge'),
            'returnUrl'=> env('H5_URL').'/mian',
            'description'=>$order['id_order'],
        ];
        $params['sign']= $this->getSign($params);
        $res = $this->curl_post($params,$this->baseUrl.'/api/payin');
        return json_decode($res,true);
    }

    public function callbackUrl($type)
    {
        return  env('NOTICE_URL').'/callback/'.$type;
    }

}
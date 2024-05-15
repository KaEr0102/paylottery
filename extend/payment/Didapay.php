<?php

namespace payment;

class Didapay extends Payment
{
    private $baseUrl = 'https://apidevpo.didapay.club';
    private $merchantNo = 'M1000';
    private $privateKey = "-----BEGIN RSA PRIVATE KEY-----\MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALzGcqb7y4Rhm3tLGp2X5F1o4qtd2OdQH7UoFaoa8SElClTD7evxok0QX7TneFbIE8AiijcwOGdsLkzBuB7rSmve9+XozkVGZ6zqhpadParIAPuxmaqTM1g6u/Wo5G8eUi1+QOuG1+EfcaZ8C8qisDWERzHN9XrlKxScmncA83LFAgMBAAECgYBvruSkADIe5vRq1Dsx42w7C1OXWRV7fH7V2Zo/omLoXhanoadAQRvphfdpesxKY2Kz+HtXPVMRdQJLbQy2VjQXa5ifUQja6RMZZLiTCjDzykDy055oIEVSgD/VrqEFiLDRNVhGLE9+LKHIDmH5eLk0jZHH4jCpg0IA4fIlG4mOYQJBAN3M6gHS3wevR0xjHnSk5nObXbHaqvc5pCzJYKgBRDrz9hvc+JZZ6/hoieOdmOUrfAfzuA6n1KKDcnFxo+0P/V0CQQDZ4e8kT9OLNehytsKfnTKEmo/3AmfJQlvu7TpT7DtS4erwa8GguqG3J+2C/efuvhKEBAz75CLnTHwNcPhUzoyJAkEA3KSbaYMHoZJpQAEea/Ua14iINYSNLPEnc/JEd/0Cjg7hFijFFnSPvIbqHQdK8TdH5HU79UBZ0+0lbNsaspqEdQJAAQOVKlUYxfAVSdth4n5HyugxPVQMiZo+dUkzWUqjKAqXHlFSEF5t/D06VL67wpet3GFscguowezQMvvQnAxuCQJBALHxgmr8/u+UCM7+J9WlhnhaIFoXTkeztndAvyTdSQ9/vaJu0v79lrQZHGmwXP5A+1uGGL1Tif8NhY2U7CkzJCg=\n-----END RSA PRIVATE KEY-----";


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
        $res = openssl_pkey_get_private($this->privateKey);
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
    public function recharge($order)
    {
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
        dump($res);
    }

    public function callbackUrl($type)
    {
        return  env('H5_URL').'/callback/didapay/'.$type;
    }

}
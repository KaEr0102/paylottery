<?php

namespace payment;

class Didapay extends Payment
{
    private $baseUrl = 'https://apidevpo.didapay.club';
    private $merchantNo = 'M1000';
    private $private_key = "-----BEGIN RSA PRIVATE KEY-----\nMIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAN9ITJ/fWH9KR8ttebdF0zmhhJWV+9bGSaOoPbP8MlTDcqb33locF/KpBxkgbLxiqiQhevgAHtvDUEadvsStK1H5JJd2y460Lmms58R0qt1TyZ9tx/mfrijdv2obAXOBKwweeGk7qdqN0JcLGosZfpkXaGi/a2I6oWonhySNG86ZAgMBAAECgYEAkdQTj3r8vq4R8+/9RdDJ4uL1yAjcIWsCH2w7WHkHmkrIb/qFc47TqT3yD9wYiHVcMBrZyG2zuc53eJeAR83d8wRscocj2GIzsNjZzUEhYkoItrLMOH/I8dKb2Z85x/HrkbdYTf1qCXpxhvAUsdGKGfIbSyjhymgeGCWoUQ8KZ3ECQQD3c4BLYhFPTb00gNv7yMvGxIXYmecOsApoqJG+9vUVuS5bHR79ToV+E3/7Uyr17lItt3yJVhaxKJAEOB5yj5RdAkEA5v8Ls2EekqAAh0pfZYf1PaXEIv5KF8mLF0fsRoNrn6GGL3qfNCvQk3ASZ6Vwyc8RRN5KyTgmY+cyqyTUkuX/bQJBAKUJteGRMLZRxQWFhDL0A2U4oYSLcR3Mr8SJ2VsiXuf0MES4sXiErGggHVXEbHzGTK0NGdSHRG83/IWz4CrMNEkCQQCACmmK8c+HiOciFuiQF++pT0RL/VZGnzHZIsXmRByY7Gi70qWCvrKrtxiMmRjO1FeHLAyaQuSMxe/BC/ZEwvZ1AkAg4FgPWMu6PrpYMfpFH6U6sOpSI5wtw3ac6Rmu1rPAwewy8wBI0iIoJJ+yeVfqkK02AOWEY0XaasFmPdFItb/j\n-----END RSA PRIVATE KEY-----";

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
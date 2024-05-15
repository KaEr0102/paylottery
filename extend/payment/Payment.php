<?php

namespace payment;

class Payment
{

    public function curl_post($params,$url)
    {
        $log_params = is_array($params) ? json_encode($params,JSON_UNESCAPED_UNICODE) : $params;
        $log_file = runtime_path('log') . 'payment-' . date('Y-m-d') . '.log';
        file_put_contents($log_file, PHP_EOL . date('Y-m-d H:i:s') . " URL:{$url}    " . PHP_EOL . $log_params . PHP_EOL, FILE_APPEND);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:application/json;charset=UTF-8"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params,JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        }

        curl_close($ch);
        file_put_contents($log_file, $result . PHP_EOL, FILE_APPEND);
        return $result;
    }
}
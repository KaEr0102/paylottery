<?php

namespace app\controller;

use app\BaseController;
use app\model\Recharge;
use app\model\Users;

class Callback extends BaseController
{

    public function recharge()
    {
        $date = file_get_contents('php://input');
        try {
            $res = json_decode($date, true);
            $log_file = runtime_path('log') . 'callback-' . date('Y-m-d') . '.log';
            file_put_contents($log_file, PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL . "pay callback:    " . PHP_EOL . file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
            $recharge = Recharge::where('id_order', $res['merchantOrderNo'])->find();
            if (empty($recharge)) {
                return 'ORDER EMPTY';
            }

            if ($recharge->status == 0 && ($res['orderStatus'] == 'SUCCESS'||$res['orderStatus'] =='CLEARED'||$res['orderStatus'] =='ARRIVED')) {
                $recharge->status = 1;
                $recharge->save();
                //用户余额更改
                $user =Users::where('phone',$recharge->phone)->find();
                $user->money = $user->money+$recharge->money;
                $user->save();
            }
            die('SUCCESS');

        } catch (\Exception $e) {
            Log::error("支付代收回调失败：{$e->getMessage()} ({$e->getLine()}行,{$e->getFile()})");
            return 'FAIL';
        }
    }
}
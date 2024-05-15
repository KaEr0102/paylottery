<?php
namespace app\controller;

use app\BaseController;
use app\model\Recharge;
use payment\Didapay;

class Index extends BaseController
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V' . \think\facade\App::version() . '<br/><span style="font-size:30px;">16载初心不改 - 你值得信赖的PHP框架</span></p><span style="font-size:25px;">[ V6.0 版本由 <a href="https://www.yisu.com/" target="yisu">亿速云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ee9b1aa918103c4fc"></think>';
    }

    public function pay(){
        $model = Recharge::where('status',0)->where('url','<>','')->find();
        if ($model){
            $pay = new Didapay();
            $res = $pay->recharge();
            if ($res['status']==200){
                $model->url = $res['data']['paymentInfo'];
                $model->transaction_id =  $res['data']['platOrderNo'];
                $model->save();
                return json(['status'=>200,'msg'=>'获取支付url','data'=>$model->url]);
            }
        }else{
            return json(['status'=>201,'msg'=>'订单不存在']);
        }
    }

}

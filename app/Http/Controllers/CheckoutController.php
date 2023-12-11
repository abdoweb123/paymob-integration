<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $order = Order::create([
            'total_price'=>$request->total_price,
        ]);

        $PaymentKey = PayMobController::pay($order->total_price,$order->id);

        return view('paymob_iframe')->with('token',$PaymentKey);
    }


} //end of class

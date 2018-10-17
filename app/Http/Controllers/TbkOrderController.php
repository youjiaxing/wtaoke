<?php

namespace App\Http\Controllers;

use App\Models\TbkOrder;
use Illuminate\Http\Request;

class TbkOrderController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TbkOrder  $tbkOrder
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        $orders = $user->tbkOrders()->orderByDesc('create_time')->limit(50);

        $status = $request->input('status', null);
        $active = $status;
        switch ($status) {
            case 'paid':
                $orders = $orders->ordered();
                break;
            case 'canceled':
                $orders = $orders->canceled();
                break;
            case 'settled':
                $orders = $orders->settled();
                break;
            default:
                $active = 'all';
        }

        $orders = $orders->get();
        return view('orders.show', compact('orders', 'active'));
    }
}

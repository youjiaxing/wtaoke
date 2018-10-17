<?php

namespace App\Http\Controllers;

use App\Handlers\TbkRebateHandler;
use App\Models\User;
use Illuminate\Http\Request;

class TbkUserController extends Controller
{
    /**
     * 个人详情页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $user = \Auth::user();

        //TODO 此处需要来个缓存, 避免每次都查询数据库

        $orderedCount = $user->tbkOrders()->ordered()->count();
        $orderedCommission = $user->tbkOrders()->ordered()->sum('pub_share_pre_fee');
        $orderedCommission = app(TbkRebateHandler::class)->calcRebate($orderedCommission);

        $settledCount = $user->tbkOrders()->settled()->rebated()->count();
        $settledCommission = $user->tbkOrders()->settled()->rebated()->sum('rebate_fee');

        return view(
            'users.show',
            compact('user', 'orderedCount', 'orderedCommission', 'settledCommission', 'settledCount')
        );
    }

    public function moneyFlow(Request $request)
    {
        $user = \Auth::user();
        $query = $user->moneyFlows()->orderByDesc('created_at')->limit(100);

        $type = $request->input('type', 0);
        switch ($type) {
            case '1':
                $query = $query->income();
                break;
            case '2':
                $query = $query->expenditure();
                break;
            default:
                $type = 0;
        }

        //TODO 同样需要缓存

        $moneyFlows = $query->get();
        return view('users.money_flow', compact('moneyFlows', 'type'));
    }
}

@extends('layouts.app')

@section('title', '订单记录')

@section('content')
    <ul class="row top-navbar">
        <li class="{{ $active == 'all' ? "active" : "" }}"><a href="{{ route('wechat.tbkOrder.show') }}">全部</a></li>
        <li class="{{ $active == 'paid' ? "active" : "" }}"><a href="{{ route('wechat.tbkOrder.show', ['status' => 'paid']) }}">已付款</a></li>
        <li class="{{ $active == 'canceled' ? "active" : "" }}"><a href="{{ route('wechat.tbkOrder.show', ['status' => 'canceled']) }}">已失效</a></li>
        <li class="{{ $active == 'settled' ? "active" : "" }}"><a href="{{ route('wechat.tbkOrder.show', ['status' => 'settled']) }}">已结算</a></li>
    </ul>

    <ul class="list-group after-top-navbar">
        @forelse($orders as $order)
            <li class="list-group-item">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="text-left">订单号: {{ $order->trade_id }}</small>
                                </div>
                                <div class="col-xs-6">
                                    <small class="text-right">
                                        创建时间: {{ $order->create_time->toDateTimeString() }}</small>
                                </div>
                            </div>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-left">
                                <a href="#">
                                    <img class="media-object"
                                         src="{{ $order->pict_url ? : "http://temp.im/100x100" }}"
                                         alt="" width="50px" height="50px">
                                </a>
                            </div>
                            <div class="media-body">
                                <p class="text-nowrap text-muted item-title">{{ $order->item_title }}</p>
                                <span class="btn-xs btn-{{ orderStatus($order->tk_status, $order->is_rebate)[0] }}">
                                    {{ orderStatus($order->tk_status, $order->is_rebate)[1] }}
                                </span>

                                <span style="margin-left: 5px;">创建时间: {{ $order->create_time->toDateTimeString() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-xs-4">
                                支付金额: <strong>{{ moneyFormat($order->alipay_total_price) }}</strong>
                            </div>
                            <div class="col-xs-4">
                                佣金: <span class="money">¥ {{ moneyFormat($order->calcRebate()) }}</span>
                            </div>
                        </div>

                        {{--@if ($order->tk_status == )--}}
                    </div>
                </div>
            </li>
        @empty
            <p class="text-center text-muted">当前没有符合的订单记录</p>
        @endforelse

    </ul>
@stop
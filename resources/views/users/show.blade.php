@extends('layouts.app')

@section('title', '个人中心')

@section('content')
    <div class="row">
        <ul class="list-group">
            {{-- 用户基础数据 --}}
            <li class="list-group-item user-info">
                <div class="media">

                    <div class="media-left">
                        <img src="{{ $user->avatar }}" alt="" class="media-object img-circle" width="50px"
                             height="50px">
                    </div>
                    <div class="media-body">
                        <h4><strong>{{ $user->name }}</strong></h4>

                        <p>可提现余额: ¥ {{ moneyFormat($user->balance) }}</p>
                    </div>
                    <div class="media-right">
                        <a href="#" class="btn btn-sm btn-danger">提现</a>
                    </div>
                </div>
            </li>
            <a href="{{ route('wechat.user.moneyFlow') }}" class="">
                <li class="list-group-item">

                    <span class="glyphicon glyphicon-list-alt"></span> 收入明细
                    <span class="glyphicon glyphicon-arrow-right pull-right"></span>

                </li>
            </a>

            <li class="list-group-item income-info">
                <div class="row">
                    <div class="col-xs-6">
                        <p class="text-muted">待结算收入(元)</p>
                        <p><strong>{{ moneyFormat($orderedCommission) }}</strong></p>
                        <p class="text-danger">待结算</p>
                    </div>
                    <div class="col-xs-6">
                        <p class="text-muted">累计结算金额(元)</p>
                        <p><strong>{{ moneyFormat($settledCommission) }}</strong></p>
                        <p class="text-danger">已结算</p>
                    </div>
                </div>

            </li>

            <br>
            <a href="{{ route('wechat.tbkOrder.show') }}" class="">
                <li class="list-group-item">

                    <span class="glyphicon glyphicon-list-alt"></span> 全部订单明细
                    <span class="glyphicon glyphicon-arrow-right pull-right"></span>

                </li>
            </a>
            <li class="list-group-item order-info">
                <div class="row">
                    <div class="col-xs-6">
                        <p class="text-muted">待结算订单数</p>
                        <p><strong>{{ $orderedCount }}</strong></p>
                        <p class="text-danger">待结算</p>
                    </div>
                    <div class="col-xs-6">
                        <p class="text-muted">累计结算订单数</p>
                        <p><strong>{{ $settledCount }}</strong></p>
                        <p class="text-danger">已结算</p>
                    </div>
                </div>

            </li>

            <br>
            <li class="list-group-item">
                <span class="glyphicon glyphicon-ice-lolly" style="color: #2e78b4;"></span> 关于我们
            </li>
        </ul>
    </div>


@stop
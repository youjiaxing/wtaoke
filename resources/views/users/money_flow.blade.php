@extends('layouts.app')

@section('title', '钱包流向')

@section('content')
    <ul class="row top-navbar">
        <li class="{{ $type == 0 ? "active" : "" }}"><a href="{{ route('wechat.user.moneyFlow') }}">全部</a></li>
        <li class="{{ $type == 1 ? "active" : "" }}"><a href="{{ route('wechat.user.moneyFlow', ['type' => 1]) }}">收入</a></li>
        <li class="{{ $type == 2 ? "active" : "" }}"><a href="{{ route('wechat.user.moneyFlow', ['type' => 2]) }}">支出</a></li>
    </ul>
    <div class="bg-warning" id="top-msg">
        如果对流向有疑问, 请联系在线客服!
    </div>

    <ul class="list-group after-top-navbar">
        @forelse($moneyFlows as $flow)
            <li class="list-group-item">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="text-left">
                                         <span class="glyphicon glyphicon-time"></span> {{ $flow->created_at->toDateTimeString() }}
                                    </small>
                                </div>
                                <div class="col-xs-6">
                                    <small class="text-center">
                                        结余(元)
                                    </small>
                                </div>
                            </div>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="{{ $flow->isIncome() ? "text-success" : "text-danger" }}">
                                    <span class="glyphicon {{ $flow->isIncome() ? "glyphicon-plus-sign" : "glyphicon-minus-sign" }}"></span>
                                    {{ flowSubType($flow->sub_type) }}
                                    {{ $flow->amount }}
                                </div>

                            </div>
                            <div class="col-xs-6">
                                {{ $flow->balance }}
                            </div>
                        </div>
                        @if ($flow->comment)
                        <div class="comment">
                            <span class="glyphicon glyphicon-comment text-info"></span> <span class="">{{ $flow->comment }}</span>
                        </div>
                        @endif
                    </div>

                    {{--@empty($flow->comment)--}}
                    {{--<div class="panel-footer">--}}
                    {{--<span class="glyphicon glyphicon-comment text-info"></span>--}}
                    {{--{{ $flow->comment }}--}}

                    {{--</div>--}}
                    {{--@endif--}}
                </div>
            </li>
        @empty
            <p class="text-center text-muted">当前没有符合的收入记录</p>
        @endforelse

    </ul>
@stop
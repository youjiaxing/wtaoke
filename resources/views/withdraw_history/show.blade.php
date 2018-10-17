@extends('layouts.app')

@section('title', '提现记录')

@section('content')
    <ul class="list-group">
        @forelse($histories as $history)
            <li class="list-group-item">
                <div class="row">
                    <div class="col-xs-7 vertical-divider-right">
                        <p><span class="glyphicon glyphicon-time text-info"></span> 日期: {{ $history->withdraw_at ? $history->withdraw_at->toDateTimeString() : $history->created_at->toDateTimeString() }}</p>
                         <p><span class="glyphicon glyphicon-comment text-info"></span> 备注: {{ $history->comment ?: "无" }}</p>
                    </div>
                    <div class="col-xs-5">
                        <p class="pull-right btn-xs btn-{{ [0 => 'info', 1 => 'success', -1 => 'danger'][$history->status] }}">
                            {{ [0 => '申请中', 1 => '已提现', -1 => '取消'][$history->status] }}
                        </p>
                        <p>渠道: {{ $history->channel }} </p>

                        <p>金额: <span class="money">¥ {{ moneyFormat($history->amount) }}</span></p>
                    </div>
                </div>

            </li>
        @empty
            <p class="text-center text-muted">当前没有提现记录</p>
        @endforelse
    </ul>
@stop
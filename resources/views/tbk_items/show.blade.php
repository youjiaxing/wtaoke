@extends('layouts.app')

@section('title', '商品页面')

@section('content')
    <div class="row">
        <img src="{{ $data['pict_url'] }}" class="img-responsive main-img" alt="Image">
    </div>

    <div class="item-desc">
        <div class="row">
            <div class="col-xs-8 bg-white">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($data['has_coupon'])
                            <span class="btn btn-sm estimate-rebate">券后价</span>
                            <span class="money-big">¥ {{ $data['final_price'] }}</span>
                            <span class="text-muted">
                        {{ $data['user_type'] == 1 ? "天猫价" : "淘宝价" }}
                                ¥ {{ $data['price'] }}
                    </span>
                        @else
                            <div class="money">
                                ¥ {{ $data['final_price'] }}
                            </div>
                        @endif
                    </div>
                </div>

                <span class="badge badge-tmall">{{ $data['user_type'] == 1 ? "天猫" : "淘宝" }}</span>
                <span>{{ $data['title'] }}</span>
            </div>
            <div class="col-xs-4 bg-white {{ $data['has_coupon'] ? "has-coupon" : "" }}">
                <div class="text-center coupon-money">¥ {{ $data['coupon'] }}</div>
                <div class="text-center coupon-info">专享优惠券</div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-8">
                <div><span class="shop-mark">店铺</span> <span class="text-muted">{{ $data['nick'] }}</span></div>
                <div>30天内已售{{ $data['volume'] }}件</div>
            </div>
            <div class="col-xs-4">
                <span class="btn btn-xs estimate-rebate">预估佣金 ¥ {{ $data['rebate'] }}</span>
            </div>
        </div>

        <div class="row">
            <br>
            <div class="col-xs-12"><span class="glyphicon glyphicon-hand-down"></span> 复制以下文字到淘宝中打开购买</div>
            <div class="col-xs-12">
                <button id="copyCode" class="btn btn-danger" data-clipboard-target="#taokouling"
                        data-clipboard-action="copy">
                    一键复制淘口令
                </button>
                <textarea id="taokouling" class="form-control" name="" id="" rows="7" readonly>
复制本条消息到淘宝打开购买 {{ $data['tpwd'] }}
                    [{{ $data['title'] }}]
店铺: {{ $data['nick'] }}
                    💸售价: {{ $data['price'] }}({{ $data['has_coupon'] ? "可".$data['coupon_info'] : "无优惠券" }})
🛒30天销量: {{ $data['volume'] }}件
💸实际花费: {{ $data['final_price'] - $data['rebate'] }}
                </textarea>

            </div>
        </div>
    </div>



@stop

@section('script')
    <script>
        {{-- 在 app.js 中使用如下命令来引入 Clipboard--}}
        //     $.Clipboard = require('clipboard');

        var clipboard = new $.Clipboard('#copyCode');
        clipboard.on('success', function (e) {
            console.log(e);
            document.getElementById('copyCode').innerHTML = '淘口令复制成功, 请打开手机淘宝购买';
        });
        clipboard.on('error', function (e) {
            document.getElementById('copyCode').innerHTML = '复制失败，请长按手动复制';
        });
    </script>
@stop
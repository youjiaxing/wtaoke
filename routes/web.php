<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::any('/wechat', 'WeChatController@serve');

//Route::group(['middleware' => ['web', 'wechat.oauth:snsapi_userinfo']], function () {
//    Route::get('/wechat/user', function () {
//        $user = session('wechat.oauth_user');
//        return "<pre>".json($user, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)."</pre>";
//    })->name('wechat.user');
//});

Route::group(['middleware' => ['wechat.fake_user:local']], function () {
    Route::group(['middleware' => ['web', 'wechat.oauth', 'wechat.autologin']], function () {
        Route::get('/wechat/user', 'TbkUserController@show')->name('wechat.user');

        Route::get('/wechat/withdraw-history', 'WithdrawHistoryController@show')->name('wechat.withdrawHistory.show');

        Route::get('/wechat/order', 'TbkOrderController@show')->name('wechat.tbkOrder.show');
        Route::get('/wechat/moneyFlow', 'TbkUserController@moneyFlow')->name('wechat.user.moneyFlow');
    });
});


Route::get('/test/dynamic/{method}', 'TestController@dynamic')->middleware('ip.limit');

<?php

namespace App\Console\Commands;

use App\Models\TbkOrder;
use App\Models\User;
use App\Services\TbkApi\TbkApiService;
use App\Services\TbkThirdApi\Manager;
use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TopClient\request\TbkScOrderGetRequest;
use App\Console\Command;

class SyncTbkOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbk:sync-order {start?} {end?} {--span=} {--settle} {--notify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步淘宝客订单';

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var int 查询时长(最大1200秒)
     */
    protected $span;

    /**
     * @var int 是否是查询结算订单(每月20~25号批量查询上个月的)
     */
    protected $settle;

    /**
     * @var int 单次查询最大返回数量
     */
    protected $pageSize;

    /**
     * @var boolean
     */
    protected $notify;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Manager $manager)
    {
        $startTime = $this->argument('start');
        $endTime = $this->argument('end');
        $this->settle = $this->option('settle');
        $this->notify = $this->option('notify');
        $this->span = $this->option('span') ? intval($this->option('span')) : 1200;
        $this->span = max(60, min($this->span, 1200));

        // 参数修正
        if (is_null($startTime)) {
            if ($this->settle) {
                // 查询结算订单, 默认为查询上个月的所有已结算订单
                $startTime = date('Y-m-d 00:00:00', strtotime('first day of last month'));
                $endTime = date('Y-m-d 00:00:00', strtotime('first day of this month'));
            } else {
                // 查询最近的20分钟内的所有订单
                $startTime = date('Y-m-d H:i:00', time() - $this->span + 60);
                $endTime = date('Y-m-d H:i:s', strtotime($startTime) + $this->span);
            }
        } else {
            $startTime = date('Y-m-d H:i:s', strtotime($startTime) + 60);
            if (!$endTime) {
                $endTime = date('Y-m-d H:i:s', strtotime($startTime) + $this->span);
            } else {
                $endTime = date('Y-m-d H:i:s', strtotime($endTime));
            }
        }

        $this->debug(str_pad('-', 40, '-'));

        $this->manager = $manager;
        $this->pageSize = 100;
        $req = $this->getInitRequest();

        $pageNo = 1;
        $consecutiveFail = 0;
        while (strtotime($startTime) < strtotime($endTime)) {
            $this->debug("查询订单" . ($this->settle ? "(结算)" : "") ." $startTime - $endTime, 当前第 $pageNo 页");
            $response = $this->fetchOrder($req, $startTime, $pageNo);

            // 获取数据失败
            if ($response === false) {
                if (++$consecutiveFail >= config('taobaotop.order_get.fail_retry.count')) {
                    $this->warn("订单连续查询失败达到 {$consecutiveFail} 次, 本次查询中止!");
                    break;
                } else {
                    $this->debug("订单查询" . ($consecutiveFail > 1 ? "连续" : "") . "失败 {$consecutiveFail} 次, 准备开始下一次尝试");
                    sleep(config('taobaotop.order_get.fail_retry.interval'));
                }
                continue;
            } else {
                $consecutiveFail = 0;
                $pageNo = 1;
                $count = count($response);
                if ($count > 0) {
                    $this->debug("成功获取 $count 条订单数据, 准备同步到数据库.");
                } else {
                    $this->debug("没有新的订单数据.");
                }
                $this->syncToDb($response);
            }


            if (count($response) == $this->pageSize) {
                $pageNo++;
            } else {
                $startTime = date('Y-m-d H:i:s', strtotime($startTime) + $this->span);
                $pageNo = 1;
            }

            if (strtotime($startTime) < strtotime($endTime)) {
                sleep(config('taobaotop.order_get.interval'));
            }
        }

        if ($this->notify) {
            $this->call('tbk:notify-order');
        }
    }

    /**
     * @return TbkScOrderGetRequest
     */
    protected function getInitRequest()
    {
        $req = new TbkScOrderGetRequest();
        $req->setFields("tb_trade_parent_id,tk_status,tb_trade_id,num_iid,item_title,item_num,price,pay_price,seller_nick,seller_shop_title,commission,commission_rate,unid,create_time,earning_time,tk3rd_pub_id,tk3rd_site_id,tk3rd_adzone_id,relation_id");
        $req->setSpan($this->span);
        $req->setPageSize((string)$this->pageSize);
        if ($this->settle) {
            $req->setTkStatus("14");
            $req->setOrderQueryType("settle_time");
        } else {
            $req->setTkStatus("1");
            $req->setOrderQueryType("create_time");
        }

        return $req;
    }

    protected function fetchOrder(TbkScOrderGetRequest $req, $startTime, $pageNo)
    {
        $req->setStartTime($startTime);
        $req->setPageNo((string)$pageNo);

        return $response = $this->manager->scOrderGet($req);
    }

    /**
     * @param $response
     */
    protected function syncToDb($response)
    {
        if (empty($response)) {
            return;
        }

        $new = 0;
        $update = 0;
        $ignore = 0;
        $error = 0;
        $total = count($response);

        $validateRules = [
            'trade_id' => 'required',
            'tk_status' => 'required',
        ];

        foreach ($response as $item) {
//            dump($item);
            $validator = validator($item, $validateRules);
            if ($validator->fails()) {
                $this->info("忽略异常订单, 异常:" . $validator->errors()->first() . "  订单:" . json($item));
            }

            // 通过 "trade_id" 字段查找相同记录
            $tradeId = (string)$item['trade_id'];
//            if (empty($tradeId) || empty($item['tk_status'])) {
//                $this->info("忽略异常数据 " . json($item));
//                continue;
//            }

            $tbkOrder = TbkOrder::firstOrNew(['trade_id' => $tradeId], $item);
            // 已存在记录
//            if (!$tbkOrder->wasRecentlyCreated) {
            if ($tbkOrder->exists) {
                if ($tbkOrder->tk_status != $item['tk_status']) {
                    $this->info(
                        "订单:$tradeId({$item['trade_parent_id']}) 状态改变: " .
                        tbkOrderStatusMap($tbkOrder->tk_status) .
                        ' -> ' .
                        tbkOrderStatusMap($item['tk_status']) .
                        ($tbkOrder->user ? "用户: " . $tbkOrder->user->name : "") .
                        " 商品:{$item['item_title']}"
                    );
                    $tbkOrder['need_notify'] = true;

                    // 更新可能改变的值
                    $tbkOrder->fill($item);
                    $tbkOrder->save();
                    $update++;
                } else {
                    $ignore++;
                }
            } else {
                $adzoneId = $tbkOrder['adzone_id'];
                $user = User::where('tbk_adzone_id', $adzoneId)->first();
                if ($user) {
                    $tbkOrder->user_id = $user->id;
                }
                $tbkOrder['need_notify'] = true;

                $itemId = $tbkOrder['num_iid'];
                $itemInfo = app(TbkApiService::class)->itemInfoGet($itemId);
                if (!empty($itemInfo) && !empty($itemInfo->results) && !empty($itemInfo->results->n_tbk_item)) {
                    $tbkOrder->pict_url = ($itemInfo->results->n_tbk_item[0])->pict_url;
                }

                try {
                    $tbkOrder->save();
                } catch (\Exception $e) {
                    $this->error("新订单保存失败: " . $e->getMessage() . " 订单数据: " . json($item));
                    $error++;
                    continue;
                }

                $new++;
            }
        }

        $this->line(
            "订单同步 $total 条, 其中新增 $new 条, 更新 $update 条, 错误 $error 条, 忽略已存在的 $ignore 条.",
            $new + $update + $error > 0 ? "comment" : "debug"
        );
    }
}

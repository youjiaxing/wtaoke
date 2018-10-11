<?php

namespace App\Console\Commands;

use App\Models\TbkAdzone;
use App\Console\Command;

class ImportTbkAdzones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbk:import-adzones {site_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入推广位id';

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
    public function handle()
    {
        $siteId = (string)$this->argument('site_id');
        $file = base_path($this->argument('file'));
        if (!file_exists($file)) {
            $this->warn("导入文件 $file 不存在");
            return;
        }

        if (!$this->confirm("媒体id: $siteId, 本次导入文件为: $file, 请确认?", false)) {
            return;
        }

        $this->info("开始导入");
        $newCount = 0;
        $repeatCount = 0;
        $fp = fopen($file, "r");
        while (!feof($fp)) {
            $line = trim(fgets($fp));
            if (empty($line)) {
                continue;
            }
            if (!is_numeric($line)) {
                $this->warn("异常数据: $line");
                continue;
            }

            $tbkAdzones = TbkAdzone::firstOrCreate(['tbk_adzone_id' => $line], ['tbk_site_id' => $siteId]);
            if ($tbkAdzones->wasRecentlyCreated) {
                $this->info("新增推广位 {$siteId}_{$line}");
                $newCount++;
            } else {
                $this->info("推广位 {$siteId}_{$line} 已存在, 忽略.");
                $repeatCount++;
            }
        }
        fclose($fp);
        $this->comment("本次共导入 $newCount 条新纪录, 忽略 $repeatCount 条已存在的记录.");
    }
}

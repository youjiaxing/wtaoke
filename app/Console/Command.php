<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 18:58
 */

namespace App\Console;

use Illuminate\Log\Writer;

class Command extends \Illuminate\Console\Command
{
    /**
     * @var bool
     */
    protected $log = true;

    /**
     * @var Writer
     */
    protected $logger;

    protected function getLogger()
    {
        if (!$this->logger instanceof Writer) {
            $this->logger = new Writer(
                new \Monolog\Logger(basename(str_replace('\\', '/', get_called_class())))
            );

            $this->logger->useFiles(
                storage_path('logs/command.log'),
                config('app.log_level')
            );
        }

        return $this->logger;
    }

    /**
     * Write a string as standard output.
     *
     * @param  string          $string
     * @param  string          $style
     * @param  null|int|string $verbosity
     *
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        parent::line($string, $style, $verbosity);

        if (!$this->log) {
            return;
        }

        switch ($style) {
//            case 'info':
//                \Log::info($string);
//                break;
//
//            case 'comment':
//                \Log::info($string);
//                break;

            case 'error':
                $this->getLogger()->error($string);
                break;

            case 'warning':
                $this->getLogger()->warning($string);
                break;

            default:
                $this->getLogger()->info($string);
                break;
        }
    }
}
<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 18:58
 */

namespace App\Console;

use Illuminate\Log\Writer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
//            $this->logger = app('log');
//            $this->logger->getMonolog()->popHandler();
//
//            $this->logger->useFiles(
//                storage_path('logs/command.log'),
//                config('app.log_level')
//            );


            $this->logger = new Writer(
                new \Monolog\Logger(basename(str_replace('\\', '/', get_called_class())))
            );

            $this->logger->useFiles(
                storage_path('logs/command.log'),
                config('app.log_level')
            );
            app()->instance('log', $this->logger);
        }

        return $this->logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger();
        return parent::execute($input, $output);
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
        parent::line($string, $style == 'debug' ? 'info' : $style, $verbosity);

        if (!$this->log) {
            return;
        }

        switch ($style) {
            case 'debug':
                $this->getLogger()->debug($string);
                break;
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
            case 'warn':
                $this->getLogger()->warning($string);
                break;

            default:
                $this->getLogger()->info($string);
                break;
        }
    }

    public function debug($string, $verbosity = null)
    {
        $this->line($string, 'debug', $verbosity);
    }
}
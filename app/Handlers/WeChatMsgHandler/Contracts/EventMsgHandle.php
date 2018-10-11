<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 9:59
 */

namespace App\Handlers\WeChatMsgHandler\Contracts;

interface EventMsgHandle
{
    public function handleEvent($message);
}
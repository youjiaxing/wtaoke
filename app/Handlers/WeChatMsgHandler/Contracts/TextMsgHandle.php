<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 9:56
 */

namespace App\Handlers\WeChatMsgHandler\Contracts;

use EasyWeChat\Kernel\Messages\Text;

interface TextMsgHandle
{
    public function handleText($message);
}

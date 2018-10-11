<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 10:01
 */

namespace App\Handlers\WeChatMsgHandler\Contracts;

interface ImageMsgHandle
{
    public function handleImage($message);
}
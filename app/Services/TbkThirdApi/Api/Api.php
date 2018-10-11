<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/9 14:52
 */

namespace App\Services\TbkThirdApi\Api;

use TopClient\request\TbkScOrderGetRequest;

abstract class Api
{
    /**
     * @param TbkScOrderGetRequest $request
     *
     * @return mixed|false
     */
    public function scOrderGet(TbkScOrderGetRequest $request)
    {
        return false;
    }
}

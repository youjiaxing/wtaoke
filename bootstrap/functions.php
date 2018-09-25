<?php

function json($obj, $option = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
{
    return json_encode($obj, $option);
}
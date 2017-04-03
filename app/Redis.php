<?php

namespace App;

use \Redis as R;

class Redis extends R
{
    public function __construct()
    {
        $this->connect('127.0.0.1', 6379);
    }

    public function getPage()
    {
        return $this->incr('crawler_page');
    }
}
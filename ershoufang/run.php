<?php
/**
 *
 * run.php
 *
 * Author: swen@verystar.cn
 * Create: 05/01/2017 14:52
 * Editor: created by PhpStorm
 */
require dirname(__FILE__) . '/vendor/autoload.php';

(new App\Worker())->run();
//echo (new \App\Spider())->getUrlContent('http://sh.lianjia.com/ershoufang/d2');

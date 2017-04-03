<?php
/**
 *
 * Spider.php
 *
 * Author: swen@verystar.cn
 * Create: 05/01/2017 14:31
 * Editor: created by PhpStorm
 */
namespace App;

use Symfony\Component\DomCrawler\Crawler;


class Spider
{
    /***
     *
     * 抓取数据，并解析结果，如果解析失败(服务器防爬虫，sleep1秒继续)，则重复执行10次
     *
     * @param $url
     * @return bool|mixed
     */
    public function craw($url)
    {
        for ($i = 0; $i < 10; $i++) {
            $crawler = new Crawler();
            $crawler->addHtmlContent($this->getUrlContent($url));
            $found = $crawler->filter(".house-lst li");

            //判断是否页面已经结束
            if ($found->count()) {
                return $this->parse($found);
            }
            sleep(1);
        }
        return false;
    }

    /***
     *
     * 解析列表数据到数组
     *
     * @param $found
     * @return mixed
     */
    private function parse($found)
    {
        return $found->each(
            function (Crawler $node, $i) {
                //问答ID
                $data = [
                    'xiaoqu'   => $this->getNodeHtml($node, '.info-panel .col-1 .where a span'),
                    'quxian'   => $this->getNodeHtml($node, '.info-panel .col-1 .other .con a'),
                    'title'    => $this->getNodeHtml($node, ".info-panel h2 a"),
                    'danjia'   => intval($this->getNodeText($node, '.info-panel .col-3 .price-pre')),
                    'zongjia'  => $this->getNodeText($node, '.info-panel .col-3 .price span'),
                    'nianxian' => $this->getNodeText($node, '.info-panel .col-1 .chanquan .agency .taxfree-ex'),
                    'mianji'   => floatval($this->getNodeText($node, '.info-panel .col-1 .where span', ['index' => 2])),
                    'huxing'   => $this->getNodeText($node, '.info-panel .col-1 .where span', ['index' => 1]),
                    'url'      => $this->getNodeAttribute($node, ".info-panel h2 a", 'href'),
                    'ditie'    => $this->getNodeText($node, '.info-panel .col-1 .chanquan .agency .fang-subway-ex'),
                    'out_sn'   => $this->getNodeAttribute($node, ".info-panel h2 a", 'key'),
                ];

                $str                = explode('|', $this->getNodeText($node, '.info-panel .col-1 .other .con'));
                $data['niandai']    = isset($str[3]) ? substr(trim($str[3]), 0, 4) : '';
                $data['chaoxiang']  = isset($str[2]) ? trim($str[2]) : '';
                $data['louceng']    = isset($str[1]) ? $this->getFloor(trim($str[1])) : '';
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['created_at'] = $data['updated_at'];
                return $data;
            }
        );
    }

    /****
     *
     * 获取楼层信息
     *
     * @param $str
     * @return int
     */
    private function getFloor($str)
    {
        $count = explode('/', $str);
        $floor = $count && isset($count[1]) ? $count[1] : $str;
        return intval($floor);
    }


    private function getNodeAttribute($node, $filter, $attr, $more = [], $default = '')
    {
        $node = $node->filter($filter);
        if ($node->count()) {
            if (isset($more['index'])) {
                $node = $node->eq($more['index']);
            }
        }

        return $node->count() ? trim($node->attr($attr)) : $default;
    }

    private function getNodeText($node, $filter, $more = [], $default = '')
    {
        $node = $node->filter($filter);
        if ($node->count()) {
            if (isset($more['index'])) {
                $node = $node->eq($more['index']);
            }
        }

        return $node->count() ? trim($node->text()) : $default;
    }


    private function getNodeHtml($node, $filter, $more = [], $default = '')
    {
        $node = $node->filter($filter);
        if ($node->count()) {
            if (isset($more['index'])) {
                $node = $node->eq($more['index']);
            }
        }

        return $node->count() ? trim($node->html()) : $default;
    }


    /***
     *
     * 抓取指定url的内容
     *
     * @param $url
     * @return bool|mixed
     */
    public function getUrlContent($url)
    {
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}
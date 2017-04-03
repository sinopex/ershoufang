<?php
namespace App;

use \PDO;

class Db extends PDO
{
    public function __construct()
    {
        $dsn     = "mysql:host=127.0.0.1;port=3306;dbname=fang";
        $options = [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;",
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ];
        parent::__construct($dsn, 'username', 'password', $options);
    }

    /****
     *
     * 批量插入数据库
     *
     * @param $rows
     * @return bool
     */
    public function multiInsert($rows)
    {
        if (!$rows || !is_array($rows)) {
            return false;
        }

        //拼接SQL插入语句
        $sql = 'INSERT INTO `er_shou_fang`(`xiaoqu`,`quxian`,`title`,`niandai`,`chaoxiang`,`louceng`,`danjia`,`zongjia`,`nianxian`,`mianji`,`huxing`,`url`,`ditie`,`updated_at`,`created_at`,`out_sn`) VALUES ';
        $dot = '';
        foreach ($rows as $i => $item) {
            $sql .= "$dot(:xiaoqu{$i},:quxian{$i},:title{$i},:niandai{$i},:chaoxiang{$i},:louceng{$i},:danjia{$i},:zongjia{$i},:nianxian{$i},:mianji{$i},:huxing{$i},:url{$i},:ditie{$i},:updated_at{$i},:created_at{$i},:out_sn{$i})";
            $dot = ',';
        }
        $sql .= ';';

        /***
         * 批量绑定变量,注意绑定变量是基于引用的,千万不要用$item取值
         * [详细见鸟哥论坛:http://www.laruence.com/2012/10/16/2831.html]
         */

        $stmt = $this->prepare($sql);
        foreach ($rows as $i => $item) {
            $stmt->bindParam(':' . 'xiaoqu' . $i, $rows[$i]['xiaoqu']);
            $stmt->bindParam(':' . 'quxian' . $i, $rows[$i]['quxian']);
            $stmt->bindParam(':' . 'title' . $i, $rows[$i]['title']);
            $stmt->bindParam(':' . 'niandai' . $i, $rows[$i]['niandai']);
            $stmt->bindParam(':' . 'chaoxiang' . $i, $rows[$i]['chaoxiang']);
            $stmt->bindParam(':' . 'louceng' . $i, $rows[$i]['louceng']);
            $stmt->bindParam(':' . 'danjia' . $i, $rows[$i]['danjia']);
            $stmt->bindParam(':' . 'zongjia' . $i, $rows[$i]['zongjia']);
            $stmt->bindParam(':' . 'nianxian' . $i, $rows[$i]['nianxian']);
            $stmt->bindParam(':' . 'mianji' . $i, $rows[$i]['mianji']);
            $stmt->bindParam(':' . 'huxing' . $i, $rows[$i]['huxing']);
            $stmt->bindParam(':' . 'url' . $i, $rows[$i]['url']);
            $stmt->bindParam(':' . 'ditie' . $i, $rows[$i]['ditie']);
            $stmt->bindParam(':' . 'updated_at' . $i, $rows[$i]['updated_at']);
            $stmt->bindParam(':' . 'created_at' . $i, $rows[$i]['created_at']);
            $stmt->bindParam(':' . 'out_sn' . $i, $rows[$i]['out_sn']);
        }

        return $stmt->execute();
    }
}
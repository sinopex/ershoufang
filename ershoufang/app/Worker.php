<?php
/**
 *
 * Worker.php
 *
 * Author: swen@verystar.cn
 * Create: 05/01/2017 14:53
 * Editor: created by PhpStorm
 */
namespace App;

class Worker
{
    private $host = '127.0.0.1';
    private $port = 9501;

    /**
     * worker start 加载业务脚本常驻内存
     *
     * @param $serv
     * @param $work_id
     */
    public function onWorkerStart($serv, $work_id)
    {
        if ($work_id > $serv->setting['worker_num']) {
            $title = $serv->setting['process_name'] . '_task';
        } else {
            $title = $serv->setting['process_name'] . '_worker';
        }
        echo $title . $work_id . ' started' . PHP_EOL;
    }

    public function onStart($serv)
    {
        echo "Start at:" . date('Y-m-d H:i:s') . PHP_EOL;
        //发起一次请求
        $client = new \swoole_client(\SWOOLE_SOCK_TCP);
        $client->connect($this->host, $this->port, 1);
        $client->send('start');
        $client->close();
    }

    public function onConnect($serv, $fd, $from_id)
    {

    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        for ($i = 0; $i < $serv->setting['task_worker_num']; $i++) {
            $serv->task('run');
        }

//        sleep(5);
//        while(1){
//            echo 'stat='.print_r($serv->stats(),true).PHP_EOL;
//            $tasking_num = $serv->stats()['tasking_num'];
//            echo 'tasking_num='.$tasking_num.PHP_EOL;
//            sleep(1);
//        }
    }

    /**
     * 任务执行，请注意，本函数内的$this和onReceive中的$this并不是同一个实例
     * 该函数千万不要return 否则会导致$serv->stats()['tasking_num']无法统计到真实的数据,从而永远sleep
     *
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return array
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
        $redis  = new Redis();
        $db     = new Db();
        $return = ['success' => 0, 'fail' => 0];
        while (1) {
            $page = $redis->getPage();

            $url    = 'http://sh.lianjia.com/ershoufang/d' . $page;
            $result = (new Spider())->craw($url);
            if (!$result) {
                break;
            }
            $ret = $db->multiInsert($result);
            if ($ret) {
                $return['success'] += 20;
            } else {
                $return['fail'] += 20;
            }
            echo '[' . $task_id . ']crawling page=' . $page . ',save to db ' . ($ret ? 'success' : 'failed') . PHP_EOL;
        }
        return $return;
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "Finish {$task_id}，success:" . $data['success'] . ',fail:' . $data['fail'] . PHP_EOL;
    }

    public function onClose($serv, $fd, $from_id)
    {
        echo "Client {$fd} close connection\n";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function run()
    {
        $serv = new \swoole_server($this->host, $this->port);
        $serv->set(
            array(
                'process_name'      => 'ershoufang_crawler', //swoole 进程名称
                'worker_num'        => 2,//开启的worker进程数
                'task_worker_num'   => 2,//开启的task进程数
                'open_cpu_affinity' => true,
                'daemonize'         => false,
                'max_request'       => 10000,
                'dispatch_mode'     => 2,
                'debug_mode'        => 0,
                'log_file'          => 'swoole.log',
                'open_tcp_nodelay'  => true,
                "task_ipc_mode"     => 2,
                'task_max_request'  => 10000
            )
        );

        $serv->on('Start', array($this, 'onStart'));
        $serv->on('Connect', array($this, 'onConnect'));
        $serv->on('Receive', array($this, 'onReceive'));
        $serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $serv->on('Task', array($this, 'onTask'));
        $serv->on('Finish', array($this, 'onFinish'));
        $serv->on('Close', array($this, 'onClose'));
        $serv->start();
    }
}
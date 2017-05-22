<?php
/**
 *
 *
 */

class BroadcastServer
{
    private $serv;
    private $pdo;
    private $data_fd;
    private $data_data;
    private $process;
    private $connections = [];
    /**
     * [__construct description]
     * 构造方法中,初始化 $serv 服务
     */
    public function __construct()
    {
        /**
         * server and process setting
         * @var redis
         */
        $this->redis = new redis();
        $result      = $this->redis->connect("127.0.0.1", 6379, 0);

        $this->serv = new swoole_websocket_server('0.0.0.0', 9512);
        //初始化swoole服务
        $this->serv->set(array(
            'daemonize'   => false, //是否作为守护进程,此配置一般配合log_file使用
            'max_request' => 100000,
            'log_file'    => './log/broadcast.log',
        ));

        //开启WorkerStart
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));

        //设置监听
        $this->serv->on('Open', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on("Message", array($this, 'onMessage'));
        $this->serv->on("Close", array($this, 'onClose'));

        // bind callback
        $this->serv->on("Task", array($this, 'onTask'));
        $this->serv->on("Finish", array($this, 'onFinish'));
        //开启
        $this->serv->start();

    }

    public function callback(swoole_process $worker)
    {
        $start = time();
        $i     = 1;

        while ($data = $this->redis->BRPOPLPUSH('message', 'forever', 0)) {

            //send data to master
            $worker->write("$data \n");
            echo "$i Data:" . $data . PHP_EOL;

            // if($i == 100000){
            //     $end = time();
            //     echo "total time: " . ($end - $start);
            // }
            // $i++;
        }
        $worker->daemon(true); //2017-05-016 add
        //$worker->exit(0);
    }

    public function onStart($serv, $request)
    {
        //echo SWOOLE_VERSION . " onStart\n";
    }

    public function onWorkerStart($serv, $worker_id)
    {
        echo "master pid:" . $this->serv->master_pid . "\n";
        if ($worker_id == 0) {
            $this->process = new swoole_process(array($this, 'callback'));
            $pid           = $this->process->start();

            swoole_event_add($this->process->pipe, function ($pipe) {
                $data = $this->process->read();
                //echo "now pid:" . $this->serv->manager_pid ."\n";
                //var_dump($this->connections);
                foreach ($this->connections as $fd) {
                    $this->serv->send($fd, $data);
                }

                

            });
            ///2017-05-17 add
            swoole_process::signal(SIGCHLD, function ($sig) {
                //必须为false，非阻塞模式
                while ($ret = swoole_process::wait(false)) {
                    echo "PID={$ret['pid']}\n";
                }
            });
            ///2017-05-17 add
            // while(true)
            // {
            // # 等待回收，如果不回收进程会变成僵死进程，很可怕的
            //     if (false === swoole_process::wait())
            //     {
            //         break;
            //     }
            // }

        } else {

        }

        echo "Woker " . $worker_id . " is Start\n";
    }

    public function onConnect($serv, $fd)
    {
        echo "Client{$fd} Connect.\n";
        $serv->send($fd, "hello, welcome\n");
        array_push($this->connections, $fd);
    }

    public function onMessage($serv, $frame)
    {
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
    }

    public function onClose($serv, $fd)
    {
        echo "Client Close.\n";
        if (($key = array_search($fd, $this->connections)) !== false) {
            unset($this->connections[$key]);
        }

        $file    = '/temp/broadcast.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "Client Close 關閉\n";
        $current .= print_r($serv, true) . "\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";

       $file    = './temp/broadcast.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "Task {$task_id} finish\n";
        $current .= print_r($serv, true) . "\n";
        $current .= print_r($task_id, true) . "\n";
        $current .= print_r($data, true) . "\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);
    }
}
$server = new BroadcastServer();

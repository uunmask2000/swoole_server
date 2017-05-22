<?php

class taskServer
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
         * Server Side
         */
        $this->serv  = new swoole_websocket_server('0.0.0.0', 9511);
        $this->redis = new redis();
        $result      = $this->redis->connect("127.0.0.1", 6379);
        $this->redis->del('fd');
        //exit();
        //初始化swoole服务
        $this->serv->set(array(
            'worker_num'      => 1,
            'daemonize'       => false, //是否作为守护进程,此配置一般配合log_file使用
            'max_request'     => 100000,
            'log_file'        => './taskServer.log',
            'task_worker_num' => 1,
            'dispatch_mode' => 2  , 
            //1，轮循模式，收到会轮循分配给每一个worker进程
            //2，固定模式，根据连接的文件描述符分配worker。这样可以保证同一个连接发来的数据只会被同一个worker处理
            //3，抢占模式，主进程会根据Worker的忙闲状态选择投递，只会投递给处于闲置状态的Worker
            //4，IP分配，根据客户端IP进行取模hash，分配给一个固定的worker进程。可以保证同一个来源IP的连接数据总会被分配到同一个worker进程。算法为 ip2long(ClientIP) % worker_num
            //5，UID分配，需要用户代码中调用 $serv-> bind() 将一个连接绑定1个uid。然后swoole根据UID的值分配到不同的//
        ));

        //开启WorkerStart
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('WorkerError', array($this, 'WorkerError'));

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
    //WorkerError
    public function WorkerError($serv, $worker_id, $worker_pid, $exit_code)
    {

        echo PHP_EOL;
        echo " WorkerError";
        echo PHP_EOL;
        var_dump($from_worker_id);
        echo PHP_EOL;
        var_dump($worker_pid);
        echo PHP_EOL;
        var_dump($exit_code);
        echo PHP_EOL;
        $JS_from_worker_id = json_encode($from_worker_id);
        $JS_worker_pid     = json_encode($worker_pid);
        $JS_exit_code      = json_encode($exit_code);

        $file    = './log/WorkerError_taskServer.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "WorkerError 關閉" . "\n";
        $current .= $JS_from_worker_id . "\n";
        $current .= $JS_worker_pid . "\n";
        $current .= $JS_exit_code . "\n";

        $current .= "-------------------------\n";
        file_put_contents($file, $current);

    }
    /**
     * When Server is Start
     * @param  [type] $serv [server]
     * @return [void]
     */
    public function onStart($serv)
    {
        echo SWOOLE_VERSION . " onStart\n";

    }

    /**
     * when client connect
     * @param  [type] $serv [server]
     * @param  [type] $fd   [clientId]
     * @return [void]
     */
    public function onConnect($serv, $fd)
    {
        echo "Client{$fd} Connect.\n";
        array_push($this->connections, $fd);
        $fdStr = json_encode($this->connections);
        //var_dump($fdStr);
        $this->redis->set('fd', $fdStr);
    }

    /**
     * When Woker Start
     * @param  [type] $serv      [server]
     * @param  [type] $worker_id [wokerId]
     */
    public function onWorkerStart($serv, $worker_id)
    {
        //echo "master pid:" . $this->serv->master_pid;
        if ($worker_id == 0) {
            echo "A" . '\n';
            $this->process = new swoole_process(array($this, 'ClientProcess'));
            $pid           = $this->process->start();
            swoole_event_add($this->process->pipe, function ($pipe) {
                $data = $this->process->read();

                // get all client from redis
                $redis = new redis();
                $redis->connect("127.0.0.1", 6379);
                $getArr            = $redis->get('fd');
                ($getArr) ? $fdArr = json_decode($getArr, true) : $fdArr = [];
                //print_r($fdArr);
                //print_r($getArr);
                //echo "\n";
                //process event
                /*
                $jsonData = json_decode($data, true);
                switch ($jsonData['userData']['event']) {
                case 'initCard':
                break;

                default:
                # code...
                break;
                }
                 */
                //send to client
                foreach ($fdArr as $fd) {
                    echo $fd . PHP_EOL;
                    $this->serv->push($fd, $data);
                }

            });
            while (true) {
                # 等待回收，如果不回收进程会变成僵死进程，很可怕的
                if (false === swoole_process::wait()) {
                    break;
                }
            }

        } else {

        }

        echo "Woker " . $worker_id . " is Start\n";
    }

    /**
     * Create a client Process
     * @param swoole_process $worker [worker]
     */
    public function ClientProcess(swoole_process $worker)
    {
        $client = new taskClient();
        $client->connect($worker);
        $worker->daemon(true); //2017-05-016 add
        //send data to master
        //$worker->write("$data \n");
        //$worker->exit(0);

    }

    /**
     * Get Message
     * @param  [type] $serv  [server]
     * @param  [json] $frame [data]
     * @return [void]        [description]
     */
    public function onMessage($serv, $frame)
    {
        // $data = json_decode($frame->data);
        //echo "Get Message From Client {$frame->fd}  Data:{$frame->data}\n";

        ///$frame->fd 是客    户端id，$frame->data是客户端发送的数据
        //服务端向客户端发送数据是用 $server->push( '客户端id' ,  '内容')
        // start a task
        $this->serv->task($frame->data);

        //echo "Continue Handle Worker\n";
    }

    /**
     * When Client Close Connect
     * @param  [type] $serv [server]
     * @param  [type] $fd   [clientId]
     * @return [void]       [description]
     */
    public function onClose($serv, $fd)
    {
        echo "Client Close.\n";
        echo $fd . " : Client Close.\n";

        if (($key = array_search($fd, $this->connections)) !== false) {
            // unset($this->connections[$key]);
            // $fdArr = json_encode($this->connections);
            // $this->redis->set("fd", $fdArr);
        }

        $file    = './log/taskServer.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "Client Close 關閉\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);
    }

    /**
     * Start Task
     * @param  [type] $serv    [server]
     * @param  [type] $task_id [taskId]
     * @param  [type] $from_id [fromId]
     * @param  [type] $data    [data]
     * @return [void]          [description]
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
        $jsonData = json_decode($data, true);
        //var_dump($serv);
        //var_dump($task_id);
        //var_dump($from_id);
        //var_dump($jsonData);
        //echo "This Task {$task_id} from Worker {$from_id}\n";
        //echo "Event:{$jsonData['event']} Data:{$jsonData['data']}\n";

        $event = $jsonData['event'];
        //var_dump($event) ;
        switch ($event) {
            case 'outCard':
                $this->redis->RPUSH('dataProcess', $data);
                break;
            case 'getCard':
                //echo "DOing";
                $this->redis->RPUSH('dataProcess', $data);
                break;
            case 'Join room':
                break;

            case 'initCard':
                $this->redis->RPUSH('dataProcess', $data);
                break;
            default:
                # code...
                break;
        }
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
        $file    = './log/taskServer.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "Task {$task_id} finish\n";
        $current .= print_r($data, true) . "\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);
    }

}

/**
 * Be a TCP Client
 */
class taskClient
{
    private $client;
    private $woker;
    public function __construct()
    {
        echo "B" . '\n';
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->on('Connect', array($this, 'onConnect'));
        $this->client->on('Receive', array($this, 'onReceive'));
        $this->client->on('Close', array($this, 'onClose'));
        $this->client->on('Error', array($this, 'onError'));
    }

    /**
     * TCP connect
     */
    public function connect($woker)
    {
        if (!$fp = $this->client->connect("127.0.0.1", 9512, 1)) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
        $this->woker = $woker;
    }
    //connect之后,会调用onConnect方法
    public function onConnect($cli)
    {
        // fwrite(STDOUT, "Enter Msg:");
        // swoole_event_add(STDIN, function () {
        //     fwrite(STDOUT, "Enter Msg:");
        //     $msg = trim(fgets(STDIN));
        //     $this->send($msg);
        // });
    }
    public function onClose($cli)
    {
        echo "Client close connection\n";
        $file    = './log/taskServer.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "onClose  關閉\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);

    }
    public function onError()
    {
    }
    public function onReceive($cli, $data)
    {
        //echo "Received: " . $data . "\n";
        $this->woker->write("$data \n");

    }
    public function send($data)
    {
        $this->client->send($data);
    }
    public function isConnected($cli)
    {
        return $this->client->isConnected();
    }
}
$server = new taskServer();

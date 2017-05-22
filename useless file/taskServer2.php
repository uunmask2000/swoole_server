<?php

class taskServer
{
    private $serv;
    private $pdo;
    private $data_fd;
    private $data_data;
    private $process;
    private $connections = [];

    public $works     = [];
    public $new_index = 0;
    public $mpid      = 0;
    /**
     * [__construct description]
     * 构造方法中,初始化 $serv 服务
     */
    public function __construct()
    {

        /**
         * Server Side
         */
        swoole_set_process_name(sprintf('php-ps:%s', 'master'));
        $this->mpid = posix_getpid();

        //$this->serv  = new swoole_websocket_server('0.0.0.0', 9511);
		$this->serv  = new swoole_websocket_server('0.0.0.0', 9521);
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
        //$JS_from_worker_id = json_encode($from_worker_id);
        $JS_worker_pid = json_encode($worker_pid);
        $JS_exit_code  = json_encode($exit_code);

        $file    = './log/taskServer_WorkerError.txt';
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
            try {
                $this->run();
                $this->processWait();

            } catch (\Exception $e) {
                die('ALL ERROR: ' . $e->getMessage());
            }

        } else {

        }

        echo "Woker " . $worker_id . " is Start\n";
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

    public function run()
    {
        $this->CreateProcess();        
    }

    public function pipe_run($worker){
        $this->checkMpid($worker);

        swoole_event_add($worker->pipe, function ($pipe) use($worker) {

            swoole_set_process_name(sprintf('php-ps:%s', $woker->pid));

            $data = $worker->read();

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

        $client = new taskClient();
        $client->connect($worker);
    }
    public function CreateProcess($index = null)
    {
        $this->process = new swoole_process(array($this, 'pipe_run'), false, 2);
        $pid                 = $this->process->start();
        return $pid;
    }

    public function checkMpid(&$worker)
    {
        if (!swoole_process::kill($this->mpid, 0)) {
            $worker->exit();
            // 这句提示,实际是看不到的.需要写到日志中
            echo "Master process exited, I [{$worker['pid']}] also quit\n";
        }
    }

    public function processWait()
    {
        while (1) {
            $ret = swoole_process::wait();
            if ($ret) {
                $this->rebootProcess($ret);
            }
        }
    }

    public function rebootProcess($ret)
    {
        $pid   = $ret['pid'];
        $new_pid = $this->CreateProcess();
        echo "rebootProcess : {" . $pid  . "to" .  $new_pid . "} Done\n";
        return;
    }
}

/**
 * Be a TCP Client
 */
class taskClient
{
    private $client;
    private $worker;
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
    public function connect(&$worker)
    {
        if (!$fp = $this->client->connect("127.0.0.1", 9512, 1)) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
        $this->worker = $worker;
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
        $this->worker->write("$data \n");

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

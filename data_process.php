<?php
/**
 *
 *
 */
require dirname(__FILE__) . "/libs/Cardbox.php";
require dirname(__FILE__) . "/libs/Userinformation.php";

class DataProcessServer
{
    private $serv;
    private $pdo;
    private $data_fd;
    private $data_data;
    private $process;
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

        $this->serv = new swoole_websocket_server('0.0.0.0', 9513);
        //初始化swoole服务
        $this->serv->set(array(
            'daemonize'   => false, //是否作为守护进程,此配置一般配合log_file使用
            'max_request' => 100000,
            'log_file'    => './log/data_process.log',
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

    /**
     * For processing data
     * @param  swoole_process $worker [swoole_process woker]
     * @return void
     */
    public function processData(swoole_process $worker)
    {
        while ($data = $this->redis->BRPOPLPUSH('dataProcess', 'foreverData', 0)) {
            $worker->write("$data \n");
            //var_dump($data);
            $deCodeData = json_decode($data, true);
            //var_dump($deCodeData);
            switch ($deCodeData['event']) {
                case 'getRoomPeople':
                    $roomData             = $this->redis->hget("Server1", $deCodeData['roomId']);
                    $retVal               = [];
                    $retVal['userData']   = $deCodeData;
                    $retVal['event']      = $deCodeData['event'];
                    $retVal['connect_fd'] = $deCodeData['connect_fd'];
                    $retVal['peopleNum']  = count(json_decode($roomData, true));

                    $retVal = json_encode($retVal);
                    $this->redis->RPUSH('message', $retVal);
                    break;
                case 'JoinRoom': //加入房間

                    break;
                case 'initCard': //牌盒
                    $cardBox            = new Carbox();
                    $retVal             = [];
                    $retVal['event']    = $deCodeData['event'];
                    $retVal['userData'] = $deCodeData;
                    $retVal['cardData'] = $cardBox->dealCard();
                    //$Round_array = '{"1":1,"2":2,"3":3,"4":4}';
                    $Round_array = '1234';
                    $this->redis->hset("CardList", $deCodeData['roomId'], json_encode($retVal['cardData']));
                    $this->redis->hset("Round", $deCodeData['roomId'], json_encode($Round_array));
                    $retVal = json_encode($retVal);
                    //$push_out_card    = json_decode($this->redis->hget("push_out_card", $parseData['roomId']), true);   // 建置 廢棄池牌
                    $this->redis->hset("push_out_card", $deCodeData['roomId'], "");
                    $this->redis->RPUSH('message', $retVal);
                    //var_dump($startCard);
                    //var_dump($playerCard);
                    break;

                case 'outCard': /// 出牌
                    //echo $data;
                    $Userinformation = new Userinformation();
                    $parseData       = json_decode($data, true);
                    $cardData        = json_decode($this->redis->hget("CardList", $parseData['roomId']), true);
                    //$key       = "player" . $parseData['player'];
                    $Round         = json_decode($this->redis->hget("Round", $parseData['roomId']), true);
                    $push_out_card = json_decode($this->redis->hget("push_out_card", $parseData['roomId']), true); //查詢 廢棄池牌
                    $endCard1      = count($cardData['endCard']);
                    switch ($endCard1) {
                        case "0":
                            echo "牌合空了 \n";
                            $retVal              = [];
                            $deCodeData['event'] = "RoundEnd";
                            $retVal['event']     = $deCodeData['event'];
                            $retVal              = json_encode($retVal);
                            $this->redis->RPUSH('message', $retVal);
                            break;
                        default:
                            $cardData_player = $Userinformation->cardData_player($cardData);
                            $payer_user      = $parseData['player'];
                            $check_outCard   = $Userinformation->check_outCard($payer_user, $Round, $cardData_player[0], $cardData_player[1], $cardData_player[2], $cardData_player[3]); // check_outCard_round
                            $OK              = $check_outCard;

                            $check_outCard_round = $Userinformation->check_outCard_round($Round, $payer_user);
                            //var_dump($check_outCard_round);
                            $Round_array = $check_outCard_round["Round_array"];
                            $key         = $check_outCard_round["key"];
                            $key2        = $check_outCard_round["key2"];
                            $push_out_card .= $parseData['data'] . ",";
                            //echo $parseData['data'] ;  //打掉的牌
                            //remove card from user's card
                            if (($rmKey = array_search($parseData['data'], $cardData[$key])) !== false) {
                                array_splice($cardData[$key], $rmKey, 1);
                            }

                            $retVal             = [];
                            $retVal['event']    = $deCodeData['event'];
                            $retVal['userData'] = $deCodeData;
                            $retVal['cardData'] = $cardData;
                            $retVal             = json_encode($retVal);
                            if ($OK != 1) {
                                //
                                $this->redis->hset("CardList", $parseData['roomId'], json_encode($cardData));
                                $this->redis->hset("gameLog", $parseData['roomId'], json_encode($data));
                                $this->redis->hset("Round", $parseData['roomId'], json_encode($Round_array));
                                $this->redis->hset("push_out_card", $parseData['roomId'], json_encode($push_out_card)); // 打掉的牌
                                $this->redis->RPUSH('message', $retVal);
                                //檢查點
                                $push_out_card2 = json_decode($this->redis->hget("push_out_card", $parseData['roomId']), true); //查詢 廢棄池牌
                                //var_dump($push_out_card2);
                                // 自動call下家 拿牌
                                $cardData = $this->auto_put($data, $key2, $deCodeData, $Round_array);
//call 拿牌
                                $endCard = count($cardData['endCard']);
                                echo $endCard . "\n";

                                switch ($endCard) {
                                    case "0":
                                        echo "已打出最後一張 \n";
                                        /*
                                        $retVal              = [];
                                        $deCodeData['event'] = "RoundEnd";
                                        $retVal['event']     = $deCodeData['event'];
                                        $retVal              = json_encode($retVal);
                                        $this->redis->RPUSH('message', $retVal);
                                         */
                                        break;
                                    default:
                                        //echo "Your favorite color is neither red, blue, nor green!";

                                }
                                //
                            }
                    }

                    break;

                case 'getCard': // 取牌
                    //echo $data;
                    $parseData = json_decode($data, true);
                    $cardData  = json_decode($this->redis->hget("CardList", $parseData['roomId']), true);
                    $Round     = json_decode($this->redis->hget("Round", $parseData['roomId']), true);
                    $endCard   = count($cardData['endCard']);
                    echo $endCard . "\n";
                    switch ($endCard) {
                        case "0":
                            echo "不能抽卡 \n";
                            break;
                        default:
                            $Userinformation = new Userinformation();
                            //remove card from user's card
                            $newCard = array_shift($cardData['endCard']);
                            //$key       = "player" . $parseData['player'];
                            $cardData_player = $Userinformation->cardData_player($cardData);
                            $payer_user      = $parseData['player'];
                            $check_hand      = $Userinformation->check_hand($payer_user, $cardData_player[0], $cardData_player[1], $cardData_player[2], $cardData_player[3], $Round);
                            //echo $check_hand;
                            $OK          = $check_hand;
                            $check_round = $Userinformation->check_round($Round, $parseData['player']);
                            $Round_array = $check_round["Round_array"];
                            $key         = $check_round["key"];

                            //push card to user's card
                            array_push($cardData[$key], $newCard);

                            $retVal             = [];
                            $retVal['event']    = $deCodeData['event'];
                            $retVal['userData'] = $deCodeData;
                            $retVal['cardData'] = $cardData;
                            $retVal['Round']    = $Round_array;
                            //var_dump($retVal) ;
                            $retVal = json_encode($retVal);

                            if ($OK != 1) {
                                $this->redis->hset("CardList", $parseData['roomId'], json_encode($cardData));
                                $this->redis->hset("gameLog", $parseData['roomId'], json_encode($data));
                                $this->redis->hset("Round", $deCodeData['roomId'], json_encode($Round_array));
                                $this->redis->RPUSH('message', $retVal);
                            }
                    }

                    break;

                default:
                    # code...
                    break;
            }
            echo "Data:" . $data . PHP_EOL;
            $file    = './output/date_processs_Data.txt';
            $current = file_get_contents($file);
            $current .= "-------------------------\n";
            $current .= date("Y-m-d H:i:s") . "Data 送出\n";
            $current .= $data . "\n";
            $current .= "-------------------------\n";
            file_put_contents($file, $current);

        }
        $worker->daemon(true); //2017-05-016 add
        //$worker->exit(0);

    }
    /**
     * auto_put  自動取牌
     *
     */
    public function auto_put($data, $key2, $deCodeData, $Round_array)
    {
        //print 'Inside `aMemberFunc()`';
        //print_r($data) ;
        //return 12345;

        $parseData = json_decode($data, true);
        //print_r($parseData);
        $cardData = json_decode($this->redis->hget("CardList", $parseData['roomId']), true);
        $Round    = json_decode($this->redis->hget("Round", $parseData['roomId']), true);
//push card to user's card
        //$key2 = "player2";
        $newCard = array_shift($cardData['endCard']);
        array_push($cardData[$key2], $newCard);

        $retVal                  = [];
        $retVal['event']         = $deCodeData['event'];
        $retVal['userData']      = $deCodeData;
        $retVal['cardData']      = $cardData;
        $retVal['Round']         = $Round_array;
        $retVal['push_out_card'] = $parseData['data'];

//var_dump($retVal) ;
        $retVal = json_encode($retVal);

        $this->redis->hset("CardList", $parseData['roomId'], json_encode($cardData));
        $this->redis->hset("gameLog", $parseData['roomId'], json_encode($data));
        $this->redis->hset("Round", $deCodeData['roomId'], json_encode($Round_array));
        $this->redis->RPUSH('message', $retVal);

        return $cardData;
    }

    /**
     * Start Server
     * @param  [type] $serv    [server]
     * @param  [type] $request [request]
     * @return [void]
     */
    public function onStart($serv, $request)
    {
        //echo SWOOLE_VERSION . " onStart\n";
    }

    /**
     * [When Woker is Start]
     * @param  [type] $serv      [server]
     * @param  [type] $worker_id [wokerId]
     * @return [void]
     */
    public function onWorkerStart($serv, $worker_id)
    {
        echo "master pid:" . $this->serv->master_pid;
        if ($worker_id == 0) {
            $this->process = new swoole_process(array($this, 'processData'));
            $pid           = $this->process->start();

            swoole_event_add($this->process->pipe, function ($pipe) {
                $data = $this->process->read();

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
            //     # 等待回收，如果不回收进程会变成僵死进程，很可怕的
            //     if (false === swoole_process::wait())
            //     {
            //         break;
            //     }
            // }

        } else {

        }

        echo "Woker " . $worker_id . " is Start\n";
    }

    /**
     * [When Client is Connect]
     * @param  [type] $serv [server]
     * @param  [type] $fd   [clientId]
     * @return [void]
     */
    public function onConnect($serv, $fd)
    {
        echo "Client{$fd} Connect.\n";
        $serv->send($fd, "hello, welcome\n");
        array_push($this->connections, $fd);
    }

    /**
     * When message is comming
     * @param  [type] $serv  [server]
     * @param  [type] $frame [data]
     * @return [void]
     */
    public function onMessage($serv, $frame)
    {
    }

    /**
     * When Task is Start
     * @param  [type] $serv    [server]
     * @param  [type] $task_id [taskId]
     * @param  [type] $from_id [fromId]
     * @param  [type] $data    [data]
     * @return [void]
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
    }

    /**
     * When Client close connect
     * @param  [type] $serv [server]
     * @param  [type] $fd   [clientId]
     * @return [void]
     */
    public function onClose($serv, $fd)
    {
        echo "Client Close.\n";
        $file    = './temp/date_processs.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "Client Close 關閉\n";
        $current .= print_r($serv, true) . "\n";
        $current .= print_r($fd, true) . "\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);

    }

    /**
     * When Task is finish
     * @param  [type] $serv    [server]
     * @param  [type] $task_id [taskId]
     * @param  [type] $data    [data]
     * @return [void]
     */
    public function onFinish($serv, $task_id, $data)
    {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
        $file    = './temp/date_processs.txt';
        $current = file_get_contents($file);
        $current .= "-------------------------\n";
        $current .= date("Y-m-d H:i:s") . "Task {$task_id} finish\n";
        $current .= print_r($data, true) . "\n";
        $current .= "-------------------------\n";
        file_put_contents($file, $current);

    }
}
$server = new DataProcessServer();

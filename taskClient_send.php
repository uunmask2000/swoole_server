<?php
/**
 * Created by PhpStorm.
 * User: yangyi
 * Date: 2016/12/7
 * Time: 16:18
 */
  
class taskClient
{
            private $client;
            private $i = 0;
            private $time;
    public function __construct() {
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->on('Connect', array($this, 'onConnect'));
        $this->client->on('Receive', array($this, 'onReceive'));
        $this->client->on('Close', array($this, 'onClose'));
        $this->client->on('Error', array($this, 'onError'));
    }
    public function connect() {
        if(!$fp = $this->client->connect("127.0.0.1", 9511 , 1)) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }
    //connect之后,会调用onConnect方法
    public function onConnect($cli) {

        /*
        fwrite(STDOUT, "Enter Msg:");
        swoole_event_add(STDIN,function(){
            fwrite(STDOUT, "Enter Msg:");
            $msg = trim(fgets(STDIN));
            $this->send($msg);
        });
        */

        for ( $i=0 ; $i<1000 ; $i++ ) 
        {
             //sleep(1);
                $this->send('A'.$i);
        }        
        exit(0);
      
 //$this-> send("親愛的服務器！我連上你啦！"); 
        $this-> send("Runing"); 
$this->time = time();
    }
    public function onClose($cli) {
        echo "Client close connection\n";
    }
    public function onError() {
    }
    public function onReceive($cli, $data) {
        //echo "Received: ".$data."\n";
        /*
                    $this->i ++;
                  
                    if( $this->i >= 10 ) {
                    echo "Use Time: " . ( time() - $this->time) . "秒\n";
                    echo "Received: ".$data."\n";
                    exit(0);
                    }
                    else {

                    // $cli->send("Get");
                    $cli->send("親愛的服務器！我連上你啦！");
                    //send("親愛的服務器！我連上你啦！"); 
                    }
        */
                    
                    // for (   $this->i  = 0 ;   $this->i  <=1000 ;   $this->i  ++ ) 
                    // {
                    //     $cli->send("親愛的服務器！我連上你啦！");
                    //     if( $this->i  =='1000')
                    //     {  
                    //     echo "Use Time: " . ( time() - $this->time) . "秒\n";
                    //     echo "Received: ".$data."\n";
                    //     exit(0);
                    //     }

                    // }
                    
                    
        for($i=0 ; $i<1000 ; $i++){
                            //sleep(1);
                            $this->send('A'.$i . "\r\n");
                            if( $this->i  =='1000')
                            {  
                            echo "Use Time: " . ( time() - $this->time) . "秒\n";
                            echo "Received: ".$data."\n";
                            exit(0);
                            }
        }  
               
        echo "Use Time: " . ( time() - $this->time) . "秒\n";
        echo "Received: ".$data."\n";   
    }
    public function send($data) {
        $this->client->send($data);
    }
    public function isConnected($cli) {
        return $this->client->isConnected();
    }
}
$client = new taskClient();
$client->connect();
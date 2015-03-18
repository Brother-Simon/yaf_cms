<?php

/**
 * 基于 beanstalk与SW实现了并行执行任务队列
 * @author Jakin
 */

date_default_timezone_set('PRC');
define('APPLICATION_PATH', dirname(__DIR__));
require APPLICATION_PATH .'/vendor/autoload.php';
use Pheanstalk\Pheanstalk;

class Server
{
	public static $instance;

    private $serv;
    public $pheanstalk;
	private $application;

	public function __construct() {

        $this->application = new Yaf_Application( APPLICATION_PATH ."/conf/application.ini");
        $this->application->bootstrap()->run();
        $this->pheanstalk = new Pheanstalk('127.0.0.1');
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(
			array(
				'worker_num' => 2,
                'task_worker_num' => 2,
                'reactor_num' => 2 ,
				'daemonize' => false,
	            'max_request' => 10000,
	            'dispatch_mode' => 1 ,
                'debug_mode'=> 1
			)
		);
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('WorkerStart' , array( $this , 'onWorkerStart'));
        $this->serv->on('Timer', array($this, 'onTimer'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));
        $this->serv->start();
	}

	public function onWorkerStart($serv , $worker_id) {
        echo "onWorkerStart\n";
        // 创建Pheanstalk

        // 只有当worker_id为0时才添加定时器,避免重复添加
        if( $worker_id == 0 ) {
            $serv->addtimer(1000);
            //$serv->addtimer(500);
            //$serv->addtimer(1000);
        }

        //Yaf_Registry::set('Application', $this->application);
        //$model = new SampleModel();
        //$function = new Functions();
        //$yaf_request = new Yaf_Request_Http('/index/rpc');
        //$this->application->getDispatcher()->dispatch($yaf_request);
        /*
        ob_start();
		$this->application->bootstrap()->run();
		ob_end_clean();
        */
	}

    public function onStart( $serv ) {
        echo "Start\n";
        // 实现重启
        //cli_set_process_title("reload_master");
    }

    public function onTimer($serv, $interval) {
        switch( $interval ) {
            case 1000: {
                //echo "Do Thing A at interval 500\n";
                //$arrConfig = Yaf_Application::app()->getConfig();
                //var_dump($arrConfig);
                //print_r($this->application->execute("indexAction") );
                //print_r($this->application->getModules());
                //$conf = Yaf_Registry::get("config");
                echo '1000Timer start ing...' . PHP_EOL;
                $this->serv->task('/index/rpc');
                break;
            }
            case 2000:{
                //echo "Do Thing B at interval 1000\n";
                //$this->pheanstalk->useTube('testtube')->put("job payload goes here\n");
                $job = $this->pheanstalk->watch('testtube')->ignore('default')->reserve();
                echo $job->getData();
                $this->pheanstalk->delete($job);
                $this->pheanstalk->getConnection()->isServiceListening(); // true or false
                break;
            }
            case 500:{
                echo "Do Thing C at interval 500\n";
                break;
            }
        }
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }
    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
    }
    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

    public function onTask($serv,$task_id,$from_id, $data) {

        $yaf_request = new Yaf_Request_Http($data);
        $this->application->getDispatcher()->dispatch($yaf_request);
    }
    public function onFinish($serv,$task_id, $data) {

    }

	public static function getInstance() {
		if (!self::$instance) {
            self::$instance = new Server;
        }
        return self::$instance;
	}
}

Server::getInstance();
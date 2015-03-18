<?php
/**
 * @name IndexController
 * @author lancelot
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract {

    function init(){
        $this->LUA = Yaf_Registry::get('lua');
    }
	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/Test/index/index/index/name/lancelot 的时候, 你就会发现不同
     */
	public function indexAction() {

		//1. fetch query
		//$get = $this->getRequest()->getQuery("get", "default value");
		//$get = HttpServer::$get;
		//2. fetch model
		//$model = new SampleModel();
		//3. assign
		//$this->getView()->assign("name", "test");
		//4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        echo '加载Yaf框架....' . PHP_EOL;

        return FALSE;
	}

    public function rpcAction() {

        $this->LUA->api = "ngx.mysql.get";
        $this->LUA->ngx_db = "mall_atido";
        $this->LUA->ngx_tab = "exam_system_topic_content";
        $this->LUA->id = 1;
        $data = $this->LUA->get();
        echo json_encode($data). PHP_EOL;

        echo 'RPC调用执行成功...' . PHP_EOL;
        return FALSE;
    }

}

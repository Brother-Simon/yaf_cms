<?php
class CT_Media {
    private static $instance;
    private static $config = array();

    /**
     * 获取实例
     */
    public static function getInstance()
    {
        if(self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * 构造函数
     */
    public function __construct() {
        //获取服务器配置
        $this->getServers();
    }

    /*
     * 获取服务器配置
     */
    public function getServers(){
        if(!self::$config){
            $this->CT = new CT_Api();
            $this->CT->api = 'system.config.get';
            $this->CT->file = 'media';
            $this->CT->item = 'music';
            $data = $this->CT->get();
            if($data['code']==1 && $data['response']){
                self::$config = $data['response'];
            }
        }
    }

    /**
     * 获取文件URL
     */
    public function getUrl($name,$sort){
       if($sort){
           $name = $sort.'/'.urlencode($name);
       }
       $url = self::$config['domain'].self::$config['prefix'].$name;
       return $url;
    }
}
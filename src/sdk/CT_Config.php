<?php
/**
 * 全局设置参数设置
 */
class CT_Config {
    //存放全局参数
    private $_Config;

    /**
     * @var  CT_Config
     */
    private static $_init;

    private function __construct() {
        $this->_Config = require_once dirname(dirname(__FILE__)).'/sdk_config/CT_Config.inc.php';
        $this->_Config['postMode'] = array('GET' => 'getSend' , 'POST' => 'postSend');
    }

    /**
     * @return CT_Config
     */
    public static function Init () {
        if (! self::$_init) {
            self::$_init = new CT_Config();
        }
        return self::$_init;
    }


    /**
     * 设置应用key
     *
     * @param int $key
     * @return CT_Config
     */
    public function setAppKey ($key) {
        $this->_Config['key'] = $key;

        return $this;
    }

    /**
     * 设置应用secret
     *
     * @param string $Secret
     * @return CT_Config
     */
    public function setAppSecret ($secret) {
        $this->_Config['secret'] = $secret;

        return $this;
    }


    /**
     * 返回全局配置参数
     *
     * @return object
     */
    public function getConfig() {
        return (object)$this->_Config;
    }
}
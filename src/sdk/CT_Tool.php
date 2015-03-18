<?php

require_once 'CT_Api.php';
/*
 * SDK ：CT_Tool
 * 功能: 工具函数处理类
 */

class CT_Tool {

    public $CT;

    public function __construct() {

        $this->CT = new CT_Api();
    }

    /*
     * 功能：设置Cookie信息
     * 调用：CT_Api::setCookieMsg
     * 参数：$key：cookie保存的key ,$msg：cookie要保存的信息
     */

    public function setCookieMsg($key, $msg) {
        //获取应用cookie设置
        $this->CT->api = 'user.passport.getConfig';
        $configData = $this->CT->get();
        $config = $configData['response'];

        $config['cookietime'] = time() + $config['cookietime'];
        setcookie($key, $this->CT->authcode($msg, 'ENCODE', $this->CT->config->secret), $config['cookietime'], $config['cookiepath'], $config['cookiedomain']);
    }

    /*
     * 功能：获取Cookie信息
     * 调用：CT_Api::getCookieMsg
     * 参数：key：cookie保存的key信息
     */

    public function getCookieMsg($key) {
        if (!empty($_COOKIE[$key])) {
            return $this->CT->authcode($_COOKIE[$key], 'DECODE', $this->CT->config->secret);
        }
        return false;
    }

    /*
     * 功能：删除Cookie信息
     * 调用：CT_Api::deleteCookieMsg
     * 参数：key：cookie保存的key信息
     */

    public function deleteCookieMsg($key) {
        if (!empty($_COOKIE[$key])) {
            //获取应用cookie设置
            $this->CT->api = 'user.passport.getConfig';
            $configData = $this->CT->get();
            $config = $configData['response'];
            setcookie($key, '', time() - 3600, $config['cookiepath'], $config['cookiedomain']);
            return true;
        }
        return false;
    }

}
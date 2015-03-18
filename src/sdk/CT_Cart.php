<?php

require_once 'CT_Curl.php';
require_once 'CT_User.php';
/*
 * SDK ：CT_Cart
 * 功能: shopping cart 处理类
 */

class CT_Cart {

    private $CT_User;
    private $CT_Util;
    private $appUrl = "http://192.168.0.124:8088/";
    private $user;

    public function __construct() {

        $this->CT_User = new CT_User();
        $this->CT_Util = new CT_Curl();
        $this->user = $this->CT_User->isLogin();
    }

    /**
     * 功能：获取购物车信息
     * 调用：CT_Cart::get
     */
    public function get() {

        if ($this->user) {
            $url = $this->appUrl . $this->user['id'];
            $this->CT_Util->fetch($url);
            $result = $this->CT_Util->results;
            return json_decode($result);
        }
    }

    /**
     * 功能：添加商品到购物车
     * 调用：CT_Cart::add
     * 参数：$gid:int商品id,$quantity:int数量
     */
    public function add($gid, $quantity) {

        if ($this->user) {
            $url = $this->appUrl . $this->user['id'] . '/add/' . $gid . '/' . (int) $quantity;
            $this->CT_Util->fetch($url);
            $result = $this->CT_Util->results;
            return json_decode($result);
        }
    }

    /**
     * 功能：添加多个商品到购物车
     * 调用：CT_Cart::add
     * 参数：$ids：string 商品串[1,2;3,4] 多个商品用','分割
     */
    public function addM($ids, $userId = 0) {

        if ($this->user) {
            $url = $this->appUrl . $this->user['id'] . '/addM/' . $ids;
            $this->CT_Util->fetch($url);
            $result = $this->CT_Util->results;
            return json_decode($result);
        } else {
            if ($userId > 0) {
                $url = $this->appUrl . $userId . '/addM/' . $ids;
                $this->CT_Util->fetch($url);
                $result = $this->CT_Util->results;
                return json_decode($result);
            }
        }
    }

    /**
     * 功能：从购物车删除一个商品
     * 调用：CT_Cart::remove
     * 参数：$gid:int商品id
     */
    public function remove($gid) {

        if ($this->user) {
            $url = $this->appUrl . $this->user['id'] . '/remove/' . $gid;
            $this->CT_Util->fetch($url);
            $result = $this->CT_Util->results;
            return json_decode($result);
        }
    }

    /**
     * 功能：从购物车删除多个商品
     * 调用：CT_Cart::add
     * 参数：$ids：string 商品串[1,2;3,4] 多个商品用','分割
     */
    public function removeM($ids) {

        if ($this->user) {
            $url = $this->appUrl . $this->user['id'] . '/removeM/' . $ids;
            $this->CT_Util->fetch($url);
            $result = $this->CT_Util->results;
            return json_decode($result);
        }
    }

    /**
     * 功能：清空购物车
     * 调用：CT_Cart::flush
     */
    public function flush() {

        if ($this->user) {
            $url = $this->appUrl . $this->user['id'] . '/flush';
            $this->CT_Util->fetch($url);
            $result = $this->CT_Util->results;
            return json_decode($result);
        }
    }

}

?>

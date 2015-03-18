<?php

require_once 'CT_Api.php';
/*
 * SDK ：CT_Cps
 * 功能: CPS函数处理类
 */

class CT_Cps {

	public $CT;
	private $key = "ATIDO_CPS";

	public function __construct() {

		$this->CT = new CT_Api();
	}

	/*
	 * 功能：设置Cps信息
	 * 调用：CT_Cps::setCps
	 * 参数：$key：cookie保存的key ,$msg：cookie要保存的信息
	 */

	public function setCps($cpsid) {
		//获取应用cookie设置
		$this->CT->api = 'user.passport.getConfig';
		$configData = $this->CT->get();
		$config = $configData['response'];
		$cookiedomain = explode('.',$_SERVER['HTTP_HOST']);
		$config['cookiedomain'] = '.'.$cookiedomain[1].'.'.$cookiedomain[2];
		if($config['cookiedomain'] != '.yidejia.com' && $config['cookiedomain'] != '.atido.com'){
			$config['cookiedomain'] = '.atido.com';
		}
		$config['cookietime'] = time() + $config['cookietime'];
		setcookie($this->key, $this->CT->authcode($cpsid, 'ENCODE', $this->CT->config->secret), time() + $config['cookietime'], $config['cookiepath'], $config['cookiedomain']);
	}

	/*
	 * 功能：设置Cps信息
	 * 调用：CT_Cps::getCps
	 * 参数：$key：cookie保存的key ,$msg：cookie要保存的信息
	 */

	public function getCps() {
		//获取应用cookie设置
		if (!empty($_COOKIE[$this->key])) {
			return $this->CT->authcode($_COOKIE[$this->key], 'DECODE', $this->CT->config->secret);
		}
		return false;
	}

	/*
	 * 功能：删除Cookie信息
	 * 调用：CT_Cps::deleteCps
	 * 参数：key：cookie保存的key信息
	 */

	public function deleteCps() {
		if (!empty($_COOKIE[$this->key])) {
			//获取应用cookie设置
			$this->CT->api = 'user.passport.getConfig';
			$configData = $this->CT->get();
			$config = $configData['response'];
			setcookie($this->key, '', time() - 3600, $config['cookiepath'], $config['cookiedomain']);
			return true;
		}
		return false;
	}

}

?>

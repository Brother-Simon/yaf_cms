<?php

require_once 'CT_Api.php';
/*
 * SDK ：CT_User
 * 功能: 用户通行证处理类
 */

class CT_User {

	public $CT;

	public function __construct() {

		$this->CT = new CT_Api();

	}

	/*
	 * 功能：注册新用户
	 * 调用：CT_User::register($data)
	 * 参数：$data; 用户信息 必须包含 $data[‘username’]或$data[‘email’]  $data[‘password’]  密码应为明文  $callback 回调 可选 格式 funtion callback($data){}
	 */

	public function register($data, $callback = null) {
		$this->CT->api = 'user.passport.register';
		$this->CT->setParams($data);
		$return = $this->CT->put();

		if ($return['code'] == 1003) {

			if ($callback) {
				$data['id'] = int($return['response']);
				call_user_func_array($callback, array($data));
			}
			//注册成功 进行登陆
			$this->login($data['username'], $data['password']);
			return true;
		}
	}

	/*
	 * 功能：用户登陆
	 * 调用：CT_User::login($username,$password,$remeber)
	 * 参数：参数均为必须，目前支持用户名和email登陆 登陆成功后 会写$_COOKIE[‘user_auth’] $remeber 默认为false $_COOKIE生命周期跟浏览器SESSION相同 为true时 有效期为一年
	 */

	public function login($username, $password, $remeber = false) {

		$this->CT->api = 'user.passport.login';
		$this->CT->username = $username;
		$this->CT->password = $password;

		$this->CT->login_ip = $this->CT->getIP();

		$return = $this->CT->put();

		if ($return['code'] == 1000 && $return['response']['customer_id'] > 0) {
			//登陆成功，设置cookie
			//获取应用cookie设置
			$this->CT->api = 'user.passport.getConfig';
			$configData = $this->CT->get();
			$config = $configData['response'];
			if (!$remeber) {
				$config['cookietime'] = 0;
			} else {
				$config['cookietime'] = time() + $config['cookietime'];
			}
			//echo $this->CT->config->secret;exit;
				
			$cookiedomain = explode('.',$_SERVER['HTTP_HOST']);
			$config['cookiedomain'] = '.'.$cookiedomain[1].'.'.$cookiedomain[2];
			if($config['cookiedomain'] != '.yidejia.com' && $config['cookiedomain'] != '.atido.com'){
				$config['cookiedomain'] = '.atido.com';
			}
			setcookie('user_auth', $this->CT->authcode($return['response']['customer_id'] . "||" . $return['response']['handset'] . "||" . $return['response']['email'] . "||" . $return['response']['customer_nick'], 'ENCODE', $this->CT->config->secret), $config['cookietime'], $config['cookiepath'], $config['cookiedomain']);
			//获得同步登录的代码
			//$this->CT->api = 'user.passport.synLogin';
			//$this->CT->id = $return['response']['id'];
			//$synLoginReturn = $this->CT->get();
			//return $synLoginReturn['response'];
		}
		return $return;
	}

	/*
	 * 功能：退出登陆
	 * 调用：CT_User::logout()
	 * 参数：无
	 */

	public function logout() {
		//获取应用cookie设置
		$this->CT->api = 'user.passport.getConfig';
		$configData = $this->CT->get();
		$config = $configData['response'];
		
		$cookiedomain = explode('.',$_SERVER['HTTP_HOST']);
		$config['cookiedomain'] = '.'.$cookiedomain[1].'.'.$cookiedomain[2];
		if($config['cookiedomain'] != '.yidejia.com' && $config['cookiedomain'] != '.atido.com'){
			$config['cookiedomain'] = '.atido.com';
		}
			
		setcookie('user_auth', '', time() - 3600, $config['cookiepath'], $config['cookiedomain']);
		//获得同步退出的代码
		//$this->CT->api = 'user.passport.synLogout';
		//$synLogoutReturn = $this->CT->get();
		//return $synLogoutReturn['response'];
		return true;
	}

	/*
	 * 功能：是否登陆，未登陆，返回false 已登陆 返回用户ID和用户名Array(‘id’=>$id,‘username’=>$username,'email'=>$email,'nickname'=>$nickname)
	 * 调用：CT_User::isLogin()
	 * 参数：无
	 */

	public function isLogin() {

		if (!empty($_COOKIE['user_auth'])) {
			$user_auth = $this->CT->authcode($_COOKIE['user_auth'], 'DECODE', $this->CT->config->secret);
			$user = explode('||', $user_auth);
			if ($user[0] > 0) {
				$arr['id'] = $user[0];
				$arr['username'] = $user[1];
				$arr['email'] = $user[2];
				$arr['nickname'] = $user[3];
				return $arr;
			}
		}
		return false;
	}

	/*
	 * 功能：通过验证码重设密码
	 * 调用：CT_User::updateNewPassword($keycode,$password)
	 * 参数：$id 客户ID 必须，$oldPwd 旧密码 必须 $password 新密码 必须  $callback 回调 可选 格式 funtion callback($userId,$password){}
	 */

	public function updateNewPassword($id, $oldPwd, $password, $callback = null) {
		$this->CT->api = 'user.passport.updateNewPassword';
		$this->CT->id = $id;
		$this->CT->oldPwd = $oldPwd;
		$this->CT->password = $password;
		$return = $this->CT->put();

		if ($return['code'] == 1) {
			if ($callback) {
				$id = int($return['response']);
				call_user_func_array($callback, array($id, $password));
			}
		}
		return $return;
	}

	/*
	 * 功能：通过email找回密码
	 * 调用：CT_User::getPasswordBack($email)
	 * 参数：$email为必须参数，email正确 会重置用户密码并把密码发送到用户邮箱中
	 *        $model为必须参数,调用指定的密码模版
	 */

	public function getPasswordBack($email, $model = 'pregnancyGetPassword') {
		$this->CT->api = 'user.passport.getPasswordBack';
		$this->CT->email = $email;
		$this->CT->model = $model;
		$return = $this->CT->put();
		return $return;
	}

	/*
	 * 功能：获取天气
	 * 调用：CT_User::getUserWeather($userId)
	 * 参数：$userId 用户ID 可选 若为空则从当前登陆Cookie中拿
	 */

	public function getUserWeather($userId = '') {
		if (!$userId) {
			$userInfo = $this->isLogin();
			if (!$userInfo) {
				return '';
			}
			$userId = $userInfo['id'];
		}
		$this->CT->api = 'user.info.get';
		$this->CT->id = $userId;
		$this->CT->fields = 'areaid';
		$return = $this->CT->get();

		$areaId = $return['response']['areaid'];

		//有设置地区
		if ($areaId != 0) {
			$this->CT->api = 'common.region.getPath';
			$this->CT->areaId = $areaId;
			$region = $this->CT->get();
			$city = $region['response']['city']['city'];
		} else {
			//根据用户ip地址获取天气信息
			$this->CT->api = 'common.region.getRegion';
			$this->CT->ip = $this->CT->getIP();
			$region = $this->CT->get();
			$city = $region['response']['city'];
		}

		if ($city == '') {
			return '';
		}
		$this->CT->api = 'common.weather.get';
		$this->CT->city = $city;
		$weather = $this->CT->get();
		return $weather;
	}

	/*
	 * 功能：获取用户可操作的按钮集合
	 * 调用：CT_User::getButtonList($userId,$className)
	 * 参数：$userId 用户ID 用户ID,$className 操作的模块菜单名称 如为空 则返回符合条件的全部信息
	 */

	public function getButtonList($userID, $className = null) {
		$this->CT->api = "user.info.getButtonList";
		$this->CT->userID = $userID;
		$this->CT->className = $className;
		$data = $this->CT->get();
		return $data;
	}

}

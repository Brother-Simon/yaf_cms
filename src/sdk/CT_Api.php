<?php

require_once 'CT_Config.php';
//require_once 'CT_Util.php';
require_once 'CT_Curl.php';

/*
 * SDK ：CT_Api
 * 功能: SDK请求类，通过CT_Api向平台发起请求
 */

class CT_Api {

    protected $_data;
    private $_params = array();
    public $config;
    private $_systemParam;
    private $timeStamp;
    public static $CT_Util;
    private static $CT_Upload;
    private static $CT_File;
    private static $CT_Media;
    private $_dbno;
    private $_prefix;
    private $_fw = "atido";

    public function __construct($f = "") {
        if (!self::$CT_Util) {
            self::$CT_Util = new CT_Curl();
            //self::$CT_Util = new CT_Util();
        }

        $config = CT_Config::Init();
        $this->config = $config->getConfig();
        $f == "lua" && $this->_fw = "lua";
        
        $this->timeStamp = time();
    }

    public function __set($name, $value) {
        if ($this->_data && $this->config->autoRestParam) {

            $this->_params = array();
            $this->_data = null;
        }

        $this->_params[$name] = trim($value);
    }

    /*
     * 功能：批量设置用户请求参数
     * 调用：CT_Api::setParams($userParam)
     * 参数：$userParam; 必须 关联数组（目前已支持多维数组）
     */

    public function setParams($userParam) {
        if (is_array($userParam)) {
            $this->_params = array_merge($this->_params, $userParam);
        }
    }

    //数组参数封装
    public function arrayToParams($key, $array) {
        foreach ($array as $k => $v) {
            $newKey = $key . '[' . $k . ']';
            if (is_array($v)) {
                $this->arrayToParams($newKey, $v);
            } else {
                $this->_params[$newKey] = $v;
            }
        }
    }

    public function __get($name) {
        if (!empty($this->_params[$name])) {

            return $this->_params[$name];
        }
    }

    public function __unset($name) {
        unset($this->_params[$name]);
    }

    public function __isset($name) {
        return isset($this->_params[$name]);
    }

    public function __destruct() {
        $this->_params = array();
    }

    public function __toString() {
        return $this->createStrParam($this->_params);
    }

    private function formatUserParam($param) {
        if (strtoupper($this->config->charset) != 'UTF-8') {
            if (function_exists('mb_convert_encoding')) {
                if (is_array($param)) {
                    foreach ($param as $key => $value) {
                        $param[$key] = @mb_convert_encoding($value, 'UTF-8', $this->config->charset);
                    }
                } else {
                    $param = @mb_convert_encoding($param, 'UTF-8', $this->config->charset);
                }
            } elseif (function_exists('iconv')) {
                if (is_array($param)) {
                    foreach ($param as $key => $value) {
                        $param[$key] = @iconv($this->config->charset, 'UTF-8', $value);
                    }
                } else {
                    $param = @iconv($this->config->charset, 'UTF-8', $param);
                }
            }
        }

        return $param;
    }

    private function formatData($data) {
        if (strtoupper($this->config->charset) != 'UTF-8') {
            if (function_exists('mb_convert_encoding')) {
                $data = str_replace('utf-8', $this->config->charset, $data);
                $data = @mb_convert_encoding($data, $this->config->charset, 'UTF-8');
            } elseif (function_exists('iconv')) {
                $data = str_replace('utf-8', $this->config->charset, $data);
                $data = @iconv('UTF-8', $this->config->charset, $data);
            }
        }

        return $data;
    }

    private function send($mode = 'GET') {

        $tempParam = $this->_params;
        foreach ($tempParam as $key => $value) {
            $tempParam[$key] = $this->formatUserParam($value);
        }

        $systemdefault['key'] = $this->config->key;
        $systemdefault['format'] = strtolower($this->config->format);
        $systemdefault['ts'] = $this->timeStamp;
        $tempParam = array_merge($tempParam, $systemdefault);
        $this->_params = array_merge($this->_params, $systemdefault);

        $mode = strtoupper($mode);
        $ReadMode = array_key_exists($mode, $this->config->postMode) ? $this->config->postMode[$mode] : 'postSend';

        $this->_data = $this->$ReadMode($tempParam);

        return $this;
    }

    private function getData() {
        if (empty($this->_data)) {
            return false;
        }
        $data = $this->formatData($this->_data);
        if ($this->config->format == 'array') {
            return json_decode($data, true);
        } else if ($this->config->format == 'xml') {
            return $this->arrayToXml(json_decode($data, true));
        } else {
            return $data;
        }
    }

    private function arrayToXml($array, $xml = null) {
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }
        $xml == null && $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><getdata></getdata>");
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = "Node_" . (string) $key;
            }
            $key = preg_replace('/[^a-z]/i', '', $key);
            if (is_array($value)) {
                $node = $xml->addChild($key);
                $this->arrayToXml($value, $node);
            } else {
                $value = htmlentities($value, ENT_COMPAT, 'utf-8');
                $xml->addChild($key, $value);
            }
        }
        return $xml->asXML();
    }

    /*
     * 功能：取得当前请求参数 （用于调试）
     * 调用：CT_Api::getParam()
     * 参数：无
     */

    public function getParam() {
        return $this->_params;
    }

    private function createSign($paramArr) {
        $sign = md5('ChunTian' . $this->config->key . $this->config->secret . $this->_params['api'] . $this->timeStamp);
        $this->_systemParam['sign'] = $sign;
        return $sign;
    }

    private function createStrParam($paramArr) {
        return http_build_query($paramArr);
    }

    //以GET方式访问api服务
    private function getSend($paramArr) {
        //组织参数
        $this->_systemParam['sign'] = $this->createSign($paramArr);
        $paramArr['sign'] = $this->_systemParam['sign'];
        $strParam = $this->createStrParam($paramArr);
        $this->_systemParam['url'] = ($this->_fw =="atido" ? $this->config->url : $this->config->lua) . '?' . $strParam;
        //访问服务
        self::$CT_Util->fetch($this->_systemParam['url']);
        $result = self::$CT_Util->results;
        //返回结果
        return $result;
    }

    //以POST方式访问api服务
    private function postSend($paramArr) {
        //组织参数，CT_Util类在执行submit函数时，它自动会将参数做urlencode编码，所以这里没有像以get方式访问服务那样对参数数组做urlencode编码
        $this->_systemParam['sign'] = $this->createSign($paramArr);
        $paramArr['sign'] = $this->_systemParam['sign'];
        $this->_systemParam['url'] = ($this->_fw =="atido" ? $this->config->url : $this->config->lua);
        //访问服务
        self::$CT_Util->submit($this->_systemParam['url'], $paramArr);
        $result = self::$CT_Util->results;
        //返回结果
        return $result;
    }

    /*
     * 功能：用GET方式向平台请求数据
     * 调用：CT_Api::get()
     * 参数：无
     */

    public function get() {
        return $this->send('get')->getData();
    }

    /*
     * 功能：用POST方式向平台请求数据和发送数据  与post()等同
     * 调用：CT_Api::put()
     * 参数：无
     */

    public function put() {
        return $this->send('post')->getData();
    }

    /*
     * 功能：用POST方式向平台请求数据和发送数据  与put()等同
     * 调用：CT_Api::post()
     * 参数：无
     */

    public function post() {
        return $this->put();
    }

    /*
     * 功能：获得当前向平台请求的url (用于调试，适用于用GET方法的请求)
     * 调用：CT_Api::getUrl()
     * 参数：无
     */

    public function getUrl() {
        return !empty($this->_systemParam['url']) ? $this->_systemParam['url'] : '';
    }

    /*
     * 功能：获得当前向平台发起请求的签名 (用于调试)
     * 调用：CT_Api::getSign()
     * 参数：无
     */

    public function getSign() {
        return !empty($this->_systemParam['sign']) ? $this->_systemParam['sign'] : '';
    }

    /*
     * 功能：获得当前用户的客户端IP
     * 调用：CT_Api::getIP()
     * 参数：无
     */

    public function getIP() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        return $ip;
    }

    //加解密
    public function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        $ckey_length = 4;

        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /*
     * 功能：向平台发起获取列表数据的请求
     * 调用：CT_Api::getList($condition,$option,$fields)
     * 参数：$condition: 查询的条件 格式为数组 如 $condition = array(‘type’=>1,’user_id’=>2)或者$condition = “type=1 and user_id=2”
     *       $option: 格式 array(‘order’=>’id desc’,’offset’=>0,’limit’=>10,’group’=>’ id’); 每个值都是可选的 默认为null
     *       $fields: 要查询的字段，默认为所有 即 *
     */

    public function getList($condition = array(), $option = array('order' => null, 'offset' => null, 'limit' => null, 'group' => null), $fields = "*") {
        $w = "";
        if (is_array($condition)) {
            $temp = array();
            foreach ($condition as $key => $val) {
                $temp[] = $key . "='" . $val . "'";
            }
            $w = implode(' and  ', $temp);
        } else {
            $w = $condition;
        }
        $params = array('where' => $w, 'option' => $option, 'fields' => $fields);
        $this->setParams($params);
        $data = $this->get();
        return $data;
    }

    /*
     * 功能：文件上传 上传到本地临时目录同时储存到mongodb中
     * 调用：CT_Api::upload($uploadDir,$allowExts,$maxSize,$afterMoved)
     * 参数：$uploadDir: 本地临时文件上传目录
     *       $allowExts: 允许上传的文件后缀 默认为* 即所有  格式 jpg|gif|bmp 或者 jpg,gif,bmp
     *       $maxSize: 允许上传的最大文件大小 默认 2048 单位 KB
     *       $afterMoved：自定义的回调函数，默认 无  格式 function uploadCall($filename){//do something} 参数为上传后的文件名 可用于保存到数据库
     */

    public function upload($uploadDir, $allowExts = '*', $maxSize = 2048, $afterMoved = null) {
	
        if (!self::$CT_Upload) {
            include_once 'CT_Upload.php';
            self::$CT_Upload = new CT_Upload();
        }
        //移动后的文件路径
        $moveFiles = array();
        //上传后的文件名
        $fileNames = array();
        //$_FILES
        $files = self::$CT_Upload->getFiles();

        $i = 0;
        foreach ($files as $file) {
            if (!$file->getFilename() || !$file->check($allowExts, $maxSize)) {
                //文件不存在或者上传的文件类型不符或者超过了大小限制。
                continue;
            }
            //生成文件名
            $fileName = $this->getFileName($file->getFilename());

            //获取mongodb存储文件名
            $storeFileName = $this->getStoreFileName($fileName);
            //获取返回给用户的文件名
            $userFileName = $this->getUserFileName($storeFileName);

            $uploadDir = rtrim($uploadDir, '\\/');
            $filePath = $uploadDir . '/' . $fileName;

            if ($file->move($filePath)) {
                //如果定义了回调函数，则调用回调函数
                if ((is_array($afterMoved) && method_exists($afterMoved[0], $afterMoved[1]) || function_exists($afterMoved))) {
                    call_user_func_array($afterMoved, array($userFileName));
                }
                $moveFiles[$i]['filepath'] = $filePath;
                $moveFiles[$i]['filename'] = $storeFileName;
                $moveFiles[$i]['db_no'] = $this->_dbno;
                $fileNames[] = $userFileName;
                $i++;
            }
        }
        //储存所有文件
        $this->storeFiles($moveFiles);
		foreach ($moveFiles as $key=>$val){				
			$this->Ypyun($val['filepath'],$fileNames[$key]);
		}
        return $fileNames;
    }

    /*
     * 功能：将本地文件储存到mongodb中
     * 调用：CT_Api::storeFiles($storeFiles)
     * 参数：$storeFiles: 必须
     *       单文件：一维数组 $storeFiles = array('filepath'=>'本地文件路径','filename'=>'储存在mongodb中的文件名','db_no'=>'mongodb数据库编号')
     *       多文件：二维数组 $storeFiles = array(array('filepath'=>'本地文件路径','filename'=>'储存在mongodb中的文件名','db_no'=>'mongodb数据库编号'),array('filepath'=>'本地文件路径2','filename'=>'储存在mongodb中的文件名3','db_no'=>'mongodb数据库编号2'))
     */

    public function storeFiles($storeFiles) {
        if (!self::$CT_File) {
            include_once 'CT_File.php';
            self::$CT_File = CT_File::getInstance();
        }
        return self::$CT_File->store($storeFiles);
    }

    private function getFilePrefix() {
        $this->_prefix = date('Y/m/d/');
    }

    /*
     * 功能：获得返回给用户的文件名
     * 调用：CT_Api::getUserFileName($storeFileName)
     * 参数：$storeFileName 储存到monogodb中的文件名
     */

    public function getUserFileName($storeFileName) {
        //dbno
        $this->generateDbNo();
        return $this->_dbno . '/' . $storeFileName;
    }

    /*
     * 功能：获得储存到monogodb中的文件名
     * 调用：CT_Api::getStoreFileName($filename)
     * 参数：$filename 唯一临时文件名
     */

    public function getStoreFileName($filename) {
        //文件名前缀
        $this->getFilePrefix();
        //储存文件名
        $storeFileName = $this->_prefix . $filename;
        return $storeFileName;
    }

    /*
     * 功能：获得生成的唯一临时文件名
     * 调用：CT_Api::getFileName($file)
     * 参数：$file 文件名
     */

    public function getFileName($file) {
        //文件名
        $id = $this->getFileId();
        //临时文件名
        $filename = $id . '.' . strtolower($this->getPathInfo($file, 'extension'));
        return $filename;
    }

    private function getFileId() {
        $uuid = substr(sha1(uniqid()), 0, 4);
        $order_id = '';
        for ($i = 0, $j = strlen($uuid); $i < $j; $i++) {
            $order_id .= ord($uuid{$i});
        }
        $order_id = date('His') . $order_id;
        return base_convert($order_id, 10, 16);
    }

    private function generateDbNo() {
        if (!self::$CT_File) {
            include_once 'CT_File.php';
            self::$CT_File = CT_File::getInstance();
        }
        $this->_dbno = self::$CT_File->getDbNo();
    }

    /*
     * 功能：获得当前文件存储的数据库编号
     * 调用：CT_Api::getDbNo()
     * 参数：无
     */

    public function getDbNo() {
        return $this->_dbno;
    }

    /*
     * 功能：获得上传的文件访问URL
     * 调用：CT_Api::getFileUrl($filename)
     * 参数：$filename: 文件名 必须  一般为上传后存在数据库里的文件名
     *       $size 图片尺寸 默认为空 表示原图 格式 100x120 中间为英文x 此参数仅针对图片
     */

    public function getFileUrl($filename, $size = '') {
        include_once 'CT_File.php';
        return CT_File::getUrl($filename, $size);
    }

    /**
     * 功能：获得上传的媒体文件访问URL
     * 调用：CT_Api::getMediaUrl($filename)
     * 参数：$filename: 文件名 必须  一般为上传后存在数据库里的文件名
     *      $sort:文件分类
     */
    public function getMediaUrl($filename, $sort) {
        if (!self::$CT_Media) {
            include_once 'CT_Media.php';
            self::$CT_Media = CT_Media::getInstance();
        }
        return self::$CT_Media->getUrl($filename, $sort);
    }

    //获取文件信息 $type : filename dirname  extension
    private function getPathInfo($file_name, $type = 'filename') {
        $file_path = pathinfo($file_name);
        return isset($file_path[$type]) ? $file_path[$type] : $file_path;
    }

    /*
     * 功能：将本地文件储存到mongodb中
     * 调用：CT_Api::storeFile($filepath)
     * 参数：$filepath: 必须 本地文件路径
     */

    public function storeFile($filepath) {
        if (is_file($filepath)) {
		
            $path_parts = pathinfo($filepath);
            //生成文件名[唯一性]
            $fileName = $this->getFileName($path_parts['basename']);
            //获取mongodb存储文件名
            $storeFileName = $this->getStoreFileName($fileName);
            //获取返回给用户的文件名
            $userFileName = $this->getUserFileName($storeFileName);
            //储存所有文件
            $this->storeFiles(array('filepath' => $filepath, 'filename' => $storeFileName, 'db_no' => $this->_dbno));
            //返回保存到mongodb中的文件名
			if(!empty($userFileName))
				$this->Ypyun($filepath,$userFileName);
            return $userFileName;
        }
    }
	
	// 上传到又拍云
	public function Ypyun($filepath,$fileNames){
		
		include_once 'upyun.class.php';
		$upyun = new UpYun('atido-pic', 'songweihang', 'atido123!@#');
		$fh = fopen($filepath, 'rb');
    	$rsp = $upyun->writeFile('/'.$fileNames, $fh, True);   // 上传图片，自动创建目录
    	fclose($fh);	
	}
}
<?php

require_once 'CT_Api.php';

/**
 * CT_File Mongodb gridfs
 *
 */
class CT_File {

    private static $instance;
    public $fs;
    public $mongo;
    private static $config = array();

    /**
     * 获取实例
     */
    public static function getInstance() {

        if (self::$instance) {
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
        self::getServers();

        $dsn = "mongodb://";
        if (self::$config['servers']) {
            $servers = array();
            foreach (self::$config['servers'] as $v) {
                $servers[] = $v['host'] . ":" . $v['port'];
            }
            $this->mongo = new Mongo($dsn . implode(",", $servers), array("replicaSet" => self::$config['replicaSet'], 'readPreference' => Mongo::RP_SECONDARY));
        }
    }

    /*
     * 获取数据库no
     */

    public function getDbNo() {

        $no = mt_rand(1, self::$config['db_num']);
        $no = intval($no + self::$config['db_full']);
        return $no;
    }

    /*
     * 获取服务器配置
     */

    public static function getServers() {

        $CT = new CT_Api();
        $CT->api = 'system.config.get';
        $CT->file = 'mongodb';
        $CT->item = 'upload';
        $data = $CT->get();
        if ($data['code'] == 1 && $data['response']) {
            self::$config = $data['response'];
        }
    }

    /*
     * 选择数据库 获取gridFs句柄
     */

    public function getFs($dbNo) {

        $db = $this->mongo->selectDB(self::$config['db_prefix'] . $dbNo);
        $this->fs = $db->getGridFS();
    }

    /**
     * 获取文件URL
     */
    public static function getUrl($name, $size) {

        if (empty(self::$config)) {
            //获取服务器配置
            self::getServers();
        }
        if ($size) {
            $name = $size . '/' . $name;
        }
        $url = self::$config['domain'] . self::$config['prefix'] . $name;
        return $url;
    }

    /**
     * 获得文件句柄
     * @param string $name
     * @return MongoGridFSFile
     */
    public function file($name) {

        return $this->fs->findOne(array('filename' => $name));
    }

    /**
     * 获得文件内容
     * @param string $name
     */
    public function read($name) {

        $file = $this->file($name);
        if ($file) {
            return $file->getBytes();
        } else {
            return false;
        }
    }

    /*
     * 批量储存所有文件
     */

    public function store($files) {

        if (isset($files['filepath'])) {
            //一维数组 单文件储存
            return $this->write($files);
        } else {
            //多文件存储
            foreach ($files as $file) {
                $this->write($file);
            }
            return true;
        }
    }

    /**
     * 写入文件
     * @param string $file
     * @param string $prefix
     * @param array $extra
     * @param boolean $overWrite
     * @return boolean
     */
    public function write($file, $extra = array(), $overWrite = false) {

        $this->getFs($file['db_no']);
        $extra = (array) $extra + array('filename' => $file['filename']);
        if ($this->file($extra['filename'])) {
            if ($overWrite) {
                $this->delete($extra['filename']);
            } else {
                return true;
            }
        }
        return $this->storeFile($file['filepath'], $extra);
    }

    /*
     * 储存文件
     */

    public function storeFile($file, $extra) {

        return $this->fs->storeFile($file, $extra);
    }

    /**
     * 删除文件
     * @param string $name
     * @return boolean
     */
    public function delete($name) {

        return $this->fs->remove(array('filename' => $name));
    }

    /**
     * 关闭数据库
     */
    public function close() {

        if ($this->mongo) {
            $this->mongo->close();
        }
    }

}

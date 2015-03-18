<?php
//客户端请求接口
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('PRC');

include_once '../sdk/CT_Api.php';

$api = new client_api();
$api ->action();

class client_api {
    //配置信息
    private static $_config = array(
        'secret' => 'chuntian_yqbb_client',
        'api' => array(
            'user.jifen.get',
            'user.info.get'
        )
    );
    
    private  $CT;

    private static $_api;  //请求的API
    private static $_sign; //签名
    private static $_params=array(); //用户参数

    public function __construct () {
        $this->CT = new CT_Api();
        $this->CT->config->format='json';   //统一返回JSON
        self::parseParams();
    }

    public function action(){
        self::checkAuth();
        $prefix = substr(self::$_api, 0, 3);
        if(self::$_api=='system.datetime.get'){
            //返回应用服务器的当前时间
            $return = json_encode(array('code'=>1,'msg'=>'成功','response'=>date('Y-m-d H:i:s')));
        }else if(self::$_api=='system.datetime.format'){
            //转换时间格式
            $return = json_encode(array('code'=>1,'msg'=>'成功','response'=>date('Y-m-d H:i:s',self::$_params['ts'])));
        }else if($prefix == 'CT_'){
            //调用二次封装过的SDK  目前仅能调用无参数的方法
            $return = json_encode(array('code'=>0,'msg'=>'无效的请求'));
            $api = explode('.',self::$_api);
            $action = array_pop($api);
            $class = reset($api);
            include_once '../sdk/'.$class.'.php';
            if (class_exists($class)){
                $obj = new $class();
                if(method_exists($obj, $action)){
                    $return =$obj->$action();
                }
            }
        }else{
            //直接调用平台API
            $this->CT->api = self::$_api;
            $this->CT->setParams(self::$_params);
            $return = $this->CT->get();
        }
        self::outPut($return);
    }

    private static function parseParams(){
        $params = self::filter($_GET);
        self::$_api = $params['api'];
        self::$_sign = $params['sign'];
        unset($params['api'],$params['sign']);
        self::$_params = $params;
    }

    private static function checkAuth(){
       //验证签名是否正确
       if(self::$_sign != self::makeSign()){
           self::outPut(json_encode(array('code'=>0,'msg'=>'客户端签名非法')));
       }
        //检验请求方法是否在允许的列表里
       if(!in_array($api,self::$_config['api'])){
            //self::outPut(json_encode(array('code'=>0,'msg'=>'客户端没有权限请求此API')));
        }
    }

    private static function makeSign(){
        return md5(self::$_api.self::$_config['secret']);
    }

    private static function outPut($data){
        if(is_array($data)){
            $data = json_encode($data);
        }
        exit($data);
    }

    private static function filter($value){
	if(!get_magic_quotes_gpc())
	{
		if( is_array( $value ) )
		{
			foreach($value as $key => $val){
				$value[$key] = self::filter($val);
                        }
		}else{
			$value = addslashes(trim($value));
		}
	}
     return $value;
 }


}
?>
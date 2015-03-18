<?php
/*
*配置文件
*/
return array(
	'key' => 'yqbb', //您的应用key
	'secret' => 'yqbb_365s_chuntian', //您的应用secret 密钥
	'url' => 'http://fw.365ok.com.cn/',  //API请求地址
	'format' => 'array',  //请求返回的数据格式 可选 json xml array
	'charset' => 'UTF-8',  //请求数据的编码，非特殊情况下都为UTF-8
	'autoRestParam' => true  //每次调用API后自动清除原有传入参数
);
<?php

/**
 * @name Bootstrap
 * @author lancelot
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

    private $_config;

    public function _initConfig()
    {
        //把配置保存起来
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->_config);
        //把页面链接数据保存起来
        $data['love'] = array('name' => '爱情', 'slug' => 'love',
            'list' => array(
                array('name' => '单身季节', 'slug' => 'dsjj'),
                array('name' => '恋爱ing', 'slug' => 'loveing'),
                array('name' => '婚姻堡垒', 'slug' => 'hybl'),
                array('name' => '边缘情感', 'slug' => 'blqg')
            )
        );

        $data['family'] = array('name' => '亲情', 'slug' => 'family',
            'list' => array(
                array('name' => '手足之情', 'slug' => 'szzq'),
                array('name' => '母亲怀抱', 'slug' => 'mqhb'),
                array('name' => '父爱如山', 'slug' => 'fars')
            )
        );

        $data['friendly'] = array('name' => '友情', 'slug' => 'friendly',
            'list' => array(
                array('name' => '兄弟连', 'slug' => 'xdl'),
                array('name' => '房中闺蜜', 'slug' => 'fzgm'),
                array('name' => '蓝颜知己', 'slug' => 'lyzj')
            )
        );

        $data['px'] = array('name' => '婆媳', 'slug' => 'px',
            'list' => array(
                array('name' => '麻辣婆媳', 'slug' => 'mlpx')
            )
        );

        $data['gzzc'] = array('name' => '工作职场', 'slug' => 'gzzc',
            'list' => array(
                array('name' => '八卦职场', 'slug' => 'bgzc'),
                array('name' => '潜规则', 'slug' => 'qgz')
            )
        );

        $data['mrxz'] = array('name' => '每日星座', 'slug' => 'mrxz',
            'list' => array(
                array('name' => '星座解说', 'slug' => 'xzjs'),
                array('name' => '星座运程', 'slug' => 'xzyc')
            )
        );
        Yaf_Registry::set('configPage', $data);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        //注册一个插件
        $objSamplePlugin = new SamplePlugin();
        $dispatcher->registerPlugin($objSamplePlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        $router = $dispatcher->getRouter();
        //栏目
        $route = new Yaf_Route_Rewrite(
            "/:slug",
            array(
                "controller" => "column",
                "action" => "index",
            )
        );
        $router->addRoute('column', $route);

        //标签页面路由规则
        $route = new Yaf_Route_Rewrite(
            "/tag/:name",
            array(
                "controller" => "column",
                "action" => "tag",
            )
        );
        $router->addRoute('tag', $route);

        $route = new Yaf_Route_Rewrite(
            "/tag/:name/:p",
            array(
                "controller" => "column",
                "action" => "tag",
            )
        );
        $router->addRoute('tagPage', $route);
        // 列表页分页规则
        $route = new Yaf_Route_Rewrite(
            "/list/:name",
            array(
                "controller" => "column",
                "action" => "list",
            )
        );
        $router->addRoute('list', $route);

        $route = new Yaf_Route_Rewrite(
            "/list/:name/:p",
            array(
                "controller" => "column",
                "action" => "list",
            )
        );
        $router->addRoute('listPage', $route);
        //文章详情页面分页规则
        $route = new Yaf_Route_Rewrite(
            "/article/:id",
            array(
                "controller" => "info",
                "action" => "index",
            )
        );
        $router->addRoute('article', $route);

    }

    public function _initDb(Yaf_Dispatcher $dispatcher)
    {
        // 注册DB类
        $this->_db = new Db($this->_config->mysql->read->toArray());
        Yaf_Registry::set('_db', $this->_db);
    }

    /*
    public function _initSession($dispatcher){
        //session_start();
        //不使用这个了，因为要使用 Yaf_Session::getInstance()->start();
        Yaf_Session::getInstance()->start();
    }
	*/
    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        //在这里注册自己的view控制器，例如smarty,firekylin
    }

}

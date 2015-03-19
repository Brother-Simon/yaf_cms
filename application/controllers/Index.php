<?php

/**
 * @name 私密网
 * @author Jakin
 * @desc 网站首页
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract
{

    public function init()
    {

    }

    //http://yaf.com/index.php?c=index&a=index
    public function indexAction()
    {

        $model = new ArticleModel();
        //1. fetch query
        //$get = $this->getRequest()->getQuery("get", "default value");
        //$get = HttpServer::$get;
        //2. fetch model
        //情感百科
        $tag = $model->getTag(25);
        $this->getView()->assign("tag", $tag);
        //推荐TOP
        $stickyPosts = $model->getStickyPosts();
        $this->getView()->assign("stickyPosts", $stickyPosts);
        return true;
    }


}

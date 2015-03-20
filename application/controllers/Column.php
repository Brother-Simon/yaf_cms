<?php

/**
 * @name 私密网
 * @author Jakin
 * @desc 网站栏目页面
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class ColumnController extends Yaf_Controller_Abstract
{

    public function init()
    {

    }

    // 栏目首页 http://yaf.com/column
    public function indexAction()
    {

        $model = new ArticleModel();
        //情感百科
        $tag = $model->getTag(40);
        $this->getView()->assign("tag", $tag);
        return true;
    }

    // 标签页面 http://yaf.com/list/xxxx
    public function listAction()
    {

        $model = new ArticleModel();
        $fun = new Functions();
        $p = $this->getRequest()->getParam('p');
        $p = ( int )$p > 0 ? ( int )$p : 1;
        $params = array(
            'total_rows' => 100, #(必须)
            'method' => 'html', #(必须)
            'parameter' => '?.html',  #(必须)
            'now_page' => $p,  #(必须)
            'list_rows' => 10, #(可选) 默认为15
        );
        $page = new Page($params);
        $pages = $page->show(1);
        $this->getView()->assign("pages", $pages);
        $name = $this->getRequest()->getParam('name');
        $this->getView()->assign("name", urldecode($name));
        $count = $model->getCount(array('_terms.slug' => $name), 'post_tag');
        $data = $model->getList(array('_terms.slug' => $name), 0, 5, "_posts.ID DESC");
        foreach ((array)$data as $key => $val) {
            $data[$key]['description'] = $fun->cutstr(strip_tags($val['post_content']), '150');
            $term = $model->getTerm($val['ID']);
            $data[$key]['term'] = $term;
            unset($data[$key]['post_content']);
        }
        $this->getView()->assign("data", $data);
        //热点推荐
        $hotData = $model->getList('', "0", "10");
        foreach ($hotData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $hotData[$key]['image'] = $optionsImage['guid'];
            break;
        }
        $this->getView()->assign('hotData', $hotData);
        //获取文字分类
        $data = $model->getClass($name);
        $this->getView()->assign("class", $data);
        return true;
    }

    // 标签页面 http://yaf.com/tag/xxxx
    public function tagAction()
    {
        $model = new ArticleModel();
        $fun = new Functions();
        $name = $this->getRequest()->getParam('name');
        $this->getView()->assign("name", urldecode($name));
        $count = $model->getCount(array('_terms.slug' => $name), 'post_tag');
        $data = $model->getList(array('_terms.slug' => $name), 0, 5, "_posts.ID DESC", 'post_tag');
        foreach ((array)$data as $key => $val) {
            $data[$key]['description'] = $fun->cutstr(strip_tags($val['post_content']), '150');
            $term = $model->getTerm($val['ID']);
            $data[$key]['term'] = $term;
            unset($data[$key]['post_content']);
        }
        $this->getView()->assign("data", $data);

        //热点推荐
        $hotData = $model->getList('', "0", "10");
        foreach ($hotData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $hotData[$key]['image'] = $optionsImage['guid'];
            break;
        }
        $this->getView()->assign('hotData', $hotData);
        //获取文字分类
        $data = $model->getClass($name);
        $this->getView()->assign("class", $data);
        return true;
    }
}

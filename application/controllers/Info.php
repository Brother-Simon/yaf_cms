<?php

/**
 * @name 私密网
 * @author Jakin
 * @desc 网站详情页面
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class InfoController extends Yaf_Controller_Abstract
{

    public function init()
    {

    }

    // 栏目首页 http://yaf.com/column
    public function indexAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = new ArticleModel($id);
        $fun = new Functions();
        $data = $model->get($id);
        $this->getView()->assign('data', $data);
        //获取文章关键词
        $term = $model->getTerm($id);
        $this->getView()->assign('term', $term);
        //编辑推荐 获取该分类最新文章
        $newsData = $model->getList('', "0", "4");
        foreach ((array)$newsData as $key => $val) {
            $newsData[$key]['description'] = $fun->cutstr(strip_tags($val['post_content']), '60');
            unset($data[$key]['post_content']);
        }
        $this->getView()->assign('newsData', $newsData);
        //相关文章
        foreach( $term as $v){ if($v['taxonomy'] == 'category'){   $class = $v;   }  }
        $data = $model->getList(array('_terms.term_id' => $class['term_id']), 0, 8, "_posts.ID DESC");
        foreach ((array)$data as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $data[$key]['image'] = $optionsImage['guid'];
            $data[$key]['description'] = $fun->cutstr(strip_tags($val['post_content']), '80');
            unset($data[$key]['post_content']);
            break;
        }
        $this->getView()->assign('beData', $data);

        return true;
    }

}

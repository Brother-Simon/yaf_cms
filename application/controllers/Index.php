<?php

/**
 * @name 私密网
 * @author Jakin
 * @desc 网站首页
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract
{
    private $configPage;

    public function init()
    {
        $this->configPage = Yaf_Registry::get('configPage');
    }

    //http://yaf.com/index.php?c=index&a=index
    public function indexAction()
    {

        $model = new ArticleModel();
        //情感百科
        $tag = $model->getTag(25);
        $this->getView()->assign("tag", $tag);

        //爱情
        $loveData = $model->getList(array("_terms.term_id" => 6), "0", "4");
        foreach ($loveData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $loveData[$key]['image'] = $optionsImage['guid'];
        }
        $this->getView()->assign('loveData', $loveData);

        //婆媳
        $pxData = $model->getList(array("_terms.term_id" => 6), "0", "5");
        foreach ($pxData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $pxData[$key]['image'] = $optionsImage['guid'];
        }
        $this->getView()->assign('pxData', $pxData);

        //友情
        $friendlyData = $model->getList(array("_terms.term_id" => 6), "0", "5");
        foreach ($friendlyData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $friendlyData[$key]['image'] = $optionsImage['guid'];
            break;
        }
        $this->getView()->assign('friendlyData', $friendlyData);

        //亲情
        $familyData = $model->getList(array("_terms.term_id" => 6), "0", "5");
        foreach ($friendlyData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $familyData[$key]['image'] = $optionsImage['guid'];
            break;
        }
        $this->getView()->assign('familyData', $familyData);

        //星座
        $constellationData = $model->getList(array("_terms.term_id" => 6), "0", "5");
        foreach ($friendlyData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $constellationData[$key]['image'] = $optionsImage['guid'];
            break;
        }
        $this->getView()->assign('constellationData', $constellationData);

        //工作
        $workData = $model->getList(array("_terms.term_id" => 6), "0", "5");
        foreach ($friendlyData as $key => $val) {
            $optionsImage = $model->getOptionsImage($val['ID']);
            $workData[$key]['image'] = $optionsImage['guid'];
            break;
        }
        $this->getView()->assign('workData', $workData);

        //推荐TOP
        $stickyPosts = $model->getStickyPosts();
        $this->getView()->assign("stickyPosts", $stickyPosts);
        //写入配置页面数据
        $this->getView()->assign("configPage", $this->configPage);
        return true;
    }


}

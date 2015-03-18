<?php
require_once 'CT_Api.php';
/*
 * SDK ：CT_Region
 * 功能: 地区类 省 市 区
*/
class CT_Region {

    public  $CT;

    public function __construct () {
        $this->CT = new CT_Api();
    }

    /*
      * 功能：获取所有省份列表
      * 调用：CT_Regoin::getProvince()
      * 参数：无
    */
    public function getProvince() {
        $this->CT->api = 'common.region.getProvince';
        $data = $this->CT->get();
        return $data;
    }


    /*
      * 功能：获取对应省份的城市列表
      * 调用：CT_Regoin::getCity($provinceId)
      * 参数：$provinceId 省份ID 必须
    */
    public function getCity($provinceId) {
        $this->CT->api = 'common.region.getCity';
        $this->CT->provinceId = $provinceId;
        $data = $this->CT->get();
        return $data;
    }

    /*
      * 功能：获取对应城市的地区列表
      * 调用：CT_Regoin::getArea($cityId)
      * 参数：$cityId 城市ID 必须
    */
    public function getArea($cityId) {
        $this->CT->api = 'common.region.getArea';
        $this->CT->cityId = $cityId;
        $data = $this->CT->get();
        return $data;
    }

    /*
      * 功能：根据地区ID 获取省、市、区路径
      * 调用：CT_Regoin::getPath($areaId)
      * 参数：$areaId 地区ID 必须
    */
    public function getPath($areaId) {
        $this->CT->api = 'common.region.getPath';
        $this->CT->areaId = $areaId;
        $data = $this->CT->get();
        return $data;
    }

    /*
      * 功能：获取用户的地区
      * 调用：CT_Regoin::getUserRegion();
      * 参数：无
    */
    public function getUserRegion() {
        $this->CT->api = 'common.region.getRegion';
        $this->CT->ip = $this->CT->getIP();
        $region = $this->CT->get();
        return $region;
    }


}
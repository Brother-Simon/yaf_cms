<?php
require_once 'CT_Api.php';
/*
 * SDK ：CT_Remind
 * 功能: 提醒
*/
class CT_Remind {

    public  $CT;

    public function __construct () {
        $this->CT = new CT_Api();
    }

    /*
      * 功能：获取单条提醒记录
      * 调用：CT_Remind::get($id)
      * 参数：$id 必须
    */
    public function get($id) {
        $this->CT->api = 'common.remind.get';
        $this->CT->id = $id;
        $data = $this->CT->get();
        return $data;
    }

    /*
      * 功能：获取提醒列表
      * 调用：CT_Remind::getList($condition,$option,$fields)
      * 参数：$condition: 查询的条件 格式为数组 如 $condition = array(‘type’=>1,’user_id’=>2)或者$condition = “type=1 and user_id=2”
      *       $option: 格式 array(‘order’=>’id desc’,’offset’=>0,’limit’=>10,’group’=>’ id’); 每个值都是可选的 默认为null
      *       $fields: 要查询的字段，默认为所有 即 *
    */
    public function getList($condition=array(),$option=array('order'=>null,'offset'=>null,'limit'=>null,'group'=>null),$fields="*") {
        $this->CT->api = 'common.remind.getList';
        $data = $this->CT->getList($condition,$option,$fields);
        return $data;
    }

    /*
      * 功能：保存提醒内容 包含ID则为更新 否则为新建
      * 调用：CT_Remind::save($data)
      * 参数：$data: 一维数组 对应数据库字段的键值对 必须 若包含id则为更新提醒，若不包含id即为新增提醒
    */
    public function save($data) {
        $this->CT->api = 'common.remind.save';
        $this->CT->setParams($data);
        $data = $this->CT->put();
        return $data;
    }

    /*
      * 功能：删除提醒
      * 调用：CT_Remind::delete($id,$user_id);
      * 参数：$id: 提醒ID  可选
      *       $user_id 用户ID 可选
      *       两者需最少其中一个有值
    */
    public function delete($id,$user_id='') {
        $this->CT->api = 'common.remind.delete';
        $this->CT->id = $id;
        $this->CT->user_id = $user_id;
        $data = $this->CT->put();
        return $data;
    }

}
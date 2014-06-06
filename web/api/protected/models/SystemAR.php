<?php

class SystemAR extends CActiveRecord {
  public function tableName() {
    return "system";
  }
  
  public function primaryKey() {
    return "sid";
  }
  
  public function rules() {
    return array(
        array("name", "required"),
        array("sid, name, required", "safe"),
    );
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  /**
   * 返回配置值
   * @param type $name
   * @param type $default
   * @return type
   */
  public static function get($name, $default = "") {
    $cond = array("condition" => "name=:name", "params" => array(":name" => $name));
    $row = SystemAR::model()->find($cond);
    if ($row) {
      return unserialize($row["value"]);
    }
    return $default;
  }
  
  /**
   * 设置配置值
   * @param type $name
   * @param type $value
   */
  public static function set($name, $value) {
    $value = serialize($value);
    $cond = array("condition" => "name=:name", "params" => array(":name" => $name));
    $row = SystemAR::model()->find($cond);
    // 添加
    if (!$row) {
      $system_ar = new SystemAR();
      $system_ar->name = $name;
      $system_ar->value = $value;
      if ($system_ar->save()) {
        return $system_ar;
      }
      return FALSE;
    }
    // 更新
    else {
      $row->value = $value;
      $row->save();
      return $row;
    }
  }
  
  public static function gameIsStart() {
    
  }
}


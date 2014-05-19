<?php

class UserMailAR extends CActiveRecord {
  
  public function tableName() {
    return "user_mails";
  }
  
  public function primaryKey() {
    return "umid";
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function rules() {
    return array(
        array("mail", "required"),
        array("mail", "email"),
        array("uid, cdate,umid", "safe"),
    );
  }
  
  public function beforeSave() {
    if (!$this->cdate ) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    
    return parent::beforeSave();
  }

  public function addNewMail($mail, $uid = 0) {
    if (!$uid) {
      $user = UserAR::crtuser();
      $uid = $user->uid;
    }
    $this->mail = $mail;
    $this->uid = $uid;
    
    return $this->save();
  }
}


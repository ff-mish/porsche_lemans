<?php

class TwitteAR extends CActiveRecord {
 public function tableName() {
   return "twittes";
 }
 
 public function primaryKey() {
   return "tid";
 }
 
 public function rules() {
   return array(
       array("uid, content, type", "required"),
       array("content, tid, cdate, udate, uuid, redirect_count, ref_user_count, type, owned_type, ref_type, ref_id", "safe"),
   );
 }
 
  public function beforeSave() {
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    $this->udate = date("Y-m-d H:i:s");
    return parent::beforeSave();
  }
  
  public function twittePost($msg) {
    $user = UserAR::crtuser();
    
    if (!$user) {
      $this->addError("uid", "user not login");
      return $this->getErrors();
    }
    
    $type = $user->from;
    $uid = $user->uid;
    $this->uid = $uid;
    $this->content = $msg;
    $this->type = $type;
    
    if ($this->save()) {
      return $this;
    }
    else {
      return $this->getErrors();
    }
    
    return $this;
  }
  
  public function afterSave() {
    // 发布一个新微博后， 我们需要发布到对应的平台去
    if ($this->{$this->primaryKey()}) {
      $content = $this->content;
      if ($this->type == UserAR::FROM_WEIBO) {
        $token = UserAR::token();
        $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
        $weibo_api->update($content);
      }
      else if ($this->type == UserAR::FROM_TWITTER) {
        Yii::app()->twitter->status_update($content);
      }
    }
    
    return parent::afterSave();
  }
}


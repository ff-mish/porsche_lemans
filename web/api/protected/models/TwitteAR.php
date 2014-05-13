<?php

class TwitteAR extends CActiveRecord {
  const LEVEL_WEB = "web";
  const LEVEL_TEAM = "team";
  const LEVEL_USER = "user";
  
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
 
 public function relations() {
   return array(
       "user" => array(self::BELONGS_TO, "UserAR",  "uid"),
   );
 }
 
 public static function  model($classname = __CLASS__) {
   return parent::model($classname);
 }
 
 // 获取最新的组的 Twitte
 public function getTeamLastPost($tid) {
   $uids = array();
   $userTeamAr = new UserTeamAR();
   $cond = array(
       "condition" => "tid=:tid",
       "params" => array(":tid" => $tid),
   );
   $rows = $userTeamAr->findAll($cond);
   foreach ($rows as $row) {
     $uids[] = $row->uid;
   }
   $cond_twitte = array(
       "condition" => "uid in (:uid)",
       "params" => array(":uid" => implode(",", $uids)),
       "limit" => TeamAR::LAST_POST_NUM
   );
   return $this->findAll($cond_twitte);
   
 }


 public function beforeSave() {
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    $this->udate = date("Y-m-d H:i:s");
    return parent::beforeSave();
  }
  
  /**
   * 发送简单文本微博
   * @param type $msg
   * @return \TwitteAR
   */
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
  
  public function twitteMediaPost($msg, $media) {
    $user = UserAR::crtuser();
    if (!$user) {
      return FALSE;
    }
    
    $uid = $user->uid;
    $content = $msg;
    $ref_type = $media->type;
    $ref_id = $media->id;
    $type = $user->from;
    $this->uid = $uid;
    $this->content = $content;
    $this->type = $type;
    $this->ref_type = $ref_type;
    $this->ref_id = $ref_id;
    
    if ($this->save()) {
      return $this;
    }
    else {
      $this->getErrors();
    }
  }
  
  public function afterSave() {
    // 发布一个新微博后， 我们需要发布到对应的平台去
    if ($this->{$this->primaryKey()}) {
      $content = $this->content;
      // 用户发的微博和一个媒体有关
      if ($this->ref_type && $this->ref_id) {
        $media = MediaAR::model()->findByPk($this->ref_id);
        if ($this->type == UserAR::FROM_WEIBO) {
          $token = UserAR::token();
          $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
          // 是图片的分享 就分享一个图片
          if ($media->type == MediaAR::MEDIA_IMAGE) {
            $weibo_api->upload($content, MediaAr::realpath($media->uri));
          }
          // 如果是分享视频 就分享一个视频链接
          else {
            $video_link = $media->media_link;
            $content .= " ". $video_link;
            $weibo_api->update($content);
          }
        }
        else if ($this->type == UserAR::FROM_TWITTER) {
          // 是图片的分享 就分享一个图片
          if ($media->type == MediaAR::MEDIA_IMAGE) {
            $weibo_api->upload($content, MediaAr::realpath($media->uri));
          }
          // 如果是分享视频 就分享一个视频链接
          else {
            $video_link = $media->media_link;
            $content .= " ". $video_link;
            Yii::app()->twitter->status_update($content);
          }
        }
      }
      // 用户发的微博只是一个简单的文本
      else {
        if ($this->type == UserAR::FROM_WEIBO) {
          $token = UserAR::token();
          $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
          $weibo_api->update($content);
        }
        else if ($this->type == UserAR::FROM_TWITTER) {
          Yii::app()->twitter->status_update($content);
        }
      }

    }
    
    return parent::afterSave();
  }
  
  public function getListInLevel($level, $num = 10) {
    if ($level == self::LEVEL_TEAM) {
      $user = UserAR::crtuser();
      $team = $user->team;
      $users = $team->loadMembers();
      $uids = array();
      foreach ($user as $user) {
        $uids[] = $user->uid;
      }
      
      // 构造条件
      $cond = array(
          "condition" => $this->getTableAlias().".uid in (:uid)",
          "order" => $this->getTableAlias() .".cdate",
          "params" => array(":uid" => implode(",", $uids)),
          "limit" => $num,
          "offset" => 0
      );
      $rows = $this->with("user")->findAll($cond);
      return $rows;
    }
    else if ($level == self::LEVEL_USER) {
      $user = UserAR::crtuser();
      $uid = $user->uid;
      // 构造条件
      $cond = array(
          "condition" => $this->getTableAlias().".uid in (:uid)",
          "order" =>  $this->getTableAlias() .".cdate",
          "params" => array(":uid" => $uid),
          "limit" => $num,
          "offset" => 0
      );
      $rows = $this->with("user")->findAll($cond);
      return $rows;
    }
  }
}


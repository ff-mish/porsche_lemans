<?php

class TwitteAR extends CActiveRecord {
  const LEVEL_WEB = "web";
  const LEVEL_TEAM = "team";
  const LEVEL_USER = "user";
  const LEVEL_TOPIC = "topic";
  
  public $media;
  
 public function tableName() {
   return "twittes";
 }
 
 public function primaryKey() {
   return "tid";
 }
 
 public function rules() {
   return array(
       array("uid, content, type", "required"),
       array("thirdpart_ref_media, is_from_thirdpart, content, tid, cdate, udate, uuid, redirect_count, ref_user_count, type, owned_type, ref_type, ref_id", "safe"),
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
 
 /**
  * 获取Twitte 对应的媒体
  */
 public function getMedia($tid = FALSE) {
   if (!$tid) {
     $tid = $this->tid;
   }
   
   if (!$tid) {
     return FALSE;
   }
   $media = FALSE;
   $twitte = $this->findByPk($tid);
   if ($twitte->is_from_thirdpart) {
     $media = $twitte->thirdpart_ref_media;
   }
   else if ($twitte->ref_type && $twitte->ref_id) {
     $share_media = MediaAR::model()->findByPk($twitte->ref_id);
     $media = $share_media->uri;
   }
   
   return $media;
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
    $ref_id = $media->mid;
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
    debug_info($this);
    // 发布一个新微博后， 我们需要发布到对应的平台去
    // 发布前，我们要检查下 微博是不是已经有了uuid , 只有发布后才会有 uuid
    if ($this->{$this->primaryKey()} && !$this->uuid) {
      $content = $this->content;
      // 用户发的微博和一个媒体有关
      if ($this->ref_type && $this->ref_id) {
        $media = MediaAR::model()->findByPk($this->ref_id);
        if ($this->type == UserAR::FROM_WEIBO) {
          $token = UserAR::token();
          $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
          // 是图片的分享 就分享一个图片
          if ($media->type == MediaAR::MEDIA_IMAGE) {
            // the uri like: http://xxx.com/api/heldxxx.png
            $uri = str_replace(Yii::app()->getBaseUrl(true), "", $media->uri);
            $weibo_api->upload($content, MediaAr::realpath($uri));
          }
          // 如果是分享视频 就分享一个视频链接
          else {
            $video_link = $media->media_link;
            $content .= " ". $video_link;
            $ret = $weibo_api->update($content);
            
            // 把uuid 保存下来
            $uuid = $ret["idstr"];
            $tweet = $this->findByPk($this->tid);
            $tweet->uuid = $uuid;
            $tweet->update();
          }
        }
        else if ($this->type == UserAR::FROM_TWITTER) {
          // 是图片的分享 就分享一个图片
          if ($media->type == MediaAR::MEDIA_IMAGE) {
            $path = MediaAr::realpath($media->uri);
            if (!is_file($path)) {
              return FALSE;
            }
            $file_data = (file_get_contents($path));
            $ret = Yii::app()->twitter->status_update_with_media($content, array($file_data));
            
            $uuid = $ret->id_str;

            $tweet = $this->findByPk($this->tid);
            $tweet->uuid = $uuid;
            $tweet->update();
          }
          // 如果是分享视频 就分享一个视频链接
          else {
            $video_link = $media->media_link;
            $content .= " ". $video_link;
            $ret = Yii::app()->twitter->status_update($content);
            
            $uuid = $ret->id_str;

            $tweet = $this->findByPk($this->tid);
            $tweet->uuid = $uuid;
            $tweet->update();
          }
        }
      }
      // 用户发的微博只是一个简单的文本
      else {
        if ($this->type == UserAR::FROM_WEIBO) {
          $token = UserAR::token();
          $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
          $ret = $weibo_api->update($content);
          
          $uuid = $ret["idstr"];
          
          $tweet = $this->findByPk($this->tid);
          $tweet->uuid = $uuid;
          $tweet->update();
        }
        else if ($this->type == UserAR::FROM_TWITTER) {
          $ret = Yii::app()->twitter->status_update($content);
          
          $uuid = $ret->id_str;
          
          $tweet = $this->findByPk($this->tid);
          $tweet->uuid = $uuid;
          $tweet->update();
        }
      }

    }
    
    return parent::afterSave();
  }
  
  public function getListInLevel($level, $num = 4) {
    $query = new CDbCriteria();
    $params = array();
    $user = UserAR::crtuser();
    if (!$user) {
      return FALSE;
    }
    $from = $user->from;
    // 1. 来源作为条件
    $query->addCondition("type=:type");
    $params[":type"] = $from;
    // 2. 用户ID 作为条件
    $uids = array();
    if ($level == self::LEVEL_USER) {
      $uids[] = $user->uid;
      $query->addCondition($this->tableAlias.".uid in (:uid)");
      $params[":uid"] = implode(",", $uids);
    }
    else if ($level == self::LEVEL_TEAM) {
      $teamAr = new TeamAR();
      $userTeamAr = new UserTeamAR();
      $teamuser = $userTeamAr->loadUserTeam($user);
      $members = $teamAr->loadMembers($teamuser->team->tid);
      //$uids[] = $user->uid;
      
      foreach ($members as $member) {
        $uids[] = $member->uid;
      }
      $query->addCondition($this->tableAlias.".uid in (:uid)");
      $params[":uid"] = implode(",", $uids);
    }
    else if ($level == self::LEVEL_TOPIC) {
      // 这里我们不需要判断用户uid 我们获取任意几个从第三方抓取的内容
      $query->addCondition("is_from_thirdpart=:is_from_thirdpart");
      $params[":is_from_thirdpart"] = 1;
    }
    else if ($level == self::LEVEL_WEB) {
      if ($from == UserAR::FROM_TWITTER) {
        $uids[] = Yii::app()->params["twitter_uid"];
      }
      else {
        $uids[] = Yii::app()->params["weibo_uid"];
      }
      $query->addCondition($this->tableAlias.".uid in (:uid)");
      $params[":uid"] = implode(",", $uids);
    }
    
    $query->order = $this->tableAlias.".cdate DESC";
    $query->limit = $num;
    $query->params = $params;
    
    $rows = $this->with("user")->findAll($query);
    
    // 然后获取微博对应的媒体
    $datas = array();
    foreach ($rows as $row) {
      $data = array();
      foreach ($row as $key => $v) {
        $data[$key] = $v;
      }
      $data["media"] = $row->getMedia();
      $data["user"] = $row->user;
      $data["date"] = self::ago(strtotime($row["cdate"]));
      $datas[] = $data;
    }
    
    return $datas;
  }
  
  public static  function ago($timestamp){
    $difference = time() - $timestamp;
    $periods = array("second", "minute", "hour", "day", "week", "month", "years", "decade");
    $lengths = array("60","60","24","7","4.35","12","10");
    for($j = 0; $difference >= $lengths[$j]; $j++)
    $difference /= $lengths[$j];
    $difference = round($difference);
    if($difference != 1) $periods[$j].= "s";
    $text = "$difference $periods[$j] ago";
    return $text;
 }
}


<?php

/**
 * 多媒体
 */
class MediaAR extends CActiveRecord {
  
  const MEDIA_IMAGE = "image";
  const MEDIA_VIDEO = "video";
  
  const PAGE_ITEMS = 10;
  
  public static $allow_images = array(
        "image/png",
        "image/gif",
        "image/jpeg",
        "image/jpg"
  );
  
  public static $allow_videos = array(
        "video/mp4"
  );
  
  private  $video;
  private $image;


  public function tableName() {
    return "media";
  }
  
  public function primaryKey() {
    return "mid";
  }
  
  public static function model($classname = __CLASS__) {
    return self::model($classname);
  }
  
  public function beforeSave() {
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    $this->udate = date("Y-m-d H:i:s");
    
    $url = Yii::app()->getBaseUrl(TRUE);
    $this->uri = str_replace($url, "", $this->uri);
    
    return parent::beforeSave();
  }
  
  public function afterFind() {
    $url = Yii::app()->getBaseUrl(TRUE);
    $this->uri = $url. $this->uri;
    return parent::afterFind();
  }
  
  public function rules() {
    return array(
        array("uri", "fileExit"),
        array("type, uri", "required"),
        array("mid, cdate, udate, uid, media_link", "safe"),
    );
  }
  
  
  public function fileExit($attribute, $params) {
    $uri = $this->{$attribute};
    $abs_path = realpath(Yii::app()->basePath.'/'. $uri);
    
    if (is_file($abs_path)) {
      return $this->addError($attribute, "URI is not existed");
    }
  }
  
  /**
   * 
   * @param type $type
   * @param type $media_type
   * @return type
   */
  public static function new_uri($type, $media_type) {
    if ($media_type == self::MEDIA_IMAGE) {
      $parts = explode("/", $type);
    }
    else if ($media_type == self::MEDIA_VIDEO) {
      $parts = explode("/", $type);
      if ($parts["1"] == "mp4") {
        $parts[1] = "mp4";
      }
    }
    $name = time().'_'. uniqid();
    return md5($name).'.'. $parts[1];
  }
  
  
  public static function isAllowed($type, $media_type) {
    $images = self::$allow_images;
    
    if (array_search($type, $images) !== FALSE && $media_type == self::MEDIA_IMAGE) {
      return TRUE;
    }
    
    // Only support MP4 now
    $videos = self::$allow_videos;
    
    if (array_search($type, $videos) !== FALSE && $media_type == self::MEDIA_VIDEO) {
      return TRUE;
    }
    
    return FALSE;
  }
  
  /**
   * 保存新的Media
   * @param type $uri
   * @param type $type
   */
  public function saveNew($uri, $type, $media_url) {
    $this->uri = $uri;
    $this->type = $type;
    $this->media_link = $media_url;
    $ret = $this->save();
    $this->afterFind();
    return $ret;
  }
  
  public function getMedias($type = NULL, $page = 1) {
    $query = new CDbCriteria();
    if ($type) {
      $query->addCondition("type=:type");
      $query->params = array(":type" => $type);
    }
    $query->offset = ($page - 1) * self::PAGE_ITEMS;
    
    return $this->findAll($query);
  }
  
  /**
   * 分享 Media
   */
  public function share($media_id, $share_text) {
    $media = $this->findByPk($media_id);
    $twittaAr = new TwitteAR();
    return $twittaAr->twitteMediaPost($share_text, $media);
  }
  
  public function realpath($uri) {
    return realpath(Yii::app()->basePath.'/../'. $uri);
  }
  
  public function urlPath($uri) {
    
  }
}


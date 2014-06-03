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
    return parent::model($classname);
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
    
    $this->teaser_image = $url.$this->teaser_image;
    return parent::afterFind();
  }
  
  public function rules() {
    return array(
        array("uri", "fileExit"),
        array("type, uri", "required"),
        array("mid, cdate, udate, uid, media_link, teaser_image, title, description", "safe"),
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
   * 保存新的 Media
   * @param type $uri
   * @param type $type
   * @param type $media_url
   * @param type $teaser_image
   * @param type $title
   * @param type $description
   * @return type
   */
  public function saveNew($uri, $type, $media_url, $teaser_image = "", $title = "", $description="") {
    $this->uri = $uri;
    $this->type = $type;
    $this->media_link = $media_url;
    $this->teaser_image = $teaser_image;
    $this->title = $title;
    $this->description = $description;
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
    $query->limit = self::PAGE_ITEMS;
    
    $rows = $this->findAll($query);
    
    $data = array();
    foreach ($rows as $row) {
      $item = array();
      if ($row->type == self::MEDIA_VIDEO) {
        $item["image"] = $row->teaser_image;
      }
      else {
        $item["image"] = $row->uri;
      }
      foreach ($row as $key => $val) {
        $item[$key] = $val;
      }
      $data[] = $item;
    }
    return $data;
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
    // 是一个包含了 全地址的 Media
    if (strpos($uri, "http://") !== FALSE || strpos($uri, "https://") !== FALSE) {
      debug_info("BEGIN REPLACE");
      $base_url = Yii::app()->getBaseUrl(true);
      debug_info("BASE URL". $base_url);
      $uri = str_replace($base_url, "", $uri);
    }
    debug_info("URI". $uri);
    debug_info("abspath: ". Yii::app()->basePath.'/../'. $uri);
    return realpath(Yii::app()->basePath.'/../'. $uri);
  }
  
  public function urlPath($uri) {
    
  }
}


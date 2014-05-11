<?php

/**
 * 多媒体
 */
class MediaAR extends CActiveRecord {
  
  const MEDIA_IMAGE = "image";
  const MEDIA_VIDEO = "video";
  
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
  
  public function rules() {
    array(
        array("uri", "fileExit"),
        array("type, uri", "required"),
        array("mid, cdate, udate, uid", "safe"),
    );
  }
  
  public static function new_uri($type, $media_type) {
    if ($media_type == self::MEDIA_IMAGE) {
      $parts = explode("/", $type);
    }
    else if ($media_type == self::MEDIA_VIDEO) {
      $parts = explode("/", $type);
      if ($parts["1"] == "mp4") {
        //TODO::
      }
    }
    $name = time().'_'. uniqid();
    return md5($name).'.'. $parts[1];
  }
  
  
  public static function isAllowed($type, $media_type) {
    $images = self::allow_images;
    
    if (array_search($type, $images) !== FALSE && $media_type == self::MEDIA_IMAGE) {
      return TRUE;
    }
    
    // Only support MP4 now
    $videos = self::allow_videos;
    
    if (array_search($type, $videos) !== FALSE && $media_type == self::MEDIA_VIDEO) {
      return TRUE;
    }
    
    return FALSE;
  }
  
}


<?php

class MediaController extends Controller {
  
  public function actionAdd() {
    $request = Yii::app()->getRequest();
    if (!$request->isPostRequest) {
      return $this->responseError("http error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
    // Fuel 的访问地址
    $media_url = $request->getPost("media_url");
    
    $media_ar = new MediaAR();
    if ($request->getPost("type") == MediaAR::MEDIA_IMAGE) {
      $image = CUploadedFile::getInstanceByName("uri");
      $file_type = $image->getType();
      if (!MediaAR::isAllowed($file_type, MediaAR::MEDIA_IMAGE)) {
        return $this->responseJSON("file type is not allowed", ErrorAR::ERROR_FILE_UPLOADED_ERROR);
      }
      
      $new_name = MediaAR::new_uri($file_type, MediaAR::MEDIA_IMAGE);
      $save_to = Yii::app()->params["uploadedPath"].'/'. $new_name;
      $image->saveAs($save_to);

      // save to db
      $uri = str_replace(realpath(Yii::app()->basePath.'/../'), "", $save_to);
      $type = MediaAR::MEDIA_IMAGE;

      $media_ar->saveNew($uri, $type, $media_url);
      
      $this->responseJSON($media_ar, "success");
    }
    else if ($request->getPost("type") == MediaAR::MEDIA_VIDEO) {
      $video = CUploadedFile::getInstanceByName("uri");
      $file_type = $video->getType();
      if (!MediaAR::isAllowed($file_type, MediaAR::MEDIA_VIDEO)) {
        return $this->responseJSON("file type is not allowed", ErrorAR::ERROR_FILE_UPLOADED_ERROR);
      }
      
      $new_name = MediaAR::new_uri($file_type, MediaAR::MEDIA_VIDEO);
      $save_to = Yii::app()->params["uploadedPath"].'/'. $new_name;
      $video->saveAs($save_to);
      
      
      // Teaser image
      $teaserImage = CUploadedFile::getInstanceByName("teaser_image");
      $file_type = $teaserImage->getType();
      // 图片格式应该和Fuel 的 图片格式一样
      if (!MediaAR::isAllowed($file_type, MediaAR::MEDIA_IMAGE)) {
        return $this->responseJSON("file type is not allowed", ErrorAR::ERROR_FILE_UPLOADED_ERROR);
      }
      $new_name = MediaAR::new_uri($file_type, MediaAR::MEDIA_IMAGE);
      $teaser_save_to = Yii::app()->params["uploadedPath"].'/'. $new_name;
      $teaserImage->saveAs($teaser_save_to);
      $teaserUri = str_replace(realpath(Yii::app()->basePath.'/../'), "", $teaser_save_to);
      
      // save to db
      $uri = str_replace(realpath(Yii::app()->basePath.'/../'), "", $save_to);
      $type = MediaAR::MEDIA_VIDEO;
      
      $media_ar->saveNew($uri, $type, $media_url, $teaserUri);
      
      $this->responseJSON($media_ar, "success");
    }
  }
  
  public function actionList() {
    $request = Yii::app()->getRequest();
    
    $type = $request->getParam("type", NULL);
    $page = $request->getParam("page", 1);
    
    $mediaAr = new MediaAR();
    $medias = $mediaAr->getMedias($type, $page);
    
    $this->responseJSON($medias, "success");
  }
  
  public function actionShare() {
    $request = Yii::app()->getRequest();
    
    // media_id: 媒体 ID
    // share text: 分享文本
    if (!$request->isPostRequest) {
      //return $this->responseError("http verb error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
    $media_id = $request->getParam("media_id");
    $share_text = $request->getParam("share_text");
    
    if (!$media_id || !$share_text) {
      return $this->responseError("http params error", ErrorAR::ERROR_MISSED_REQUIRED_PARAMS);
    }
    
    $mediaAr = new MediaAR();
    $ret = $mediaAr->share($media_id, $share_text);
    
    $this->responseJSON($ret, "success");
  }
  
  public function actionIndex() {
    $request = Yii::app()->getRequest();
    $mid = $request->getParam("mid", false);
    
    $mediaAr = MediaAR::model()->findByPk($mid);
    
    $this->responseJSON($mediaAr, "success");
  }
}


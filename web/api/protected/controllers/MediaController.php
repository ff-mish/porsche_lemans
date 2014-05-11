<?php

class MediaController extends Controller {
  
  public function actionAdd() {
    $request = Yii::app()->getRequest();
    if (!$request->isPostRequest) {
      return $this->responseError("http error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
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
      
      $media_ar->saveNew($uri, $type);
      
      $this->responseJSON($media_ar, "success");
    }
    else if ($request->getPost("type") == MediaAR::MEDIA_VIDEO) {
      $video = CUploadedFile::getInstanceByName("uri");
      $file_type = $video->getType();
      if (!MediaAR::isAllowed($file_type, MediaAR::MEDIA_VIDEO)) {
        return $this->responseJSON("file type is not allowed", ErrorAR::ERROR_FILE_UPLOADED_ERROR);
      }
    }
    
    print_r($video);
    //print_r($image);
    
    die();
    
    $this->redirect("/api/test/addmedia");
  }
  
}


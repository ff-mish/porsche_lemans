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
      print $file_type;
      if (!MediaAR::isAllowed($file_type, MediaAR::MEDIA_IMAGE)) {
        return $this->responseJSON("file type is not allowed", ErrorAR::ERROR_FILE_UPLOADED_ERROR);
      }
      
      $new_name = MediaAR::new_uri();
      
      print_r($new_name);
      die();
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


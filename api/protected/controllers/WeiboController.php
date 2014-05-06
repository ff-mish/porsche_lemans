<?php
Yii::import('ext.sinaWeibo.SinaWeibo',true);

class WeiboController extends Controller
{  
	
	public function actionIndex() {
		
    $this->responseJSON("weibo api", "");
	}
  
  public function actionLoginurl() {
    $weibo = new SinaWeibo(WB_AKEY, WB_SKEY);
    $this->responseJSON(array("url" => $weibo->getAuthorizeURL(WB_CALLBACK_URL)), "");
  }
  
	public function actionCallback(){
		$weiboService=new SinaWeibo(WB_AKEY, WB_SKEY);
		if (isset($_REQUEST['code'])) {
			$keys = array();
      
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = WB_CALLBACK_URL;
			try {
				$token = $weiboService->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
        //
			}
		}
		
		if ($token) {
      //TODO:: 获取token后处理逻辑
      Yii::app()->session["weibo_token"] = $token;
		} else {
		 // TODO:: 获取Token失败后处理逻辑
		}
	}
}
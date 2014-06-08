<?php
Yii::import('ext.sinaWeibo.SinaWeibo',true);

/**
 * @author Jackey <jziwenchen@gmail.com>
 * @description Weobo 控制器
 */
class SnsController extends Controller
{  
	public function actionIndex() {
    $this->responseJSON("weibo api", "");
	}
  
  /**
   * 获取微博登录地址
   */
  public function actionLoginurl() {
    $weibo = new SinaWeibo(WB_AKEY, WB_SKEY);
    $this->responseJSON(array("url" => $weibo->getAuthorizeURL(WB_CALLBACK_URL), "twitter_url" => UserAR::twitter_login_url()), "");
  }
  
	public function actionWeibocallback(){
    // Step1, 获取回调的 Code
		$weiboService=new SinaWeibo(WB_AKEY, WB_SKEY);
    $token = FALSE;
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
    else {
      return $this->redirect("/");
    }
		
    // Step2, 授权成功后进行登录
		if ($token) {
       // 获取token
      Yii::app()->session["weibo_token"] = $token;
      
      // 然后实现登录
      Yii::app()->session["from"] = UserAR::FROM_WEIBO;
      $user_ar = new UserAR();
      $user_ar->login();
      
      // 在这里 如果是系统级别的用户需要额外保存Token
      $weibo_uid = Yii::app()->params["weibo_uid"];
      if ($token["uid"] == $weibo_uid) {
        SystemAR::set("weibo_token", $token["access_token"]);
      }
      
      // 最后跳转到首页
      $this->redirect("/stand");
		} else {
		 // TODO:: 获取Token失败后处理逻辑
		}
	}
  
  public function actionTwittercallback() {
    if (isset($_REQUEST["denied"])) {
      return $this->redirect("/");
    }
    //先获取access token
    Yii::app()->twitter->getAccess_token();
    
    // 把access token  放入session
    Yii::app()->session["twitter_token"] = Yii::app()->session["access_token"];
    
    // 然后实现登录
    Yii::app()->session["from"] = UserAR::FROM_TWITTER;
    $user_ar = new UserAR();
    $user_ar->login();
    
    $twitter_uid = Yii::app()->session["twitter_token"]["user_id"];
    $twitter_user = Yii::app()->twitter->user_show($twitter_uid);
    
    if ($twitter_user->id_str == Yii::app()->params["twitter_uid"]) {
      SystemAR::set("twitter_token", Yii::app()->session["access_token"]);
    }
    
    // 最后跳转到首页
    $this->redirect("/stand");
  }
}
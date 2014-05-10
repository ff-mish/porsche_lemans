<?php
import("ext.sinaWeibo.SianWeibo_API");
class WeiboCommand extends CConsoleCommand {
  /**
   *
   * @var SinaWeibo_API
   */
  private $weibo_api;
  public function init() {
    $this->token = SystemAR::get("weibo_token");
    $this->weibo_api = new SinaWeibo_API();
  }
  public function actionSearchtag() {
    $weibo_api = $this->weibo_api;
  }
}

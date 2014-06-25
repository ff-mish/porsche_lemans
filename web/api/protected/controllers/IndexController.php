<?php

class IndexController extends Controller {
  
  public $page_name;
  public $classname;
  public $is_start = FALSE;
  
  public function init() {
    parent::init();
    
    $this->layout = "default";
    
    // 判断活动是否已经开始
    $start_data = strtotime(Yii::app()->params["startTime"]);
    $now = time();
    if ($now >= $start_data) {
      $this->is_start = TRUE;
    }
  }
  
//  public function beforeAction($action) {
//    if ($action->id == "video") {
//      return parent::beforeAction($action);
//    }
//    // 用户是否登录
//    $user = UserAR::crtuser();
//    if (!$user && $action->id != "index") {
//      return $this->redirect("/");
//    }
//    return parent::beforeAction($action);
//  }
  
  public function actionIndex() {
    $params = array(
        "page_name" => "winner"
    );
    $this->page_name = $params["page_name"];
      $this->render("winner");
  }
  
  public function actionAchieve() {
    $params = array(
        "page_name" => "achieve"
    );
    $this->page_name = $params["page_name"];
    $this->render("achieve", $params);
  }
  
  public function actionCountdown() {
    $params = array(
        "page_name" => "countdown"
    );
    $this->page_name = $params["page_name"];
    $this->render("countdown", $params);
  }
  
  public function actionFuel() {
    $params = array(
        "page_name" => "fuel"
    );
    $this->page_name = $params["page_name"];
    $this->render("fule", $params);
  }
  
  public function actionRace() {
    $params = array(
        "page_name" => "race"
    );
    $this->page_name = $params["page_name"];
    if ($this->isMobileBrowser()) {
      $this->page_name = "racemobile";
      $this->render("racemobile", $params);
    }
    else {
      $this->render("race", $params);
    }
  }
  
  public function actionRaceteam() {
    $params = array(
        "page_name" => "raceteam"
    );
    $this->page_name = $params["page_name"];
    $this->render("race", $params);
  }
  
  public function actionStand() {
    $params = array(
        "page_name" => "stand"
    );
    $user = UserAR::crtuser(TRUE);

    if ($user && $user->team) {
      $params["team_name"] = $user->team->name;
      $params["team_owner_uid"] = $user->team->owner_uid;
    }
    else {
      $params["team_name"] = "";
      $params["team_owner_uid"] = "";
    }
    
    $this->page_name = $params["page_name"];
    $this->classname = "pagebg8";
    
    // 邀请逻辑
    $invited_data = Yii::app()->session["invited_data"];
    $code = $invited_data["code"];
    // 执行邀请弹窗满足下面条件：
    // 1. 邀请信息存在
    // 2. 邀请没有被接受过(可用状态)
    // 3. 邀请数据有效
    // 4. 用户没有Team  (用户在接受邀请之前已经有了 Team 则不跳出弹窗)
    if ($invited_data && $user && !$user->team && !InviteLogAR::userWasAllowedInvite($user->uuid, $code) && InviteLogAR::inviteIsExist($user->uuid, $code)) {
      $params["is_invited"] = TRUE;
      // 在这里还要获取一下用户的被邀请的team的名字
      $tid = $invited_data["tid"];
      $team = TeamAR::model()->findByPk($tid);
      if ($team) {
        $params["team_name"] = $team->name;
        $params["team_id"] = $team->tid;
      }
      else {
        $params["team_name"] = "";
        $params["team_id"] = "";
      }
    }
    else {
      $params["is_invited"] = FALSE;
      $params["team_name"] = "";
      $params["team_id"] = "";
    }
    
    // 用户如果是自动加入了小组, 但是又是属于邀请类型用户
    // 这时需要询问用户是否加入当前小组？
    $params["now_team_name"] = "";
    $params["now_team_id"] = "";
    if ($user->status == UserAR::STATUS_AUTO_JOIN && $invited_data && !InviteLogAR::userWasAllowedInvite($user->uuid, $code) && InviteLogAR::inviteIsExist($user->uuid, $code)) {
      //1. 把用户现在的组拿出来
      $team_now = $user->team;
      if ($team_now) {
        $params["now_team_name"] = $team_now->name;
        $params["now_team_id"] = $team_now->tid;
        $params["is_invited"] = TRUE;
      }
    }
    
    if ($this->is_start) {
      $this->render("stand_p2", $params);
    }
    else {
      $this->render("stand", $params);
    }
  }
  
  public function actionTeambuild() {
    $params = array(
        "page_name" => "teambuild"
    );
    $this->page_name = $params["page_name"];
    $this->render("teambuild", $params);
  }
  
  public function actionTweet() {
    $params = array(
        "page_name" => "tweet"
    );
    $this->page_name = $params["page_name"];
    $this->render("tweet", $params);
  }
  
  public function actionMonitoring() {
    $params = array(
        "page_name" => "monitoring"
    );
    $this->page_name = $params["page_name"];
    $this->classname = "pagebg10";
    $this->render("monitoring", $params);
  }
  
  public function actionWinnerprices() {
    $params = array(
        "page_name" => "winnerprices"
    );
    $this->page_name = $params["page_name"];
    $this->classname = "pagebg12";
    
    $this->render("winnerprices");
  }
  
  public function actionTeamrace() {
    $params = array(
        "page_name" => "teamrace"
    );
    $this->page_name = $params["page_name"];
    $this->render("teamrace", $params);
  }

    public function actionWinner() {
        $params = array(
            "page_name" => "winner"
        );
        $this->page_name = $params["page_name"];
        $this->render("winner", $params);
    }

    public function actionTimelapse() {
        $params = array(
            "page_name" => "timelapse"
        );
        $this->page_name = $params["page_name"];
        $this->render("timelapse", $params);
    }
  
  public function actionVideo() {
    $request = Yii::app()->getRequest();
    preg_match("/\d+/i", $request->getUrl(), $matches);
    $mid = array_shift($matches);
    
    $mediaAr = MediaAR::model()->findByPk($mid);
    if (!$mediaAr) {
      return $this->redirect("/");
    }
    
    $params = array(
        "page_name" => "fuel"
    );
    $this->page_name = $params["page_name"];
    $params["mediaAr"] = $mediaAr;
    $this->render("video", $params);
  }
  
	protected function isMobileBrowser()
	{
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
	}
}


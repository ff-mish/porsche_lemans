  <!--  -->
  <div class="page pagebg8">
        <!--  -->
        <div class="header">
            <div class="logo"><?php echo Yii::t("lemans", "PORSCHE");?></div>
	        <a target="_blank" href="http://www.porsche.com/microsite/mission2014-resettozero/" class="hd_info"></a>
        </div>

    <div class="count">
      <div class="conut_tit" data-fadein><p>Le Mans</p><?php echo Yii::t("lemans", "#24SocialRace")?></div>
      
      <!-- <div class="conut_watch" data-fadein>watch the trailer</div> -->
      <div class="conut_tips" ><?=Yii::t('lemans','Join the race and create your team now')?></div>
      <div class="home_v" data-a="mobile_home_v"></div>
      <div class="home_share">
        <!--   -->
      <a href="<?php echo UserAR::weibo_login_url() ?>" class="home_weibo"></a>
      <a href="<?php echo UserAR::twitter_login_url()  ?>" class="home_twitter"></a>
      </div>
      <div class="btn home_winners" data-a="winners-prizes"><?php echo Yii::t('lemans','Winners’ Prizes')?></div>
    </div>
      <div id="home_video">
          <a class="skipintro" href="#" data-a="skip-intro"><?php echo Yii::t('lemans','Skip intro')?></a>
      </div>
    <div id="winners-prizes">
        <div class="popup_close"></div>
        <div class="winners-prizes-wrap">
            <h2><?php echo Yii::t("lemans", "Winners’ Prizes")?></h2>
            <img class="winners-icon" src="/images/winner_prizes.png">
            <div class="winners-prizes-con clearfix">
                <?php echo Yii::t("lemans", "race_description")?>
            </div>
        </div>

    </div>

  </div>
  <!--  -->

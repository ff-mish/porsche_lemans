	<!--  -->
	<div class="page page_count pagebg8">
        <!--  -->
        <div class="header">
            <div class="logo"><?php echo Yii::t("lemans", "PORSCHE")?></div>
	        <a target="_blank" href="<?php echo Yii::t("lemans", "PORSCHE_LINK")?>" class="hd_info"></a>
        </div>

		<div class="count">
			<div class="conut_tit" data-fadein><p>Le Mans</p><?php echo Yii::t("lemans", "#24SocialRace")?></div>
            <div class="conut_down_wrap">
                <div class="conut_down cs-clear" data-fadein>
	                <div class="conut_downitem">00</div><span class="conut_downinfo"><?php echo Yii::t("lemans", "d")?></span>
	                <div class="conut_downitem">00</div><span class="conut_downinfo"><?php echo Yii::t("lemans", "h")?></span>
	                <div class="conut_downitem">00</div><span class="conut_downinfo"><?php echo Yii::t("lemans", "min")?></span>
	                <div class="conut_downitem">00</div><span class="conut_downinfo" style="margin-right:0;"><?php echo Yii::t("lemans", "sec")?></span>
                </div>
              </div>
			
			<!-- <div class="conut_watch" data-fadein>watch the trailer</div> -->
			<div class="conut_tips" ><?php echo Yii::t("lemans", "JOIN_NOW")?></div>
			<div class="home_v btn" id="mobile_home_v"></div>
			<div class="home_share">
				<!--   -->
      <a href="<?php echo UserAR::weibo_login_url() ?>" class="home_weibo"></a>
      <a href="<?php echo UserAR::twitter_login_url()  ?>" class="home_twitter"></a>
			</div>
			<div class="btn home_winners" data-a="winners-prizes"><?=Yii::t('lemans','Winners’ Prizes')?></div>
		</div>
        <div id="home_video">
            <a class="skipintro" href="#" data-a="skip-intro"><?=Yii::t('lemans','Skip intro')?></a>
        </div>
		<div id="winners-prizes">
            <div class="popup_close btn"></div>
            <div class="winners-prizes-wrap">
                <h2><?=Yii::t('lemans','Winners’ Prizes')?></h2>
                <img class="winners-icon" src="/images/winner_prizes.png">
                <div class="winners-prizes-con clearfix">
                    <?php echo Yii::t("lemans", "race_description")?>
                </div>
            </div>
		</div>

	</div>
	<!--  -->

	<!--  -->
	<div class="page_wrap bg1 ">
		<!-- logo -->
    <div class="header">
        <a href="/" class="logo"><?php echo Yii::t("lemans", "PORSCHE")?></a>
	    <a target="_blank" href="<?php echo Yii::t("lemans", "PORSCHE_LINK")?>" class="hd_info"></a>
    </div>

		<div class="nav">
			<div class="post_link">
				<a href="#" data-a="post_weibo" class="navicon"></a>
				<div class="post_tips">
					<?=Yii::t('lemans','Make a tweet')?>
					<span>◆</span>
				</div>
			</div>
			<p class="on"><?=Yii::t('lemans','Stand')?></p>
			<p><a href="/race"><?=Yii::t('lemans','Race')?></a></p>
			<p><a href="/fuel"><?=Yii::t('lemans','Fuel')?></a></p>
			<p><a href="/monitoring"><?=Yii::t('lemans','Monitoring')?></a></p>
			<p> &nbsp; </p>
			<div class="mobile_nav">
				<p data-a="legal-mentions" class="btn legal"><?php echo Yii::t("lemans", "Legal Mentions")?></p>
				<p class="language">
					<?php if (Yii::app()->language == "zh_cn"): ?>
						<a class="f_lang_en" data-lang="en_us" href="#">En</a> | <span>中文</span>
					<?php else:?>
						<span>En</span> | <a class="f_lang_cn" data-lang="zh_cn" href="#">中文</a>
					<?php endif;?>
				</p>
			</div>
			<p><a data-a="logout" class="logout" href="/api/user/logout"><?php echo Yii::t("lemans", "Log out")?></a></p>
			<div class="mobile_menu btn" data-a="show-menu">
				<p></p>
				<p></p>
				<p></p>
			</div>
		</div>
    <div class="page pagebg1">
      <!-- stand -->
      <div class="stand">
        <div id="data-stand" style="display:none" 
             data-is_invited="<?php echo $is_invited ? 1: 0?>"
             data-team_id="<?php echo $team_id?>"
             data-team_name="<?php echo $team_name?>"
             data-now_team_name="<?php echo $now_team_name?>"
             data-now_team_id="<?php echo $now_team_id?>"
             >
        </div>
        
        <div class="stand_tit">
          <span id="team-score"></span>
          <div class="team_name_error_tip" >
			 <?=Yii::t('lemans','Team name is limited within 12 characters')?>
		  </div>
          <span class="team_name" style="-webkit-user-select: auto;" spellcheck="false" contenteditable="true"></span>
          <div class="stand_chart_tip">
			 <?php echo Yii::t("lemans", "Edit team name")?>
			 <span>◆</span>
		  </div>
        </div>
          <!--  -->
        <div class="teambuild_members cs-clear">
			</div>
			<!--  -->
			<div class="stand_achivments cs-clear" data-style="opacity:0;" data-animate="opacity:1;" data-delay="1400" data-time="500" data-easing="easeOutQuart">
				<h2 class="fl"><?php echo Yii::t("lemans", "Achievements")?></h2>
				<div class="stand_achivmentsbox">
				</div>
			</div>
			<!--  -->
			<div class="stand_tweet" data-style="opacity:0;" data-animate="opacity:1;" data-delay="1600" data-time="500" data-easing="easeOutQuart">
				<h2 class="fl"><?php echo Yii::t("lemans", "Race Starts In")?></h2>
				<div class="conut_down_wrap stand_count_down">
			      	<div class="conut_down cs-clear" data-fadein>
						<div class="conut_downitem">00</div><span class="conut_downinfo"><?php echo Yii::t("lemans", "d")?></span>
						<div class="conut_downitem">00</div><span class="conut_downinfo"><?php echo Yii::t("lemans", "h")?></span>
						<div class="conut_downitem">00</div><span class="conut_downinfo"><?php echo Yii::t("lemans", "min")?></span>
						<div class="conut_downitem">00</div><span class="conut_downinfo" style="margin-right:0;"><?php echo Yii::t("lemans", "sec")?></span>
					</div>
			    </div>
			</div>
			<div class="stand_tweet" data-style="opacity:0;" data-animate="opacity:1;" data-delay="1600" data-time="500" data-easing="easeOutQuart">
				<h2 class="fl"><?php echo Yii::t("lemans", "Lastest Posts")?></h2>
				<div class="stand_posts">
					<div class="stand_posts_inner clearfix"></div>
					<div class="stand_add"></div>
				</div>
			</div>
			<!--  -->
			<div class="stand_chart">
				<div class="stand_chart_score">
				</div>
				<div class="stand_chart_speed" >
					<div class="stand_chart_tip">
						<?php echo Yii::t("lemans", "This display the number of tweets per hour. 40 tweets per hour allow to reach full speed.") ?>
						<span>◆</span>
					</div>
				</div>
				<div class="stand_chart_quality" >
					<div class="stand_chart_tip">
						<?php echo Yii::t("lemans" ,"Tweet content from Fuel section to increase your quality")?>
						<span>◆</span>
					</div>
				</div>
				<div class="stand_chart_assiduite" >
					<div class="stand_chart_tip">
						<?php echo Yii::t("lemans", "Answer right to Porsche challenges to improve your assiduity")?>
						<span>◆</span>
					</div>
				</div>
				<div class="stand_chart_impact" >
					<div class="stand_chart_tip">
						<?php echo Yii::t("lemans", "This represents the number of your followers")?>
						<span>◆</span>
					</div>
				</div>
			</div>
		</div>
		<!-- stand end -->
	</div>

	<div class="tutr-step">
        <div class="tutr-step-skip"><?php echo Yii::t("lemans", "Skip Tutorial")?></div>
		<div class="tutr-step-top"></div>
		<div class="tutr-step-bottom"></div>
		<div class="tutr-step-left"></div>
		<div class="tutr-step-right"></div>
		<div class="tutr-step-tip1">
			<p class="step-num"><?php echo Yii::t("lemans", "1/5")?></p>
			<div class="step-con">
				<?php echo Yii::t("lemans", "tutorial_step_1")?>
			</div>
			<a href="#" class="step-btn" data-step="2"><?php echo Yii::t("lemans", "Next")?></a>
		</div>
		<div class="tutr-step-tip2">
			<p class="step-num"><?php echo Yii::t("lemans", "2/5")?></p>
			<div class="step-con"><?php echo Yii::t("lemans", "tutorial_step_2")?>
		</div>
			<a href="#" class="step-btn" data-step="3"><?php echo Yii::t("lemans", "Next")?></a>
		</div>
		<div class="tutr-step-tip3">
			<p class="step-num"><?php echo Yii::t("lemans", "3/5")?></p>
			<div class="step-con"><?php echo Yii::t("lemans", "tutorial_step_3")?>			</div>
			<a href="#" class="step-btn" data-step="4"><?php echo Yii::t("lemans", "Next")?></a>
		</div>
		<div class="tutr-step-tip4">
			<p class="step-num"><?php echo Yii::t("lemans", "4/5")?></p>
			<div class="step-con">
				<?php echo Yii::t("lemans", "tutorial_step_4")?>
			</div>
			<a href="#" class="step-btn" data-step="5"><?php echo Yii::t("lemans", "Next")?></a>
		</div>
	</div>
	<!--  -->

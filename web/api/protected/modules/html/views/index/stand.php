	<!--  -->
	<div class="page pagebg6">
		<!-- logo -->
		<div class="logo">PORSCHE</div>
		<!-- nav -->
		<div class="nav">
        <a href="#" data-a="post_weibo" class="navicon"> &nbsp;</a>
            <p><a href="/race.html"><?=Yii::t('lemans','The Race')?></a></p>
            <p><a href="/monitoring.html"><?=Yii::t('lemans','Monitoring')?></a></p>
            <p class="on"><a href="/stand.html"><?=Yii::t('lemans','My Stand')?></a></p>
            <p><a href="/fuel.html"><?=Yii::t('lemans','Fuel')?></a></p>
		</div>
		<!-- stand -->
		<div class="stand">
      <div style="display:none" id="data-stand"
           data-team_name="<?php echo htmlspecialchars($team_name)?>" 
           data-team_owner_uid="<?php echo $team_owner_uid?>">
      </div>
			<div class="stand_tit"><input class="team_name" value="team name" /></div>
			<!--  -->
			<div class="teambuild_member stand_useritem cs-clear">
				<div class="member_item ">
					<img src="/images/phodemo.jpg" />
					<p class="member_name">@Mradrien_
			            <span class="member-leave" data-a="leaveteam"><?php echo Yii::t("messages", "Leave Team")?></span>
			        </p>
				</div>
				<div class="member_speed"></div>
				<div class="memeber_space">11K</div>
			</div>
			<!--  -->
			<div class="teambuild_member stand_useritem cs-clear">
				<div class="member_item ">
					<img src="/images/phodemo.jpg" />
					<p class="member_name">@Mradrien_</p>
				</div>
				<div class="member_speed"></div>
				<div class="memeber_space">11K</div>
			</div>
			<!--  -->
			<div class="teambuild_member stand_useritem cs-clear">
				<a href="javascript:;" data-a="member_invent" class="member_add cs-clear">+</a>
			</div>
			<!--  -->
			<div class="stand_achivments cs-clear">
				<h2 class="fl">achievements</h2>
				<div class="stand_achivmentsbox">
					<p></p>
					<p></p>
					<p></p>
				</div>
			</div>
			<!--  -->
			<div class="stand_tweet">
				<h2 class="fl">Lastest Posts</h2>
				<div class="stand_tweetitem">
					@yenatweet Good news, maintenant faut attendre le montant afin voir si ça diminuera le dumping social.
				</div>
				<div class="stand_tweetitem">
					@yenatweet Good news, maintenant faut attendre le montant afin voir si ça diminuera le dumping social.
				</div>
			</div>
			<!--  -->
			<div class="stand_chart">
				<div class="stand_chart_speed" >
				</div>
				<div class="stand_chart_quality" >
				</div>
				<div class="stand_chart_assiduite" >
				</div>
				<div class="stand_chart_impact" >
				</div>
			</div>
			<!--  -->
			<div class="stand_add"></div>

		</div>
		<!-- stand end -->
	</div>
	<!--  -->
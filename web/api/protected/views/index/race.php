	<!--  -->
	<div class="page pagebg5">
		<!--  -->
		<div class="header">
			<div class="logo">PORSCHE</div>
			<div class="hd_info"></div>
		</div>
		<!--  -->
		<!-- nav -->
		<div class="nav">
        <a href="#" data-a="post_weibo" class="navicon"> &nbsp;</a>
            <p class="on"><a href="/race.html"><?=Yii::t('lemans','The Race')?></a></p>
            <p><a href="/monitoring.html"><?=Yii::t('lemans','Monitoring')?></a></p>
            <p><a href="/stand.html"><?=Yii::t('lemans','My Stand')?></a></p>
            <p><a href="/fuel.html"><?=Yii::t('lemans','Fuel')?></a></p>
		</div>
		<!-- race -->
		<div class="race">
			<div class="race_track" id="map"></div>
			<div class="race_bg" id="container">
			</div>
			<div class="race_nav">
				<div class="race_time"></div>
				<div class="race_speed"></div>
				<div class="race_navitem "><?=Yii::t('lemans','Networks')?></div>
				<div class="race_navitem race_navitemon"><?=Yii::t('lemans','Teams')?></div>
			</div>
		</div>
		<!-- race end -->
	</div>
	<div class="footer">
		<div class="footer_link cs-clear">
			<p>Legal Mentions</p>
			<p>Share</p>
		</div>
		<div class="footer_language"><a href="#">En</a> | <a href="#">中文</a></div>
	</div>
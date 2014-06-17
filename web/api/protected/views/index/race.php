<div class="page_wrap ">
	<div class="header">
	    <a href="/" class="logo"><?php echo Yii::t("lemans", "PORSCHE")?></a>
		<a target="_blank" href="<?php echo Yii::t("lemans", "PORSCHE_LINK")?>" class="hd_info"></a>
	</div>
	<!--  -->
	<div class="page pagebg5">
		<!--  -->
		<!-- nav -->
		<div class="nav">
            <div class="header-space"></div>
            <p><a href="/winner"><?=Yii::t('lemans','Winners')?></a></p>
            <p class="on"><?=Yii::t('lemans','Simulation')?></p>
            <p> &nbsp; </p>
			<div class="mobile_nav">
				<p data-a="legal-mentions" class="btn legal"><?php echo Yii::t("lemans", "Legal Notice")?></p>
				<p class="language">
					<?php if (Yii::app()->language == "zh_cn"): ?>
						<a class="f_lang_en" data-lang="en_us" href="#">En</a> | <span>中文</span>
					<?php else:?>
						<span>En</span> | <a class="f_lang_cn" data-lang="zh_cn" href="#">中文</a>
					<?php endif;?>
				</p>
			</div>
			<div class="mobile_menu btn" data-a="show-menu">
				<p></p>
				<p></p>
				<p></p>
			</div>
		</div>
		<!-- race -->
<!--		<div class="race">
			<div class="race_track" id="map"></div>
			<div class="race_bg" id="container">
			</div>
			<div class="race_nav">
				<div class="race_time"></div>
				<div class="race_speed"></div>
				<div class="race_navitem "><?=Yii::t('lemans','Networks')?></div>
				<div class="race_navitem race_navitemon"><?=Yii::t('lemans','Teams')?></div>
			</div>
		</div>-->
    <div id="container"></div>
    <div id="map"></div>
		<!-- race end -->
	</div>
</div>

<!--[if !IE]><!-->
<script type="text/javascript" src="/js/track.js"></script>
<!--<![endif]-->
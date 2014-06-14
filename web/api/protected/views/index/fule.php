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
			<div class="post_link">
				<a href="#" data-a="post_weibo" class="navicon"></a>
				<div class="post_tips">
					<?=Yii::t('lemans','Tweet')?>
					<span>◆</span>
				</div>
			</div>
			<p><a href="/stand"><?=Yii::t('lemans','Stand')?></a></p>
			<p><a href="/teamrace"><?=Yii::t('lemans','Race')?></a></p>
			<p class="on"><?=Yii::t('lemans','Fuel')?></p>
			<p><a href="/monitoring"><?=Yii::t('lemans','Monitoring')?></a></p>
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
			<p><a data-a="logout" class="logout" href="/api/user/logout"><?php echo Yii::t("lemans", "Log out")?></a></p>
			<div class="mobile_menu btn" data-a="show-menu">
				<p></p>
				<p></p>
				<p></p>
			</div>
		</div>
		<!-- fuel -->
		<div class="fuel">
			<div class="fuellist cs-clear">
			</div>
			<!-- fuellist -->
			<div class="fuelmore" style="display:none;">
				<a href="#" data-a="fuel-load"><?php echo Yii::t("lemans", "Load More")?></a>
			</div>
		</div>
		<!-- fuel end -->
	</div>
</div>
<script type="text/tpl" id="fuel-tpl">
<div class="fuelitem" {{#if video}}data-video="{{uri}}"{{/if}}>
	<img src="{{image}}" style="width:105%"/>
	<div class="fuelshade" style="display:none" data-d="mid={{mid}}" data-a="preview"></div>
	<div class="fuelbtnbox" style="display:none">
		<div class="fuelbtn fuelbtn1" data-d="{{mid}}" data-img="{{image}}" data-title="{{title}}" data-url="{{short_url}}" data-a="repost"></div>
		<div class="fuelbtn fuelbtn2" data-d="mid={{mid}}" data-a="preview"></div>
	</div>
</div>
</script>
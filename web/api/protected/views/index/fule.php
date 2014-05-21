	<!--  -->
	<div class="page pagebg1">
		<!--  -->
		<div class="header">
			<div class="logo">PORSCHE</div>
			<div class="hd_info"></div>
		</div>
		<!--  -->
		<div class="page ">
			<!-- nav -->
			<div class="nav">
				<a href="#" data-a="post_weibo" class="navicon"> &nbsp;</a>
				<p><a href="/race"><?=Yii::t('lemans','The Race')?></a></p>
				<p><a href="/monitoring"><?=Yii::t('lemans','Monitoring')?></a></p>
				<p><a href="/stand"><?=Yii::t('lemans','My Stand')?></a></p>
				<p class="on"><a href="/fuel"><?=Yii::t('lemans','Fuel')?></a></p>
			</div>
			<!-- fuel -->
			<div class="fuel">
				<div class="fuellist cs-clear">
				</div>
				<div class="loading"></div>
				<!-- fuellist -->
				<div class="fuelmore">
					<a href="#" data-a="fuel-load">LOAD MORE</a>
				</div>
			</div>
			<!-- fuel end -->
		</div>
	</div>
	<!--  -->
	<script type="text/tpl" id="fuel-tpl">
<div class="fuelitem" {{#if video}}data-video="{{uri}}"{{/if}}>
	<img src="{{image}}" style="width:100%"/>
	<div class="fuelshade" style="display:none" data-d="mid={{mid}}" data-a="preview"></div>
	<div class="fuelbtnbox" style="display:none">
		<div class="fuelbtn fuelbtn1" data-img="{{image}}" data-a="repost"></div>
		<div class="fuelbtn fuelbtn2" data-d="mid={{mid}}" data-a="preview"></div>
	</div>
</div>
	</script>
<!--  -->
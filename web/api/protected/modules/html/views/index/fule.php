	<!--  -->
	<div class="page pagebg1">
		<!-- logo -->
		<div class="logo">PORSCHE</div>
		<!-- nav -->
		<div class="nav">
        <a href="#" data-a="post_weibo" class="navicon"> &nbsp;</a>
            <p class="on"><a href="/race.html"><?=Yii::t('lemans','The Race')?></a></p>
            <p><a href="#"><?=Yii::t('lemans','Monitoring')?></a></p>
            <p><a href="/stand.html"><?=Yii::t('lemans','My Stand')?></a></p>
            <p><a href="/fuel.html"><?=Yii::t('lemans','Fuel')?></a></p>
		</div>
		<!-- fuel -->
		<div class="fuel">
			<div class="fuellist cs-clear">
			</div>
			<!-- fuellist -->
			<div class="fuelmore">
				<a href="#" data-a="fuel-load">LOAD MORE</a>
			</div>
		</div>
		<!-- fuel end -->
	</div>
	<!--  -->
	<script type="text/tpl" id="fuel-tpl">
<div class="fuelitem" {{#if video}}data-video="{{uri}}"{{/if}}>
	<img src="{{image}}" style="width:100%"/>
	<div class="fuelshade" style="display:none"></div>
	<div class="fuelbtnbox" style="display:none">
		<div class="fuelbtn fuelbtn1" data-a="repost"></div>
		<div class="fuelbtn fuelbtn2" data-a="preview"></div>
	</div>
</div>
	</script>
<!--  -->
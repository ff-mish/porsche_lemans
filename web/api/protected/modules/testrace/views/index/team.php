<div class="page_wrap ">
	<div class="header">
	    <a href="/" class="logo"><?php echo Yii::t("lemans", "PORSCHE")?></a>
		<a target="_blank" href="<?php echo Yii::t("lemans", "PORSCHE_LINK")?>" class="hd_info"></a>
	</div>
	<!--  -->
	<div class="page pagebg5">
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
    <div class="race_nav">
      <div class="race_time"></div>
      <div class="race_speed"></div>
      <div class="race_navitem race_navitemon"><a href="/race/index"><?=Yii::t('lemans','Networks')?></a></div>
      <div class="race_navitem "><a href="/race/index/team"><?=Yii::t('lemans','Teams')?></a></div>
    </div>
		<!-- race end -->
	</div>
</div>

<script type="application/javascript" src="/js/sticks.js"></script>
<script type="application/javascript">
        $(function($){
            $('div.loading').show();
            sticksCreate(function(){
                $('div.loading').hide();
            })
        });
</script>
  

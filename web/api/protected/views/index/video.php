<div class="page_wrap " style="background: #000;opacity: 0.3">
	<div class="header">
	    <a href="/" class="logo"><?php echo Yii::t("lemans", "PORSCHE")?></a>
		<a target="_blank" href="<?php echo Yii::t("lemans", "PORSCHE_LINK")?>" class="hd_info"></a>
	</div>
	<!--  -->
	<div class="page pagebg5">
		<!--  -->
		<div class="video-center" style="width: 600px;margin: 0 auto;">

		</div>
	</div>
</div>

<script type="text/javascript">
   window.load(function(){
   	  window.renderVideo( $('.video-center') , '<?php echo $mediaAr->uri?>' , "<?php echo $mediaAr->teaser_image?>"  );
   });
</script>

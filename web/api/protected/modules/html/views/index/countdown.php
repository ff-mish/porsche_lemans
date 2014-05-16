	<!--  -->
	<div class="page pagebg7">
        <!-- logo -->
        <div class="logo">PORSCHE</div>
        <!-- nav -->
        <div class="nav">
            <div class="navicon"></div>
            <p class="on"><a href="/race.html"><?=Yii::t('lemans','The Race')?></a></p>
            <p><a href="#"><?=Yii::t('lemans','Monitoring')?></a></p>
            <p><a href="/stand.html"><?=Yii::t('lemans','My Stand')?></a></p>
            <p><a href="/fuel.html"><?=Yii::t('lemans','Fuel')?></a></p>
        </div>
        <!-- count -->
        <div class="count">
            <div class="conut_tit" data-fadein>#24socialrace</div>
            <div class="conut_down cs-clear" data-fadein>
                <div class="conut_downitem">00</div>
                <div class="conut_downitem">00</div>
                <div class="conut_downitem">00</div>
                <div class="conut_downitem">00</div>
            </div>
            <div class="conut_watch" data-fadein><?=Yii::t('lemans','watch the trailer')?></div>
        </div>

        <!-- Begin VideoJS -->
        <div id="video-wrap">
          <video id="bg_video_1" style="width: 100%;height: 100%;" class="video-js vjs-default-skin"
            preload="auto"
              poster="/images/bg7.jpg"
              data-setup='{"controls": false, "autoplay": true}'>
             <source src="/videos/small.mp4" type='video/mp4' />
             <source src="http://video-js.zencoder.com/oceans-clip.webm" type='video/webm' />
             <source src="http://video-js.zencoder.com/oceans-clip.ogv" type='video/ogg' />
            </video>
        </div>
          <!-- End VideoJS -->

        <!-- count end -->
	</div>
	<!--  -->
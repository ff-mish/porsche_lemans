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
                <?php if (Yii::app()->language == "zh_cn"): ?>
                    <a target="_blank" href="http://v.t.sina.com.cn/share/share.php?title=%e5%8a%a0%e5%85%a5%e6%88%91%e7%9a%84%e9%98%9f%e4%bc%8d%e5%90%a7%ef%bc%81%40%e4%bf%9d%e6%97%b6%e6%8d%b7+%e9%82%80%e4%bd%a0%e5%8f%82%e5%8a%a0%23%e5%8b%92%e8%8a%92%e7%a4%be%e4%ba%a4%e8%80%90%e5%8a%9b%e8%b5%9b%23%e3%80%82%e4%bb%a5%e5%be%ae%e5%8d%9a%e4%b9%8b%e5%90%8d%ef%bc%8c%e5%8a%a9%e5%8a%9b%e5%8b%92%e8%8a%92%e7%ab%9e%e8%b5%9b%e3%80%82&url=http://porsche.com/24socialrace&pic=http://24socialrace.porsche.com/images/banner_cn.jpg" class="navicon"></a>
                <?php else:?>
                    <a target="_blank" href="https://twitter.com/intent/tweet?text=Join+my+team!+@Porsche+introduces+%2324SocialRace+the+better+you%27ll+tweet,+the+faster+you%27ll+go!+http://por.sc/24" class="navicon"></a>
                <?php endif;?>
                <div class="post_tips">
                    <?=Yii::t('lemans','Tweet')?>
                    <span>◆</span>
                </div>
            </div>
            <div class="header-space"></div>
            <p><a href="/winner"><?php echo Yii::t('lemans','Winners')?></a></p>
            <p ><a href="/race"><?php echo Yii::t('lemans','Simulation')?></a></p>
            <p class="on"><?php echo Yii::t('lemans','Timelapse')?></p>
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
        <div id="container"></div>

        <div id="hourContainer"></div>
        <div id="hourBar">
            <div id="hourBox" onselectstart="return false;">
            </div>
            <div id="hourProcess"></div>
            <div id="playBtn">Play</div>
            <div id="playTime"></div>
        </div>
    </div>
</div>
<style>
    #my_video_0 {display:none;}
    .no-webgl #my_video_0 {display:block;}
    .video_wrap {top:80px;}
    #container {}
</style>
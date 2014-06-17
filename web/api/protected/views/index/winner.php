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
            <p class="on"><?=Yii::t('lemans','Winners')?></p>
            <p ><a href="/race"><?=Yii::t('lemans','Simulation')?></a></p>
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
        <div class="winner_result">
            <img style="height:300px;" src="/images/winner_prizes.png">
            <h3><?=Yii::t('lemans','WINNER_RESULT')?></h3>
        </div>
    </div>
</div>
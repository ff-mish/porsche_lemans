	<!--  -->
	<div class="bg1">
		<!-- logo -->
    <div class="header">
        <div class="logo">PORSCHE</div>
        <div class="hd_info"></div>
    </div>
    <div class="page">
      <!-- nav -->
      <div class="nav">
          <a href="#" data-a="post_weibo" class="navicon"> &nbsp;</a>
              <p><a href="/race.html"><?=Yii::t('lemans','The Race')?></a></p>
              <p><a href="/monitoring.html"><?=Yii::t('lemans','Monitoring')?></a></p>
              <p class="on"><a href="/stand.html"><?=Yii::t('lemans','My Stand')?></a></p>
              <p><a href="/fuel.html"><?=Yii::t('lemans','Fuel')?></a></p>
      </div>
      <!-- stand -->
      <div class="stand">
        <div id="data-stand" style="display:none" 
             data-is_invited="<?php echo $is_invited ? 1: 0?>"
             data-team_id="<?php echo $team_id?>"
             data-team_name="<?php echo $team_name?>"
             >
        </div>
        
        <div class="stand_tit">
          <input class="team_name" value="" />
          <span id="team-score"></span>
        </div>
          <!--  -->
        <div class="teambuild_member stand_useritem cs-clear">

        </div>
        <!--  -->
        <div class="stand_achivments cs-clear">
          <h2 class="fl">Achievements</h2>
          <div class="stand_achivmentsbox">
          </div>
        </div>
        <!--  -->
        <div class="stand_tweet">
          <h2 class="fl">Lastest Posts</h2>
        </div>
        <!--  -->
        <div class="stand_chart">
          <div class="stand_chart_score">
          </div>
          <div class="stand_chart_speed" >
            <div class="stand_chart_tip">
              Tweet content from Fuel section to increase your quality..
              <span>◆</span>
            </div>
          </div>
          <div class="stand_chart_quality" >
            <div class="stand_chart_tip">
              Tweet content from Fuel section to increase your quality..
              <span>◆</span>
            </div>
          </div>
          <div class="stand_chart_assiduite" >
            <div class="stand_chart_tip">
              Tweet content from Fuel section to increase your quality..
              <span>◆</span>
            </div>
          </div>
          <div class="stand_chart_impact" >
            <div class="stand_chart_tip">
              Tweet content from Fuel section to increase your quality..
              <span>◆</span>
            </div>
          </div>
        </div>
        <!--  -->
        <div class="stand_add"></div>
      </div>
    </div>
		<!-- stand end -->
	</div>
	<!--  -->
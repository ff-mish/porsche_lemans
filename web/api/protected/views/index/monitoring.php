<div class="page_wrap ">
	<div class="header">
	    <a href="/" class="logo"><?php echo Yii::t("lemans", "PORSCHE")?></a>
		<a target="_blank" href="<?php echo Yii::t("lemans", "PORSCHE_LINK")?>" class="hd_info"></a>
	</div>
	<div class="page pagebg1">
		<!-- nav -->
		<div class="nav">
			<div class="post_link">
				<a href="#" data-a="post_weibo" class="navicon"></a>
				<div class="post_tips">
					<?=Yii::t('lemans','Make a tweet')?>
					<span>◆</span>
				</div>
			</div>
			<p><a href="/stand"><?=Yii::t('lemans','Stand')?></a></p>
			<p><a href="/race"><?=Yii::t('lemans','Race')?></a></p>
			<p><a href="/fuel"><?=Yii::t('lemans','Fuel')?></a></p>
			<p class="on"><?=Yii::t('lemans','Monitoring')?></p>
			<p> &nbsp; </p>
			<div class="mobile_nav">
				<p data-a="legal-mentions" class="btn legal"><?php echo Yii::t("lemans", "Legal Mentions")?></p>
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
		<!-- monitoring -->
		<div class="monitor">
			<h1 class="monitor_tit">Social Race Monitoring:  What’s happening right now?</h1>
			<div class="monitor_com tweet-con cs-clear">
				<!--  -->
				<div class="monitor_item">
					<h2>Porsche</h2>
					<div class="monitor_list">
					</div>
				</div>
				<!--  -->
				<div class="monitor_item">
					<h2>Profile</h2>
					<div class="monitor_list">
					</div>
				</div>
				<!--  -->
				<div class="monitor_item tweet-con">
					<h2>Team</h2>
					<div class="monitor_list">
					</div>
				</div>
				<!--  -->
				<div class="monitor_item tweet-con">
					<h2>#24SocialRace</h2>
					<div class="monitor_list">
					</div>
				</div>
				<!--  -->
			</div>
		</div>
		<!-- monitoring end -->
	</div>
	<div class="tweet-con">
	  <ul class="tweet-list clearfix">
	    <li class="item clearfix">
	      <ul class="tweet-items">
	      </ul>
	    </li>
	    <li class="item">
	      <ul class="tweet-items">
	      </ul>
	    </li>
	    <li class="item">
	      <ul class="tweet-items">
	      </ul>
	    </li>
	    <li class="item">
	      <ul class="tweet-items">
	      </ul>
	    </li>
	  </ul>
	</div>
<div>

<script type="text/tpl" id="tweet-item-tpl">
        <li class="tweet-signle-item clearfix">
        {{#if media}}<div class="avatar"><img src="{{media}}" alt="" /></div>{{/if}}
          <div class="desc {{#unless media}}no-media{{/unless}}">
            <div class="title">@{{name}}</div>
            <div class="from from-{{from}}">{{from}}</div>
            <div class="profile-msg">{{content}}</div>
            <div class="time">{{date}}</div>
          </div>
          <div class="btns">
          	<a href="#" data-a="mo-retweet" data-d="uuid={{uuid}}"><img src="/images/retweet.png"/></a>
          	<a href="#" data-a="mo-comment" data-d="uuid={{uuid}}"><img src="/images/comment.png"/></a>
          </div>
        </li>
</script>

<script type="text/tpl" id="tweet-panel-tpl">
  
</script>
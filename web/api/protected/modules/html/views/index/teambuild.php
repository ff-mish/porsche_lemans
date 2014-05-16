	<!--  -->
	<div class="page pagebg6">
		<!-- logo -->
		<div class="logo">PORSCHE</div>
		<!-- nav -->
		<div class="nav">
			<a href="#" data-a="post_weibo" class="navicon"> &nbsp;</a>
			<p><a href="/race.html">The Race</a></p>
			<p><a href="#">Monitoring</a></p>
			<p class="on"><a href="/stand.html">My Stand</a></p>
			<p><a href="/fuel.html">Fuel</a></p>
		</div>
		<!-- teambuild -->
		<div class="teambuild">
			<div class="teambuild_step">step 1/3</div>
			<div class="teambuild_tit">team building</div>
			<div id="teambuild_info" style="display:none;">
				<a href="javascript:;" data-a="member_invent" class="member_add cs-clear">+</a>
			</div>
			<form role="form" class="teambuild_from" style="display:none;">
        <input name="name" value="" placeholder="Please input your team name" class="input"/>
				<button type="submit"> Add Team </button>
			</form>
			<!-- <div class="teambuild_member teambuild_info1 cs-clear">
				<div class="member_item ">
					<img src="images/phodemo.jpg" />
					<p class="member_name">@Mradrien_</p>
				</div>
				<div class="member_speed"></div>
				<div class="memeber_space">11K</div>
			</div>
			<div class="teambuild_member teambuild_info2 cs-clear">
				<div class="member_item cs-clear">
					<img src="images/phodemo.jpg" />
					<p class="member_name">@Mradrien_</p>
				</div>
				<div class="member_speed"></div>
				<div class="memeber_space">11K</div>
			</div>
			<a href="#" class="member_add cs-clear">+</a>
			<a href="#" class="member_ok cs-clear">OK</a> -->
		</div>
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
		<!-- teambuild end -->
	</div>
	<!--  -->
<!--  -->
<script type="text/tpl" id="teambuild-member-tpl">
    <div class="teambuild_member teambuild_info1 cs-clear">
		<div class="member_item ">
			<img src="{{avatar}}" />
			<p class="member_name">@{{name}}</p>
		</div>
		<div class="member_speed"></div>
		<div class="memeber_space">{{space}}K</div>
	</div>
</script>
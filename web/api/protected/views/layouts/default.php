<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <title><?php echo Yii::t("lemans", "PORSCHE")?></title>
    <link href="/css/style.css" rel="stylesheet" type="text/css" />
    <link href="/css/js.css" rel="stylesheet" type="text/css" />
	<!--[if lt IE 9]>
	<link href="/css/ie8.css" rel="stylesheet" type="text/css" />
	<![endif]-->
    <?php if (UserAR::crtuser() && UserAR::crtuser()->from == UserAR::FROM_TWITTER): ?>
    <link href="/css/twitter.css" rel="stylesheet" type="text/css" />
    <?php endif;?>
    <script type="text/javascript">
      <?php if (UserAR::crtuser()): ?>
      window.from = "<?php echo UserAR::crtuser()->from?>";
      window.topic = "<?php echo Yii::app()->params["topic"]?>";
      <?php else:?>
        window.from = "";
      <?php endif;?>
        
      window.is_start = <?php echo $this->is_start ? 1 : 0?>
    </script>
</head>
<body class="lang-<?php echo Yii::app()->language == "en_us" ? "en": "cn" ?> bg1 <?php echo $this->classname ?> <?php if (UserAR::crtuser()){echo UserAR::crtuser()->from;} ?>" data-page="<?php echo $this->page_name?>">
	<!--  -->
	<?php echo $content?>
	<!--  -->
  
	<div class="footer">
        <div class="footer_link cs-clear">
            <a class="btn footer-icon" href="/stand" data-a="start-tutr">&nbsp;&nbsp;</a>
            <p class="btn legal" data-a="legal-mentions"><?php echo Yii::t("lemans", "Legal Mentions")?></p>
            <div class="btn <?php echo Yii::app()->language;?>" id="share"><?php echo Yii::t("lemans", "Share")?>
	            <?php if (Yii::app()->language == "zh_cn"): ?>
		            <div class="share-btns">
			            <a target="_blank" href="http://v.t.sina.com.cn/share/share.php?title=%e5%92%8c%e6%88%91%e4%bb%ac%e4%b8%80%e5%90%8c%e5%9b%9e%e5%bd%92%e5%8b%92%e8%8a%92%ef%bc%8c%e4%bd%a0%e5%87%86%e5%a4%87%e5%a5%bd%e4%ba%86%e5%90%97%ef%bc%9f%40%e4%bf%9d%e6%97%b6%e6%8d%b7+%e9%82%80%e4%bd%a0%e5%8f%82%e5%8a%a0%23%e5%8b%92%e8%8a%92%e7%a4%be%e4%ba%a4%e8%80%90%e5%8a%9b%e8%b5%9b%23%e3%80%82" class="i-sina"></a>
			            <a target="_blank" href="https://plus.google.com/share?url=&t=" class="i-g"></a>
			            <a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url=&title=&ro=false&summary=&source=" class="i-in"></a>
			            <a target="_blank" href="http://pinterest.com/pin/create/button/?url=/node/[nid]&description=[title]" class="i-p"></a>
		            </div>
	            <?php else:?>
		            <div class="share-btns">
			            <a target="_blank" href="https://www.facebook.com/share.php?u=&t=&pic=" class="i-fb"></a>
			            <a target="_blank" href="https://twitter.com/intent/tweet?text=&pic=" class="i-tw"></a>
			            <a target="_blank" href="https://plus.google.com/share?url=&t=" class="i-g"></a>
			            <a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url=&title=&ro=false&summary=&source=" class="i-in"></a>
			            <a target="_blank" href="http://pinterest.com/pin/create/button/?url=/node/[nid]&description=[title]" class="i-p"></a>
		            </div>
	            <?php endif;?>
            </div>
        </div>
        <div class="footer_language"><a data-lang="en_us" href="#">En</a> | <a data-lang="zh_cn" href="#">中文</a></div>
    </div>

	<div class="loading-wrap">
		<div class="logo"></div>
		<div class="loading-loader">
			<div class="loading-bar"></div>
		</div>
		<div class="loading-percentage"></div>
	</div>

    <div id="legal-notice">
      <div class="popup_close"></div>
      <h2><?php echo Yii::t("lemans", "Legal Notice")?></h2>
      <div class="legal-con clearfix">
        <div class="legal-left">
	        <div class="intro">
		        WEBSITE TERMS AND CONDITIONS OF USE
				<br/><br/>
				BY ACCESSING AND BROWSING THIS WEBSITE, YOU ACCEPT, WITHOUT LIMITATION OR QUALIFICATION, THE FOLLOWING TERMS AND CONDITIONS OF USE. IF YOU DO NOT AGREE, PLEASE DISCONTINUE YOUR USE OF THIS WEBSITE.
				<br/><br/>
	        </div>
			Modification of Terms<br/>
			Porsche may, at any time and without notice, amend these terms and conditions ("Terms"), or may limit or deny access to, or change the content of, the website. You should periodically visit this page to review the then current Terms to which you are bound. These Terms are applicable to all Porsche North American websites. When leaving this website, whether or not to visit another Porsche website, please read the website terms and the privacy policy applicable to such website to ensure you understand and agree with the terms applicable to that website.
			<br/><br/>
			Applicability<br/>
			Information and materials displayed on the website, including without limitation, product pricing, specifications, warranty and/or other information, are applicable to products sold in the United States and not necessarily to Porsche products sold in other markets.
			<br/><br/>
			Restricted Use and Copyrights<br/>
			All photographs, audio and video clips, picture images, graphics, links, website architecture, format, layout and data structures, and all other items contained on the website are copyrighted unless otherwise noted and may not be used in any manner, except as provided in these Terms or in the website text, without Porsche's prior written permission. Images of people or places and Porsche products, are either property of, or used with permission by, Porsche. Any unauthorized use of these materials may violate copyright, trademark and privacy laws, and other applicable statutes. You may browse this website and download or print a copy of material displayed on the website for your personal use only and not for redistribution, unless consented to in writing by Porsche. This limited consent shall automatically terminate upon your breach of any of these website Terms.
			<br/><br/>
			Trademarks<br/>
			Porsche, Porsche Crest, Porsche Design, 911, Spyder, Carrera, Targa, Tiptronic, Porsche Speedster, Varioram, CVTip, VarioCam, Boxster, Cayenne, Porsche Cayenne, Cayman, Panamera, Tequipment, "Porsche. There is no Substitute.", RS, Porsche Bike FS, Porsche Bike S, Pan Americana, PCCB, PCM, Technorad, Varrera, and other Porsche product names, model numbers, logos, commercial symbols, trade names and slogans are trademarks and the distinctive shapes of Porsche automobiles are trade dress of Dr. Ing. h.c. F. Porsche AG ("PAG") and are protected by U.S. and international trademark laws. You are prohibited from using any of the marks appearing on this website without the express prior written consent of PAG, except as permitted by applicable laws. Other marks and logos shown on this website may be marks owned by third parties not affiliated with Porsche and are used with permission. Nothing shown on this website should be construed as granting, by implication, estoppel or otherwise, any permission, license or right to use any trademark, service mark or trade name displayed on this website without the written permission of PAG or the third party owner. The use of any Porsche logo or mark, whether registered or unregistered, as a hyperlink to this website or any other Porsche website is strictly prohibited unless consented to in writing by PAG.
			<br/><br/>
			Disclaimers<br/>
			All data contained on the website relating to third-party products or services, including but not limited to prices, availability of service or product, product feature or service coverage, if any, should be verified with the party supplying the product or service. Porsche may at any time without notice amend the data displayed on the website. While Porsche endeavors to periodically update posted information, not all information may be current, and Porsche does not guarantee the accuracy or reliability of such data. Specifications on the website are for comparison purposes only; specifications, standard equipment, options and pricing are subject to change without notice. Some options may be unavailable when your car is built. Some vehicles may be shown with non-U.S. or non-Canadian equipment. Please consult your dealer for advice concerning the current availabilty and pricing of options. Manufacturer's Suggested Retail Price (MSRP) excludes transportation fees, taxes, license, title, optional or regionally required equipment. Actual dealer price may vary. Porsche is not responsible for any loss or damage caused by your reliance on any data contained on this website. Porsche recommends seat belt usage and observance of traffic laws at all times.
			<br/><br/>
			This website is provided as a convenience to you on an "as is" and "as available" basis. Porsche does not warrant that your access to these website pages will be uninterrupted or error-free. NO WARRANTY OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF TITLE OR NON-INFRINGEMENT, OR IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE, IS MADE IN RELATION TO THE AVAILABILITY, ACCURACY, RELIABILITY OR CONTENT OF THE SITE PAGES. PORSCHE SHALL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL OR PUNITIVE DAMAGES, LOST PROFITS, REVENUES OR DATA, OR LOSSES FOR BUSINESS INTERRUPTION ARISING OUT OF THE USE OF OR INABILITY TO USE THIS WEBSITE, EVEN IF PORSCHE HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. Porsche assumes no responsibility and/or liability for any damages to or viruses that may infect your computer equipment or other property on account of or arising out of your use of or access to this website. Some jurisdictions do not allow exclusion of certain warranties or limitations of liability, so the above limitations or exclusions may not apply to you. Porsche's liability in any case shall, however, be limited to the greatest extent permitted by law.
			<br/><br/>
			Links<br/>
			Porsche may include links to other sites on the Internet that are owned or operated by third parties, including authorized Porsche dealers. When visiting these third-party sites, you do so at your own risk. You should review and determine if you agree to a particular site's terms and conditions of use before using such site. Porsche does not control these sites and assumes no responsibility for their content. A link to a non-Porsche site does not imply that Porsche endorses the site or the products or services described on such sites.
			<br/><br/>
			Submissions<br/>
			Any communication or material you transmit to the website (including feedback, data, answers, questions, comments, suggestions, ideas, plans, orders, requests or the like) will be treated as non-confidential and non-proprietary. All materials transmitted to the website becomes the property of Porsche and may be used for any purpose. Porsche may publish the material and/or incorporate it or any concepts described in it in our products without compensation, restrictions on use, acknowledgment of source, accountability, or liability.
			<br/><br/>
			Governing Law<br/>
			This website is governed by and subject to the laws of the State of Georgia and, where applicable, U.S. federal law.
        </div>
        <div class="legal-right">
	        Restricted Use and Copyrights<br/>
			All photographs, audio and video clips, picture images, graphics, links, website architecture, format, layout and data structures, and all other items contained on the website are copyrighted unless otherwise noted and may not be used in any manner, except as provided in these Terms or in the website text, without Porsche's prior written permission. Images of people or places and Porsche products, are either property of, or used with permission by, Porsche. Any unauthorized use of these materials may violate copyright, trademark and privacy laws, and other applicable statutes. You may browse this website and download or print a copy of material displayed on the website for your personal use only and not for redistribution, unless consented to in writing by Porsche. This limited consent shall automatically terminate upon your breach of any of these website Terms.
			<br/><br/>
			Trademarks<br/>
			Porsche, Porsche Crest, Porsche Design, 911, Spyder, Carrera, Targa, Tiptronic, Porsche Speedster, Varioram, CVTip, VarioCam, Boxster, Cayenne, Porsche Cayenne, Cayman, Panamera, Tequipment, "Porsche. There is no Substitute.", RS, Porsche Bike FS, Porsche Bike S, Pan Americana, PCCB, PCM, Technorad, Varrera, and other Porsche product names, model numbers, logos, commercial symbols, trade names and slogans are trademarks and the distinctive shapes of Porsche automobiles are trade dress of Dr. Ing. h.c. F. Porsche AG ("PAG") and are protected by U.S. and international trademark laws. You are prohibited from using any of the marks appearing on this website without the express prior written consent of PAG, except as permitted by applicable laws. Other marks and logos shown on this website may be marks owned by third parties not affiliated with Porsche and are used with permission. Nothing shown on this website should be construed as granting, by implication, estoppel or otherwise, any permission, license or right to use any trademark, service mark or trade name displayed on this website without the written permission of PAG or the third party owner. The use of any Porsche logo or mark, whether registered or unregistered, as a hyperlink to this website or any other Porsche website is strictly prohibited unless consented to in writing by PAG.
			<br/><br/>
			Disclaimers<br/>
			All data contained on the website relating to third-party products or services, including but not limited to prices, availability of service or product, product feature or service coverage, if any, should be verified with the party supplying the product or service. Porsche may at any time without notice amend the data displayed on the website. While Porsche endeavors to periodically update posted information, not all information may be current, and Porsche does not guarantee the accuracy or reliability of such data. Specifications on the website are for comparison purposes only; specifications, standard equipment, options and pricing are subject to change without notice. Some options may be unavailable when your car is built. Some vehicles may be shown with non-U.S. or non-Canadian equipment. Please consult your dealer for advice concerning the current availabilty and pricing of options. Manufacturer's Suggested Retail Price (MSRP) excludes transportation fees, taxes, license, title, optional or regionally required equipment. Actual dealer price may vary. Porsche is not responsible for any loss or damage caused by your reliance on any data contained on this website. Porsche recommends seat belt usage and observance of traffic laws at all times.
			<br/><br/>
			This website is provided as a convenience to you on an "as is" and "as available" basis. Porsche does not warrant that your access to these website pages will be uninterrupted or error-free. NO WARRANTY OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF TITLE OR NON-INFRINGEMENT, OR IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE, IS MADE IN RELATION TO THE AVAILABILITY, ACCURACY, RELIABILITY OR CONTENT OF THE SITE PAGES. PORSCHE SHALL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL OR PUNITIVE DAMAGES, LOST PROFITS, REVENUES OR DATA, OR LOSSES FOR BUSINESS INTERRUPTION ARISING OUT OF THE USE OF OR INABILITY TO USE THIS WEBSITE, EVEN IF PORSCHE HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. Porsche assumes no responsibility and/or liability for any damages to or viruses that may infect your computer equipment or other property on account of or arising out of your use of or access to this website. Some jurisdictions do not allow exclusion of certain warranties or limitations of liability, so the above limitations or exclusions may not apply to you. Porsche's liability in any case shall, however, be limited to the greatest extent permitted by law.
			<br/><br/>
			Links<br/>
			Porsche may include links to other sites on the Internet that are owned or operated by third parties, including authorized Porsche dealers. When visiting these third-party sites, you do so at your own risk. You should review and determine if you agree to a particular site's terms and conditions of use before using such site. Porsche does not control these sites and assumes no responsibility for their content. A link to a non-Porsche site does not imply that Porsche endorses the site or the products or services described on such sites.
			<br/><br/>
			Submissions<br/>
			Any communication or material you transmit to the website (including feedback, data, answers, questions, comments, suggestions, ideas, plans, orders, requests or the like) will be treated as non-confidential and non-proprietary. All materials transmitted to the website becomes the property of Porsche and may be used for any purpose. Porsche may publish the material and/or incorporate it or any concepts described in it in our products without compensation, restrictions on use, acknowledgment of source, accountability, or liability.
			<br/><br/>
			Governing Law<br/>
			This website is governed by and subject to the laws of the State of Georgia and, where applicable, U.S. federal law.
        </div>
      </div>
    </div>
<!--  -->
<script type="text/javascript" src="/js/sea/sea-debug.js" data-config="../config.js"></script>
<script type="text/javascript" src="/js/lp.core.js"></script>
<script type="text/javascript" src="/js/lang/<?php echo Yii::app()->language;?>.js"></script>
<script type="text/javascript" src="/js/lp.base.js"></script>
<!--  -->
    <!--IE6透明判断-->
    <!--[if IE 6]>
    <script src="/js/DD_belatedPNG.js"></script>
    <script>
        DD_belatedPNG.fix('*');
        document.execCommand("BackgroundImageCache", false, true);
    </script>
    <![endif]-->

    <!--[if lt IE 9]>
    <style type="text/css">
    	body .teambuild_member{position:relative;}
    	/*body .member_add{*/
    	/*background: none;*/
    	/*filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#66000000, endColorstr=#66000000);*/
    /* For IE 8*/
    /*-ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#66000000, endColorstr=#66000000);*/
/*z-index:1; */
/*behavior: url(/css/iecss3.htc); }*/
    </style>
	<![endif]-->
</body>
</html>
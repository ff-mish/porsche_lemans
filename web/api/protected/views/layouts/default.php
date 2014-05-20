<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <title>PORSCHE</title>
    <link href="/css/style.css" rel="stylesheet" type="text/css" />
    <link href="/css/js.css" rel="stylesheet" type="text/css" />
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
<body class="bg1 <?php echo $this->classname ?>" data-page="<?php echo $this->page_name?>">
	<!--  -->
	<?php echo $content?>
	<!--  -->

<div class="footer">
        <div class="footer_link cs-clear">
            <p>Legal Mentions</p>
            <p>Share</p>
        </div>
        <div class="footer_language"><a href="#">En</a> | <a href="#">中文</a></div>
    </div>
<!--  -->
<script type="text/javascript" src="/js/sea/sea-debug.js" data-config="../config.js"></script>
<script type="text/javascript" src="/js/lp.core.js"></script>
<script type="text/javascript" src="/js/lp.base.js"></script>
<script type="text/javascript" src="/js/lp.ext.js"></script>
<!--  -->
    <!--IE6透明判断-->
    <!--[if IE 6]>
    <script src="/js/DD_belatedPNG.js"></script>
    <script>
        DD_belatedPNG.fix('*');
        document.execCommand("BackgroundImageCache", false, true);
    </script>
    <![endif]-->
</body>
</html>
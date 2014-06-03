<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=640, minimum-scale=0.5, maximum-scale=1, target-densityDpi=290,user-scalable = no" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <title><?php echo Yii::t("lemans", "PORSCHE")?></title>
    <link href="/admin/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/admin/css/jquery.dataTables_themeroller.min.css" />
    <link rel="stylesheet" href="/admin/css/jquery.dataTables.min.css" />
    <link href="/admin/css/style.css" rel="stylesheet" type="text/css" />
</head>
  <body class="container" ng-app="AdminApp">
  <div class="row">
    <div class="menu-bar span2">
      <ul class='nav nav-tabs nav-stacked'>
        <li><a href="/admin/index/qa">Q&A</a></li>
        <li><a href="/admin/index/team">Team</a></li>
        <li><a href="/admin/index/fuel">Fuel</a></li>
        <li><a href="/admin/index/email">E-Mails</a></li>
        <li><a href="/admin/index/logout">Logout</a></li>
      </ul>
    </div>
    <div class="container  span10">
        <?php echo $content?>
    </div>
  </div>

  <script type="text/javascript" src="/admin/js/jquery-1.8.3.min.js"></script>
  <script type="text/javascript" src="/admin/js/jquery.dataTables.js"></script>
  <script type="text/javascript" src="/admin/js/jquery.form.js"></script>
  <script type="text/javascript" src="/admin/js/angular.js"></script>
  <script type="text/javascript" src="/admin/js/script.js"></script>
</body>
</html>
<?php
use midl\html\HTML;
?>
<!DOCTYPE html>
<html ng-app="app">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  
  <title><?php echo HTML::esc($pageTitle = $this->controller->getTitle());?></title>
  
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?php echo HTML::escAttr($pageTitle);?>">
  <link rel="apple-touch-icon" href="<?php echo $logoPng = $this->getImgPath("logo.png");?>">
  
  <link rel="shortcut icon" type="image/png" sizes="196x196" href="<?php echo $logoPng;?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $this->getImgPath("logo32x32.png");?>">
  
  <?php echo $this->getHead();?>
  
  <link type="text/css" rel="stylesheet" href="<?php echo $this->getCSSPath();?>" />
  
  <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>

<body ng-controller="AppCtrl" class="theme-default">
<?php
$this->includeView("common/header.php");

$this->includeBody();

$this->includeView("common/footer.php");
?>
<script type="text/javascript" src="<?php echo $this->getJSPath();?>"></script>
<?php echo $this->getScript();?>
</body>
</html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="index,follow" />
<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>" />
<link href="css/style.css" rel="stylesheet" type="text/css" /> 
<!--[if IE 7]><link rel="stylesheet" media="all" type="text/css" href="css/ie7.css" /><![endif]-->
<!--[if IE 8]><link rel="stylesheet" media="all" type="text/css" href="css/ie8.css" /><![endif]-->
<link href="fonts/stylesheet.css" rel="stylesheet" type="text/css" />
<!--slidercss-->
<link rel="stylesheet" href="css/basic-jquery-slider.css" />
<script src="js/jquery-1.6.2.min.js" type="text/javascript" language="javascript"></script>
<script src="js/basic-jquery-slider.js"  type="text/javascript" language="javascript"></script>
<script src="js/custom.js" type="text/javascript" language="javascript"></script>
<script src="js/input.js" type="text/javascript" language="javascript"></script>

<!--  Attach the plug-in to the slider parent element and adjust the settings as required -->
<script  type="text/javascript" language="javascript">
      $(document).ready(function() {
        
        $('#banner').bjqs({
          'animation' : 'slide',
          'width' : 960,
          'height' : 450
        });
        
      });
    </script>

<!--[if lt IE 10]>
<script type="text/javascript" src="js/pie.js" language="javascript"></script>

<style type="text/css">
ul.bjqs h1, h2, ol.bjqs-markers li a {
	behavior: url("js/PIE.htc") !important;
}
</style>
<![endif]-->
<script type="text/javascript">
	
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-38401210-1']);
  _gaq.push(['_setDomainName', 'rocketmailmerge.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body>

<!--Header Start Here-->
<div id="header">
  <div id="top_header">
    <div class="center_frame">
      <div class="top" style="float:right;">
        <form  style="visibility: hidden;" name="search" method="post" action="">
          <div class="search">
            <input type="text" value="Search" />
            <a href="http://www.rocketmailmerge.com/"></a> </div>
        </form>
        <div class="language"> <a class="english active">&nbsp;</a> <!--<a href="http://www.rocketmailmerge.com/#" class="german">&nbsp;</a> <a href="http://www.rocketmailmerge.com/#" class="spanish">&nbsp;</a>--> </div>
        <div class="login"> <a href="https://app.rocketmailmerge.com/dashboard/" class="login_img">&nbsp;</a> <a href="https://app.rocketmailmerge.com/account/register.html" class="register">&nbsp;</a> </div>
      </div>
    </div>
  </div>
  <!--Top Header End Here-->
  <div id="logo_nav" <?=$topStyle?>>
    <div class="<?=$bg?>">
      <div class="center_frame"> 
        <!--Logo And Navigation Start Here-->
        <div class="logo"> <a href="http://www.rocketmailmerge.com/index.html"><img src="images/rmm_logo.png" alt="Rocket Mail Merge" /> <h2>Rocket Mail Merge</h2></a> </div>
        <ul id="navigation">
          <li><a href="http://www.rocketmailmerge.com/index.html"><span>Home</span></a></li>
          <li><a href="http://www.rocketmailmerge.com/tour.html"><span>Tour</span></a>
            <ul>
				<li> <a href="http://www.rocketmailmerge.com/tour.html#mailmerge" class="life">What Is Mail Merge</a> <span class="nav_line"></span></li>
				<li> <a href="http://www.rocketmailmerge.com/what-can-it-make.html" class="life_3">What Can It Make?</a> <span class="nav_line"></span></li>
				<li> <a href="http://www.rocketmailmerge.com/who-is-it-for.html" class="life_2">Who Is It For?</a><span class="nav_line"></span></li>
				<li> <a href="http://www.rocketmailmerge.com/tour.html#tour" class="life_1">Merging Paperwork</a> <span class="nav_line"></span></li>
				<li> <a href="http://www.rocketmailmerge.com/tour.html#screenshots" class="life_4">Screenshots</a> </li>
            </ul>
          </li>
          <li><a href="http://www.rocketmailmerge.com/plans.html"><span>Price</span></a></li>
		  <!--<li><a href="http://www.rocketmailmerge.com/faq.html"><span>FAQ</span></a>
			<ul>
				<li> <a href="http://www.rocketmailmerge.com/faq.html#excel" class="life">Support for Excel?</a> <span class="nav_line"></span></li>
				<li> <a href="http://www.rocketmailmerge.com/faq.html#word" class="life_1">Support for Word?</a> <span class="nav_line"></span></li>
				<li> <a href="http://www.rocketmailmerge.com/faq.html#background" class="life_3">What's a background</a> </li>
            </ul>
		  </li>-->
		  <li><a href="http://www.rocketmailmerge.com/about.html"><span>Our Team</span></a></li>
          <li><a href="http://www.rocketmailmerge.com/contact.html"><span>Contact Us</span></a></li>          
        </ul>
        <!--Logo And Navigation End Here--> 
        <!--slider here--> 

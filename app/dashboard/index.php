<?php
	//error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	
	$server->fetchDashboardData();	
	
	//echo "<pre>";
	//die(print_r($server->outData));
	
	//Used to offset our times, is set by a cookie in main.js
	$gmtOffset = 0;
	if(isset($_COOKIE['gmtoffset'])) 
		@$gmtOffset = (is_int((int)$_COOKIE['gmtoffset']) && (int)$_COOKIE['gmtoffset'] < 780 && (int)$_COOKIE['gmtoffset'] > -780 ? ($_COOKIE['gmtoffset'] * 60) : 0);

	if($server->outData['statistics']['subscription_status'] == "cancelled") {
		header( 'Location: https://app.rocketmailmerge.com/account/reactivate.html?id='.$server->outData['statistics']['chargebee_id'] ) ;
	}
?>	
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Rocket Mail Merge - Dashboard</title>
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
<!--Stylesheets-->
<link rel="stylesheet" href="css/reset.css" />
<link rel="stylesheet" href="css/main.css" />
<link rel="stylesheet" href="css/typography.css" />
<link rel="stylesheet" href="css/tipsy.css" />
<link rel="stylesheet" href="js/cl_editor/jquery.cleditor.css" />
<link rel="stylesheet" href="uploadify/uploadify.css" />
<link rel="stylesheet" href="css/jquery.ui.all.css" />
<link rel="stylesheet" href="css/fullcalendar.css" />
<link rel="stylesheet" href="css/bootstrap.css" />
<link rel="stylesheet" href="js/jq_tables/demo_table_jui.css" />
<link rel="stylesheet" href="js/fancybox/jquery.fancybox-1.3.4.css" />
<link rel="stylesheet" href="css/highlight.css" />
<link rel="stylesheet" href="fonts/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="css/guiders-1.3.0.css" type="text/css" />
<!--[if lt IE 9]>
    <script src="js/html5.js"></script>
    <![endif]-->
<!--Javascript-->
<script type="text/javascript" src="js/jquery.min.js"> </script>
<script type="text/javascript" src="js/excanvas.js"> </script>
<script type="text/javascript" src="js/jquery.flot.js"> </script>
<script type="text/javascript" src="js/jquery.flot.stack.js"> </script>
<script type="text/javascript" src="js/jquery.flot.pie.js"> </script>
<script type="text/javascript" src="js/jquery.flot.resize.js"> </script>
<script type="text/javascript" src="js/jquery.quicksand.js"> </script>
<script type="text/javascript" src="js/jquery.easing.1.3.js"> </script>
<script type="text/javascript" src="js/jquery.tipsy.js"> </script>
<script type="text/javascript" src="js/cl_editor/jquery.cleditor.min.js"> </script>
<script type="text/javascript" src="uploadify/swfobject.js"></script>
<script type="text/javascript" src="uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="js/jquery.autogrowtextarea.js"></script>
<script type="text/javascript" src="js/form_elements.js"></script>
<script type="text/javascript" src="js/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="js/jquery.ui.progressbar.js"></script>
<script type="text/javascript" src="js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="js/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="js/fullcalendar.js"></script>
<script type="text/javascript" src="js/gcal.js"></script>
<script type="text/javascript" src="js/bootstrap-modal.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="js/highlight.js"></script>
<script type="text/javascript" src="js/jq_tables/jquery.dataTables.js"></script>
<script type="text/javascript" src="js/main.js"> </script>
<script type="text/javascript" src="js/guiders-1.3.0.js"></script>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<script type="text/javascript">	
	<?php   $term_start = 0;
			$term_end   = 0;
			$inTrial    = 1;
			
			if(isset($server->outData['statistics']['subscription_current_term_start'])) {
				$term_start = $server->outData['statistics']['subscription_current_term_start'];
				$term_end   = $server->outData['statistics']['subscription_current_term_end'];
				$inTrial    = 0;
			} else if(isset($server->outData['statistics']['subscription_trial_start'])) {
				$term_start = $server->outData['statistics']['subscription_trial_start'];
				$term_end   = $server->outData['statistics']['subscription_trial_end'];
				$inTrial    = 1;
			} else {
				$term_start = time();
				$term_end   = time() + 1296000; //seconds in 15 days, to simulate a trial period.
			}
	?>
	
	var dashboardGraphData = <?= json_encode($server->outData['billing_period_generated_documents']) ?>;	
	var billingPeriodStart = <?= $term_start*1000 ?>;
	var billingPeriodEnd   = <?= $term_end*1000 ?>;
	var planIncludedPages  = <?= $server->outData['statistics']['plan_included_pages'] ?>;
	
    /**
     * Guiders are created with guider.createGuider({settings}).
     *
     * You can show a guider with the .show() method immediately
     * after creating it, or with guider.show(id) and the guider's id.
     *
     * guider.next() will advance to the next guider, and
     * guider.hideAll() will hide all guiders.
     *
     * By default, a button named "Next" will have guider.next as
     * its onclick handler.  A button named "Close" will have
     * its onclick handler set to guider.hideAll.  onclick handlers
     * can be customized too.
     */
     
	jQuery(document).ready(function() {
		clearTutorial = function() {
			$("#tutDS a").attr("href","datasources.php");
			guiders.hideAll();			
		}
		
		$(".helpTour").click( function() {
			$("#tutDS a").attr("href","datasources.php?tut=1");
			guideFirst.show('first');		
		});
		
		guideFirst = guiders.createGuider({
		  buttons: [{name: "Skip the tutorial", onclick: clearTutorial },
					{name: "Next"}],
		  description: "<img style='float: left;' src='images/tutorial/dog1.png'/><div style='padding-top: 40px; padding-bottom:30px;'>Hi there, my name is Rex. Welcome to Rocket Mail Merge! I've prepared a tutorial for you to teach you how to create a mail merge. To begin just click the 'next' button below, otherwise if you're a mail merge pro you can click the 'skip' button.</div>",
		  id: "first",
		  next: "second",
		  overlay: true,
		  title: "Welcome to Rocket Mail Merge",
		  width: 500
		})<?=$_GET['tut'] ? '.show();' : ';'?>
		
		guiders.createGuider({
		  attachTo: "#tutDash",
		  buttons: [{name: "Close, then click on datasources.", onclick: guiders.hideAll}],
		  description: "You're currently viewing the Dashboard. The dashboard gives you an overview of your account status and history. You can view other parts of the Rocket Mail Merge system by clicking on the menu items. Lets go ahead and move to the Datasources area. Just click on the Datasources link below to continue.",
		  id: "second",
		  next: "third",
		  position: "right",
		  title: "The Menu"
		});
	});
	
	
</script>

<style type="text/css">
.css_btn_class {
	font-size:28px;
	font-family:Arial;
	font-weight:normal;
	-moz-border-radius:8px;
	-webkit-border-radius:8px;
	border-radius:8px;
	border:1px solid #268a16;
	padding:32px 32px;
	margin:40px;
	text-decoration:none;
	background:-moz-linear-gradient( center top, #77d42a 5%, #5cb811 100% );
	background:-ms-linear-gradient( top, #77d42a 5%, #5cb811 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77d42a', endColorstr='#5cb811');
	background:-webkit-gradient( linear, left top, left bottom, color-stop(5%, #77d42a), color-stop(100%, #5cb811) );
	background-color:#77d42a;
	color:#306108;
	display:inline-block;
	text-shadow:1px 1px 0px #aade7c;
 	-webkit-box-shadow:inset 1px 1px 0px 0px #caefab;
 	-moz-box-shadow:inset 1px 1px 0px 0px #caefab;
 	box-shadow:inset 1px 1px 0px 0px #caefab;
}.css_btn_class:hover {
	background:-moz-linear-gradient( center top, #5cb811 5%, #77d42a 100% );
	background:-ms-linear-gradient( top, #5cb811 5%, #77d42a 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5cb811', endColorstr='#77d42a');
	background:-webkit-gradient( linear, left top, left bottom, color-stop(5%, #5cb811), color-stop(100%, #77d42a) );
	background-color:#5cb811;
}.css_btn_class:active {
	position:relative;
	top:1px;
}
/* This css button was generated by css-button-generator.com */
</style>

</head>
<body>
<!--Header-->
<header>
    <!--Logo-->
    <div id="logo"><h1>Rocket Mail Merge</h1></div>
    <!--Search-->
       
    <div class="header_search">
		<input class="helpTour greyishBtn button_small" type="button" value="Guided Help Tour">
		<input class="logout greyishBtn button_small" type="button" value="Logout">
        <!--<form action="">
            <input type="text" name="search" placeholder="Search" id="ac">
            <input type="submit" value="">
        </form>-->
    </div>
</header>
<!--Dreamworks Container-->
<div id="dreamworks_container">
    <!--Primary Navigation-->
    <nav id="primary_nav">
        <ul>
            <li id="tutDash" class="nav_dashboard active"><a href="index.php">Dashboard</a></li>
			<li id="tutDS" class="nav_typography"><a href="datasources.php<?=$_GET['tut'] ? '?tut=1' : ''?>">Datasources</a></li>
			<li class="nav_forms"><a href="backgrounds.php">Backgrounds</a></li>
            <li class="nav_graphs "><a href="doc_templates.php">Document Templates</a></li>    
            <li class="nav_uielements"><a href="merged.php">Merged Documents</a></li>
            <li class="nav_pages"><a href="account.php">Account</a></li>
        </ul>
    </nav>
<!--Main Content-->
<section id="main_content">
<!--Secondary Navigation-->
<nav id="secondary_nav"> 
<!--UserInfo-->
<dl class="user_info">
	<dt><a href="#"><img src="images/avatar.png" alt="" /></a></dt>
    <dd>
    <a class="welcome_user" href="#">Welcome, <strong><?=ucfirst($server->session->get_user_var('username'))?></strong></a>
    <span class="log_data">Last sign in : <?=gmdate("H:i, jS M Y",strtotime($server->outData['last_login']['created_at']) + $gmtOffset)?></span>
    <a class="logout" href="#">Logout</a>
    <!--<a class="user_messages" href="#"><span>12</span></a>-->
    </dd>
</dl>

<!--Responsive Nav-->
    <a class="res_icon" href="#"></a>
    <ul id="responsive_nav">
    	<li>
        	<a href="index.html">Dashboard</a>
        </li>
        <li>
        	<a href="charts.html">Graphs</a>
            <ul>
            	<li><a href="charts.html">Lines Chart</a></li>
                <li><a href="charts_bar.html">Bars Chart</a></li>
                <li><a href="charts_pie.html">Pie Chart</a></li>
            </ul>
        </li>
        <li>
        	<a href="forms.html">Forms</a>
            <ul>
            	<li><a href="forms.html">Form elements</a></li>
                <li><a href="editor_upload.html">WYSIWYG / Uploader</a></li>
            </ul>
        </li>
        <li>
       	 	<a href="typography.html">Typography</a>
            <ul>
            	<li><a href="typography.html">Typography</a></li>
                <li><a href="grid.html">Grid</a></li>
            </ul>            
        </li>
        <li>
        	<a href="ui_elements.html">UI Elements</a>
            <ul>
            	<li><a href="ui_elements.html">Miscellaneous</a></li>
                <li><a href="buttons_icons.html">Buttons & Icons</a></li>
                <li><a href="calendar.html">Calendar</a></li>
                <li><a href="data_table.html">Tables</a></li>
                <li><a href="modal_window.html">Modal Windows</a></li>
                <li><a href="gallery.html">Gallery</a></li>
            </ul>            
        </li>
        <li>
       		<a href="pages.html">pages</a>
            <ul>
            	<li><a href="offline.html">Site offline</a></li>
                <li><a href="404.html">404 page</a></li>
                <li><a href="405.html">405 page</a></li>
                <li><a href="500.html">500 page</a></li>
            </ul>              
        </li>
    </ul>
<!--Responsive Nav ends-->

<h2>Dashboard</h2>
<ul>
	<li><a href="#"><span class="iconsweet">a</span>The Dashboard gives you a quick overview of your account activity for the past billing month.</a></li> 
</ul>
</nav>
<!--Content Wrap-->
<div id="content_wrap">	<!--Activity Stats-->
    	<div id="activity_stats"> 
        	<h3>Activity</h3>
            <div class="activity_column">
            	<span class="iconsweet">Y</span> <span class="big_txt gr_txt"><?=$server->outData['totals']['document_count']?></span>Document Templates
            </div>
            <div class="activity_column">
            	<span class="iconsweet">Y</span> <span class="big_txt gr_txt"><?=$server->outData['totals']['background_count']?></span>Backgrounds
            </div>
            <div class="activity_column">
            	<span class="iconsweet">#</span> <span class="big_txt gr_txt"><?=$server->outData['totals']['datasource_count']?></span>Datasources
            </div>                         
        </div>        
	
		<?php foreach($server->outData['notices'] as $msgKey =>$msgData) { ?>
			<div class="msgbar <?=$msgData['type']?> hide_onC" rel=<?=$msgData['id']?>>
				<span class="iconsweet">*</span><p><b><?=$msgData['message']?></b></p>
			</div>
		<?php } ?>


	<!--One_Wrap-->
        <div class="one_wrap">
        <div class="widget">
                <div class="widget_title"><span class="iconsweet">f</span><h5>Steps to create your document.</h5></div>
	            <div class="widget_body">
       		        <!--Activity Table-->
			<center>
			<a href="datasources.php" class="css_btn_class">1. Upload Your Datasource</a>
			<a href="backgrounds.php" class="css_btn_class">2. Upload Your Page Background</a>
			<a href="doc_templates.php" class="css_btn_class">3. Design Your Document</a>
			</center>
		    </div>
		</div>
	</div>

				
	<!--One_TWO-->
 	<div class="one_two_wrap fl_left">
    	<div class="widget" style="display: none;">
        	<div class="widget_title"><span class="iconsweet">r</span><h5>Page Quota Used</h5></div>
            <div class="widget_body">
            	<div class="content_pad">
            	<!--Stacked Bar--> 
                <div id="stacked_bar" class="" style="width:100%;height:315px"></div>
                <!--<br/>
                <p class="stackControls text_center">
                <input class="greyishBtn button_small" type="button" value="With stacking">
                <input class="greyishBtn button_small" type="button" value="Without stacking">
                </p>-->
            	</div>
            </div>
        </div>
    </div>
	
	<!--One_TWO-->
 	<div class="one_two_wrap fl_right">
    	<div class="widget" style="display:none;">
        	<div class="widget_title"><span class="iconsweet">t</span><h5>Statistics</h5></div>
            <div class="widget_body">
            	<!--Stastics-->
            	<ul class="dw_summary">
							<?php
								//Terms are specified at the top of this php page for the script related to the graph.
								$termTotal		 = $term_end - $term_start;
								$termPosition	 = $term_end - time();
								$termPercentUsed = 100-($termPosition/$termTotal*100);
								$termPercentUsed = round($termPercentUsed, 1);
								$termPercentUsed = ($termPercentUsed > 100 ? 100 : $termPercentUsed);
								
								
								//Incase our user statistics billing cycle page count is 0, count the pages based off the generated documents data for our graph.
								if($server->outData['statistics']['billing_cycle_pages'] === 0 && sizeof($server->outData['billing_period_generated_documents']) > 0) {
									foreach($server->outData['billing_period_generated_documents'] as $key => $data) {
										$server->outData['statistics']['billing_cycle_pages'] += $data['pages'];
									}
								}
								
								//Incase our user statistics documents created count is 0, count the documents based off the generated documents data for our graph.
								if($server->outData['statistics']['billing_cycle_documents'] === 0 && sizeof($server->outData['billing_period_generated_documents']) > 0) {
									foreach($server->outData['billing_period_generated_documents'] as $key => $data) {
										$server->outData['statistics']['billing_cycle_documents']++;
									}
								}
								
								
								
								$monthlyQuotaUsed = $server->outData['statistics']['billing_cycle_pages'] / $server->outData['statistics']['plan_included_pages'] * 100;
								$monthlyQuotaUsed = ($monthlyQuotaUsed > 100 ? 100 : $monthlyQuotaUsed);
								
								$pagesUsed = $server->outData['statistics']['billing_cycle_pages'];
								$PageQuotaRemaining = $server->outData['statistics']['plan_included_pages'] - $pagesUsed;
								$PageQuotaRemaining = $PageQuotaRemaining < 0 ? 0 : $PageQuotaRemaining;
								$overQuota 		= $pagesUsed > $server->outData['statistics']['plan_included_pages'] ? $server->outData['statistics']['billing_cycle_pages'] - $server->outData['statistics']['plan_included_pages'] : 0;
								
								if($pagesUsed >= $server->outData['statistics']['plan_included_pages']) {
									$pagesUsed = $server->outData['statistics']['plan_included_pages'];
									$PageQuotaRemaining = 0;
								}
								
								
								
								
							?>
							<li>
								Last signed in from <span style="font-weight: bold;"><?=$server->outData['last_login']['ip']?></span> at <?=gmdate("H:i, jS M Y",strtotime($server->outData['last_login']['created_at']) + $gmtOffset)?>
                            </li>
							<li>
                                <span class="percentage_done"><?=$server->outData['statistics']['billing_cycle_logins']?></span> Number of logins this month
                            </li>
                            <li>
                                <span class="percentage_done"><?=$server->outData['statistics']['billing_cycle_documents']?></span> Documents created this month
                            </li>
							<li>
                                 <span class="percentage_done"><?=$termPercentUsed?>%</span> Through your <?=$inTrial == 1 ? "monthly period" : "billing cycle"?> (<?=$inTrial == 1 ? "Ends" : "Resets"?> <?=gmdate("H:i, jS M Y",$term_end + $gmtOffset)?>)<div class="progress_wrap"><div title="<?=$termPercentUsed?>%" class="tip_north progress_bar" style="width:<?=$termPercentUsed?>%"></div></div>
                            </li>
                            <li>
                                 <span class="percentage_done"><?=round($monthlyQuotaUsed, 1)?>%</span> Monthly page quota used <?=$monthlyQuotaUsed >= 85 ? "(<a href='account.php'>Upgrade my plan</a>)" : ""?><div class="progress_wrap"><div title="<?=round($monthlyQuotaUsed, 1)?>%" class="tip_north progress_bar" style="width:<?=round($monthlyQuotaUsed, 1)?>%"></div></div>
                            </li>
							<li>
                                 <span class="percentage_done"><?=$pagesUsed?></span> Pages from your monthly quota used (<?=$PageQuotaRemaining?> Remaining)
                            </li>
                            <li>
                                 <span class="percentage_done"><?=$overQuota ?></span> Extra pages used
                            </li>
                 </ul> 
            </div>
        </div>
    </div>   
	  
	<!--One_Wrap-->
 	<div class="one_wrap">
    	<div class="widget">
        	<div class="widget_title"><span class="iconsweet">f</span><h5>Your 6 most recently merged documents</h5></div>
            <div class="widget_body">
            	<!--Activity Table-->
            	<table class="activity_datatable" width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <th width="15%">Created At</th>
                        <th width="25%">Document Name</th>
                        <th width="25%">Datasource Name</th>
                        <th width="12%">Page Count</th>
                        <th width="23%">Actions</th>
                    </tr>
					
					<?php foreach($server->outData['recent_generated_documents'] as $genDocKey => $genDocData) { ?>
						<tr>
							<td><?=gmdate("H:i, jS M Y",strtotime($genDocData['created_at']) + $gmtOffset)?></td>
							<td><?=$genDocData['document_name']?></td>
							<td><?=$genDocData['datasource_name']?></td>
							<td><span class="stat_up"><?=$genDocData['pages']?> <span class="arrow_up iconsweet">]</span></span></td>
							<td>
								<center>						
									<a href="<?=$genDocData['file_path']?>" target="_blank"><button class="btn btn-success" >
										<i class="icon-trash icon-white"></i>
										<span>Download</span>
									</button></a>
									
									<button class="btn btn-danger deleteDocument" rel="<?=$genDocData['generated_document_id']?>">
										<i class="icon-trash icon-white"></i>
										<span>Delete</span>
									</button>							
								</center>
							</td>
						</tr>
					<?php } ?>      
                </table>
            </div>
        </div>
    </div>          
</div>
</section>
</div>

<?php if($_GET['tut']) { /*Google tracking cookie for new signups */
	
		//Update the tutorial progression indicator.
		$user_id	= $server->session->get_user_var('id');
		$query = "UPDATE users SET tutorial_position = 1 WHERE id = ? and 1 > tutorial_position";
		$stmt     = $server->sql->link->prepare($query);
		$stmt->bind_param('i', $user_id); 
		$stmt->execute();	
	?> 
<!-- Google Code for RMM - Register Free Account Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1008994454;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "Yjq8CIrArgUQlpGQ4QM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1008994454/?value=0&amp;label=Yjq8CIrArgUQlpGQ4QM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<?php } ?>
	
</body>
</html>

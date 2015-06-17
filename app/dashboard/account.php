<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	
	$server->fetchDashboardData();	
		
	//Used to offset our times, is set by a cookie in main.js
	$gmtOffset = 0;
	if(isset($_COOKIE['gmtoffset'])) 
		@$gmtOffset = (is_int((int)$_COOKIE['gmtoffset']) && (int)$_COOKIE['gmtoffset'] < 780 && (int)$_COOKIE['gmtoffset'] > -780 ? ($_COOKIE['gmtoffset'] * 60) : 0);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Rocket Mail Merge - Account Setup</title>
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
<script type="text/javascript" src="js/jquery.blockUI.js"> </script>
<script type="text/javascript" src="js/main.js"> </script>
<script type="text/javascript" src="js/account.js"> </script>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<!--Header-->
<header>
    <!--Logo-->
    <div id="logo"><h1>Rocket Mail Merge</h1></div>
    <!--Search-->
       
    <div class="header_search">
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
            <li class="nav_dashboard"><a href="index.php">Dashboard</a></li>
			<li class="nav_typography"><a href="datasources.php">Datasources</a></li>
			<li class="nav_forms"><a href="backgrounds.php">Backgrounds</a></li>
            <li class="nav_graphs "><a href="doc_templates.php">Document Templates</a></li>    
            <li class="nav_uielements"><a href="merged.php">Merged Documents</a></li>
            <li class="nav_pages active"><a href="account.php">Account</a></li>
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

<h2>Account Setup</h2>
<ul>
	<li><a href="#"><span class="iconsweet">a</span>From here you can manage your account. You can change your plan, update your payment details or cancel your account.</a></li> 
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
        	<div class="widget_title"><span class="iconsweet">f</span><h5>Your Account Setup</h5></div>
            <div class="widget_body">
            	<!--Activity Table-->
				<div class="content_pad">
					<div id="subscription" style="font-size: 16px;">
					  <h3>Your Subscription</h3>
					  <?php if($_GET['state'] == "succeeded") { ?>
					  <p>We've successfully updated your subscription details. If you've changed your subscription, please allow up to 30 seconds for the system to update your account. Have a great day!</p>
					  <p><a href='account.php?1'>Take me back to my account settings</a></p>
					  <!-- Google Code for RMM - Register paid account Conversion Page -->
						<script type="text/javascript">
						/* <![CDATA[ */
						var google_conversion_id = 1008994454;
						var google_conversion_language = "en";
						var google_conversion_format = "3";
						var google_conversion_color = "ffffff";
						var google_conversion_label = "QiecCPrBrgUQlpGQ4QM";
						var google_conversion_value = 15;
						/* ]]> */
						</script>
						<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
						</script>
						<noscript>
						<div style="display:inline;">
						<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1008994454/?value=15&amp;label=QiecCPrBrgUQlpGQ4QM&amp;guid=ON&amp;script=0"/>
						</div>
						</noscript>

					  <?php } else { 
						  
							$term_start = 0;
							$term_end   = 0;
							$inTrial    = 0;
							
							if(isset($server->outData['statistics']['subscription_current_term_start'])) {
								$term_start = $server->outData['statistics']['subscription_current_term_start'];
								$term_end   = $server->outData['statistics']['subscription_current_term_end'];
							} else if(isset($server->outData['statistics']['subscription_trial_start'])) {
								$term_start = $server->outData['statistics']['subscription_trial_start'];
								$term_end   = $server->outData['statistics']['subscription_trial_end'];
								$inTrial    = 1;
							} else {
								$term_start = time();
								$term_end   = time() + 2629746; //seconds in a month.
							}
			
					  ?>
					  <div id="subscriptionArea">
						  <p>
							<!--Your current plan is: <span style="font-weight: bold;"><?= str_replace("_", " ",ucfirst($server->outData['statistics']['subscription_plan_id'] == "free" ? "Free Account" : $server->outData['statistics']['subscription_plan_id'])) ?></span><br/>-->
							Your subscription status is: <span style="font-weight: bold; <?= ($server->outData['statistics']['subscription_status'] != "active" && $server->outData['statistics']['subscription_status'] != "in_trial" ? 'color: red;' : 'color: green;')?>"><?= str_replace("_", " ",ucfirst($server->outData['statistics']['subscription_status'])) ?></span><br/>
							Your <?=$inTrial ? "monthly" : "billing"?> period is: <span style="font-weight: bold;"><?= gmdate("H:i, jS M Y",($term_start + $gmtOffset)) . ' to ' . gmdate("H:i, jS M Y",($term_end + $gmtOffset)) ?></span>
						  <br><br>
							<h6 style="text-decoration: underline;">Help</h6>
							Need to ask us a question or get some help from the Rocket Mail Merge team? No problem, just send us an email to <a href="mailto:support@rocketmailmerge.com">support@rocketmailmerge.com</a>.<br/><br/><br/>
							
						  </p>
						  <table class="pricing" style="display: none;">
							<tbody>
							<tr style="font-weight:bold;">
								<?php if($server->outData['statistics']['subscription_plan_id'] == "free") { ?><th>Free Account</th><?php } ?>
								<th>Contractor</th>
								<th>Small Business</th>
								<th>Office</th>
								<th>Enterprise</th>
							</tr>
							<tr>
								<?php if($server->outData['statistics']['subscription_plan_id'] == "free") { ?><td>Limited to 30 pages</td><?php } ?>
								<td>100 pages included</td>
								<td>300 pages included</td>
								<td>1,500 pages included</td>
								<td>8,000 pages included</td>
							</tr>
							<tr>
								<?php if($server->outData['statistics']['subscription_plan_id'] == "free") { ?><td>Limited to 3 Backgrounds,<br/>3 Datasources &amp; 3 Templates</td><?php } ?>
								<td>Unlimited Backgrounds,<br/>Datasources &amp; Templates</td>
								<td>Unlimited Backgrounds,<br/>Datasources &amp; Templates</td>
								<td>Unlimited Backgrounds,<br/>Datasources &amp; Templates</td>
								<td>Unlimited Backgrounds,<br/>Datasources &amp; Templates</td>
							</tr>
							<tr>
								<?php if($server->outData['statistics']['subscription_plan_id'] == "free") { ?><td>No Extra Pages Allowed</td><?php } ?>
								<td>$0.10 per extra page</td>
								<td>$0.08 per extra page</td>
								<td>$0.05 per extra page</td>
								<td>$0.03 per extra page</td>
							</tr>
							<tr style="font-weight:bold;">
								<?php if($server->outData['statistics']['subscription_plan_id'] == "free") { ?><td><b>Free</b></td><?php } ?>
								<td><b>$12/month (USD)</b></td>
								<td><b>$29/month (USD)</b></td>
								<td><b>$99/month (USD)</b></td>
								<td><b>$299/month (USD)</b></td>
							</tr>
							<tr class="pay">
								<?php if($server->outData['totals']['document_count'] > 3 || $server->outData['totals']['background_count'] > 3 || $server->outData['totals']['datasource_count'] > 3) 
										 $disableFree = 1;
									  else 
									     $disableFree = 0;
										 
									if($server->outData['statistics']['subscription_status'] != "active" && $server->outData['statistics']['subscription_status'] != "in_trial") {
										$planWords = 'Reactivate my subscription';
										$planDisable = '';
									} else {
										$planWords = 'Current Plan';
										$planDisable = 'disabled';										
									}									
									
								?>
								<?php if($server->outData['statistics']['subscription_plan_id'] == "free") { ?><td><a href="#" rel='free'           class="planChange button_small <?= $disableFree ?  ' overage ' : ''?> <?= ($server->outData['statistics']['subscription_plan_id'] == "free") ? 'greenishBtn '.$planDisable.'"><span class="iconsweet">=</span>'.$planWords.'</a></td>' : 'whitishBtn "><span class="iconsweet">=</span>Select</a></td>'?><?php } ?>
								<td><a href="#" rel='contractor'     class="planChange button_small <?= ($server->outData['statistics']['subscription_plan_id'] == "contractor") ? 'greenishBtn '.$planDisable.'"><span class="iconsweet">=</span>'.$planWords.'</a></td>' : 'bluishBtn "><span class="iconsweet">=</span>Select</a></td>'?>
								<td><a href="#" rel='small_business' class="planChange button_small <?= ($server->outData['statistics']['subscription_plan_id'] == "small_business") ? 'greenishBtn '.$planDisable.'"><span class="iconsweet">=</span>'.$planWords.'</a></td>' : 'bluishBtn "><span class="iconsweet">=</span>Select</a></td>'?>
								<td><a href="#" rel='office'         class="planChange button_small <?= ($server->outData['statistics']['subscription_plan_id'] == "office") ? 'greenishBtn '.$planDisable.'"><span class="iconsweet">=</span>'.$planWords.'</a></td>' : 'bluishBtn "><span class="iconsweet">=</span>Select</a></td>'?>
								<td><a href="#" rel='enterprise'     class="planChange button_small <?= ($server->outData['statistics']['subscription_plan_id'] == "enterprise") ? 'greenishBtn '.$planDisable.'"><span class="iconsweet">=</span>'.$planWords.'</a></td>' : 'bluishBtn "><span class="iconsweet">=</span>Select</a></td>'?>
							</tr>
							</tbody>
						  </table>
						
						
						<?php   #Since only subscribed user's care about their creditcard and cancelling their subscription, don't show it to free accounts to avoid confusion.
								if($server->outData['statistics']['subscription_plan_id'] != "free") {  ?>
						<hr>
						<br/>
						<div style="font-size: 16px;"> 
						  <h3>Your credit card details</h3>
						  <?php   #For free accounts, let them enter a creditcard so they can use extra pages.
								if($server->outData['statistics']['subscription_plan_id'] == "free") { 
									if(isset($server->outData['statistics']['card_status'])) { 
										echo "<p>You're on our free account. You will only be charged for extra pages you use above your free quota.</p>";
									} else {
										echo "<p>You're on our free account. If you'd like to use extra pages please enter your credit card details.</p>";
									}
						  } ?>
						  
						  <p>
						  <?php if(isset($server->outData['statistics']['card_status'])) { ?>
							<table width="330">
								<tbody>
									<tr>
										<td>Credit card Status :</td>
										<td><span style="font-weight: bold;"><?= ucfirst($server->outData['statistics']['card_status']) ?></td>
									</tr>
									<tr>
										<td>Credit card Name :</td>
										<td><span style="font-weight: bold;"><?= $server->outData['statistics']['card_first_name']. ' '. $server->outData['statistics']['card_last_name'] ?></td>
									</tr>
									<tr>
										<td>Credit card Number :</td>
										<td><span style="font-weight: bold;"><?= $server->outData['statistics']['card_masked_number'] ?></td>
									</tr>
									<tr>
										<td>Credit card Expiry :</td>
										<td><span style="font-weight: bold;"><?= $server->outData['statistics']['card_expiry_month'].' '.$server->outData['statistics']['card_expiry_year'] ?></td>
									</tr>
								</tbody>
							</table>
							<br/>
							<a href="#" class="button_small bluishBtn cardUpdate"><span class="iconsweet">=</span>Update Card</a>		
							<?php } else { ?>
							<p>We don't have a card on file for you. Would you like to add one?</p>
							<a href="#" class="button_small bluishBtn cardUpdate"><span class="iconsweet">=</span>Add A Credit Card</a>
							<?php } ?>
						  </p>
						</div>
						<?php } ?>
						<?php   #Since only subscribed user's care about their creditcard and cancelling their subscription, don't show it to free accounts to avoid confusion.
								if($server->outData['statistics']['subscription_plan_id'] != "free") {  ?>
						<hr>
						<br/>
						<?php if($server->outData['statistics']['subscription_status'] != "non_renewing" && $server->outData['statistics']['subscription_status'] != "cancelled") { ?>
						<div id="subscription" style="font-size: 16px;">
						  <h3 style="color: red;">Cancel your subscription</h3>
						  <p>To cancel your subscription at the end of your current billing period and mark your account as closed you can regretfully click the button below. We'll miss you :(</p>
						  <a href="#" class="button_small redishBtn cancelAccount"><span class="iconsweet">x</span>Cancel</a>
						</div>
						<?php } else { ?> 
						<div id="subscription" style="font-size: 16px;">
						  <h3 style="color: green;">Reactivate your subscription</h3>
						  <p>To reactivate your cancelled subscription under your current plan, click the button below. Alternatively you can choose to subscribe to a new plan from above.</p>
						  <a href="#" class="button_small greenishBtn reactivateAccount"><span class="iconsweet">=</span>Reactivate My Subscription</a>
						</div>
						
						
						<?php } } ?>
						
					</div>
					<?php } ?>
					</div>
				</div>
			</div>
        </div>
    </div>          
</div>
</section>
</div>

<?php if(isset($_GET['redirect'])) { ?>
	<script>
		alert("Sorry, you don't have an active subscription. Please choose a subscription plan to reactivate your account.");
	</script>
<?php } ?>
</body>
</html>

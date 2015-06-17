<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	
	$server->fetchDashboardData();
	$server->fetchMergedDocumentsData();	
	
	//echo "<pre>";
	//die(print_r($server->outData));
	
	//Used to offset our times, is set by a cookie in main.js
	$gmtOffset = 0;
	if(isset($_COOKIE['gmtoffset'])) 
		@$gmtOffset = (is_int((int)$_COOKIE['gmtoffset']) && (int)$_COOKIE['gmtoffset'] < 780 && (int)$_COOKIE['gmtoffset'] > -780 ? ($_COOKIE['gmtoffset'] * 60) : 0);
		
	if($server->outData['statistics']['subscription_status'] == "cancelled") {
		header( 'Location: account.php?redirect=1' ) ;
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Rocket Mail Merge - Merged Documents</title>
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
<script type="text/javascript" src="js/main.js"> </script>

<script type="text/javascript" src="js/merged.js"> </script>

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
            <li class="nav_uielements active"><a href="merged.php">Merged Documents</a></li>
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

<h2>Merged Documents</h2>
<ul>
	<li><a href="#"><span class="iconsweet">a</span>This page shows you all your merged documents. They are the final combination of a document template and an optional datasource.</a></li> 
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
		
	<!--One_Wrap-->
 	<div class="one_wrap">
    	<div class="widget">
        	<div class="widget_title"><span class="iconsweet">f</span><h5>Your merged documents</h5></div>
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
							<td><span class="stat_up"><?=$genDocData['pages']?> <?=$genDocData['pages'] > 0 ? '<span class="arrow_up iconsweet">]</span>' : ''?></span></td>
							
							<td>
								<center>						
									<a href="<?=$genDocData['file_path']?>" target="_blank"><button class="btn btn-success" >
										<i class="icon-trash icon-white"></i>
										<span>Download</span>
									</button></a>
									
									<button class="btn btn-danger deleteDocument" rel="<?=$genDocData['document_id']?>">
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

</body>
</html>
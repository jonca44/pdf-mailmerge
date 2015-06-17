<?php
 	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$start = $time;

	error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	require_once('../datasources/parsecsv/parsecsv.lib.php');
	
	$server->fetchDashboardData();	
	
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
<title>Rocket Mail Merge - Datasources</title>
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
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
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
<!--<script type="text/javascript" src="js/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="js/jquery.ui.progressbar.js"></script>
<script type="text/javascript" src="js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="js/jquery.ui.tabs.js"></script>-->
<script type="text/javascript" src="js/fullcalendar.js"></script>
<script type="text/javascript" src="js/gcal.js"></script>
<script type="text/javascript" src="js/bootstrap-modal.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="js/highlight.js"></script>
<script type="text/javascript" src="js/jq_tables/jquery.dataTables.js"></script>
<script src="../datasources/js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/guiders-1.3.0.js"></script>


<!-- Shim to make HTML5 elements usable in older Internet Explorer versions -->
<!--[if lt IE 9]><script src="js/html5.js"></script><![endif]-->
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="js/load-image.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="js/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS and Bootstrap Image Gallery are not required, but included for the demo -->
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-image-gallery.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="../datasources/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="../datasources/js/jquery.fileupload.js"></script>
<!-- The File Upload file processing plugin -->
<script src="../datasources/js/jquery.fileupload-fp.js"></script>
<!-- The File Upload user interface plugin -->
<script src="../datasources/js/jquery.fileupload-ui.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="../backgrounds/js/cors/jquery.xdr-transport.js"></script><![endif]-->

<!--<script type="text/javascript" src="js/main.js"> </script>-->
<script type="text/javascript" src="../datasources/js/main.js"> </script>
<script type="text/javascript" src="../datasources/js-unique/main2.js"> </script>

<script type="text/javascript" src="js/apprise-1.5.full.js"></script>
<link rel="stylesheet" href="css/apprise.css" type="text/css" />


<!-- Bootstrap Image Gallery styles -->
<link rel="stylesheet" href="css/bootstrap-image-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="../datasources/css/jquery.fileupload-ui.css">


  <!-- Hanson Table code -->
  <!--<script src="js/jquery.handsontable.full.js"></script>  -->
  <script src="../datasources/parsecsv/lib/bootstrap-typeahead.js"></script>
  <script src="../datasources/parsecsv/lib/jQuery-contextMenu/jquery.contextMenu.js"></script>
  <script src="../datasources/parsecsv/lib/jQuery-contextMenu/jquery.ui.position.js"></script>
  <link rel="stylesheet" media="screen" href="../datasources/parsecsv/lib/jQuery-contextMenu/jquery.contextMenu.css">
  <!--<link rel="stylesheet" media="screen" href="../datasources/parsecsv/jquery.handsontable.css">-->
  
  <script src="js/handson2/jquery.handsontable.full.js"></script>
  <link rel="stylesheet" media="screen" href="js/handson2/jquery.handsontable.full.css">
 
  <!--
  Loading demo dependencies. They are used here only to enhance the examples on this page
  -->
  <script src="js/highlight.pack.js"></script>
  <link rel="stylesheet" media="screen" href="css/github.css">
  
 <script type="text/javascript" src="js/jquery.blockUI.js"></script>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php if($server->outData['statistics']['subscription_plan_id'] == "free" && $server->outData['totals']['datasource_count'] >= 3) { ?>
<script>
	$(document).ready(function() {
		$("input[type='file'],.create-datasource").unbind().click( function(e) {
			e.preventDefault();
			var answer = confirm("Sorry, free accounts are limited to 3 datasources maximum. Would you like to upgrade your account now?");
			
			if(answer)
				window.location = "account.php";
				
			return;		
		});
		
	});
</script>
<?php } ?>
<script>
	$(document).ready(function() {
		$(".logout").unbind().click( function(e) {

			var me = $(this);
			
			var answer = confirm("Are you sure you want to logout?");
			
			if(answer) {
				$.post("/php/dataServer.php", { "rex": "11", "out" : 1 },
					 function(data){
						if(data.success == 1)
							window.location = "https://app.rocketmailmerge.com/account/login.html";
						else {
							alert("Unable to connect to the server. Please try again later.");
						}
					 }, "json");
			}
		});
	});
</script>
<script>
	<?php if($_GET['tut']) { 
	
		//Update the tutorial progression indicator.
		$user_id	= $server->session->get_user_var('id');
		$query = "UPDATE users SET tutorial_position = 2 WHERE id = ? and 2 > tutorial_position";
		$stmt     = $server->sql->link->prepare($query);
		$stmt->bind_param('i', $user_id); 
		$stmt->execute();	
	}
	?> 
	$(document).ready(function() {
				
		guiders.createGuider({
		  attachTo: "#headers",
		  buttons: [{name: "Next"}],					
		  description: "<img style='float: left;' src='images/tutorial/dog2.png'/><div style='padding-top: 40px; padding-bottom:30px;'>This is a datasource. I've uploaded a default datasource for you with some sample data, you can also upload your own. Your datasources hold your data that you want to merge into your documents, like names and addresses.</div>",
		  id: "first",
		  next: "second",
		  position: "bottom",
		  title: "This is a Datasource",
		  width: 500,
		  offset: { left: 100, top: 30 }
		})<?=$_GET['tut'] ? '.show();' : ';'?> 
		
		guiders.createGuider({
		  attachTo: "#actions",
		  buttons: [{name: "Next"}],	
		  description: "You are able to edit your datasources directly through your web browser too! <br/>&nbsp;",
		  id: "second",
		  next: "third",
		  offset: { left: +70, top: +100 },
		  position: "left",
		  title: "Datasource Actions",
		  width: 300
		});
		
		guiders.createGuider({
		  attachTo: "#help2",
		  buttons: [{name: "Next"}],
		  description: "At the bottom of the Datasources, Backgrounds and Document Templates pages you'll find some helpful information which explains how those sections work. <br/>&nbsp;",
		  id: "third",
		  next: "fourth",
		  position: "top",
		  title: "Help and Guidance",
		  width: 500
		});
		
		guiders.createGuider({
		  attachTo: "#backgrounds",
		  buttons: [{name: "Close, then click on backgrounds.", onclick: guiders.hideAll}],
		  description: "Nice work, you're doing well so far! Once you're done reading the help area click the backgrounds menu link to continue.<br/>&nbsp;",
		  id: "fourth",
		  position: "right",
		  title: "Backgrounds",
		  width: 400
		});
		
	});
</script>
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
			<li class="nav_typography active"><a href="datasources.php">Datasources</a></li>
			<li id="backgrounds" class="nav_forms"><a href="backgrounds.php<?=$_GET['tut'] ? '?tut=1' : ''?>">Backgrounds</a></li>
            <li class="nav_graphs "><a href="doc_templates.php">Document Templates</a></li>    
            <li class="nav_uielements"><a href="merged.php">Merged Documents</a></li>
            <li class="nav_pages"><a href="account.php">Account</a></li>
			<li class="nav_pages"><a href="#help">Help</a></li>
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

<h2>Datasources</h2>
<ul>
	<li><a href="#"><span class="iconsweet">a</span>A datasource controls the data that is merged into a document template. Each line in a datasource represents a set of data variables (Typically a customer) while each column indicates the variables associated with that set. Variables are references in the document template by their header row colored green in the datasource editor.</a></li> 
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
		
	<form id="fileupload" action="../datasources/server/" method="POST" enctype="multipart/form-data">
		<!--Quick Actions-->
		<div id="quick_actions" class="fileupload-buttonbar">
			<a class="button_big create-datasource" href="#"><span class="iconsweet">+ </span>Create a new Datasource</a>
			<a class="button_big fileinput-button" href="#"><span class="iconsweet">+ <input type="file" name="files[]" multiple></span>Upload a Datasource</a>
			<a class="button_big btn_grey delete" href="#"><span class="iconsweet">f</span>Delete Selected</a>
		</div>
		<!-- The global progress information -->
		<div class="fileupload-progress hide fade">
			<!-- The global progress bar -->
			<div style="width: 100%;" class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
				<div class="bar" style="width:0%;"></div>
			</div>
			<!-- The extended global progress information -->
			<div class="progress-extended">&nbsp;</div>
		</div>
		
		<!--One_Wrap-->
		<div class="one_wrap csvEditorWrapper hide">
			<div class="widget">
				<div class="widget_title"><span class="iconsweet">f</span><h5>Data Editor</h5></div>
				<div class="widget_body" >
					<div class="rowLayout">
						<div class="descLayout">
						  <div class="pad bottomSpace650">

							<div id="example1" style="height: 100%; overflow: auto; min-height:500px"></div>

						  </div>
						</div>
					</div>
				</div>
				<hr/>
				<p style="padding-bottom:10px; padding-left: 5px;">								
					<button name="save-csv" class="btn btn-primary">
						<i class="icon-trash icon-white"></i>
						<span> Save </span>
					</button>
					
					<button name="save-close-csv" class="btn btn-primary">
						<i class="icon-trash icon-white"></i>
						<span> Save &amp; Close </span>
					</button>
					
					<button name="cancel-csv" class="btn btn-warning">
						<i class="icon-trash icon-white"></i>
						<span> Cancel Editing </span>
					</button>
					
					<span style="margin-left: 10px; font-weight: bold;" id="statusText"></span>
					
				</p>
			</div>
		</div>
		
		<!--One_Wrap-->
		<div class="one_wrap">
			<div class="widget">
				<div class="widget_title"><span class="iconsweet">f</span><h5>Your Document Datasources</h5></div>
				<div class="widget_body">
					<div class="fileupload-loading"></div>
					<!--Activity Table-->
					<table role="presentation" class="activity_datatable" width="100%" border="0" cellspacing="0" cellpadding="8">
						<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
							<tr>
								<th width="2%"><center class="fileupload-buttonbar"><input type="checkbox" class="toggle"></center></th>
								<th width="5%">Data Line Count</th>	
								<th width="27%">File Name</th>															
								<th id="headers" width="40%">Header Variable Fields</th>
								<th width="12%">File Size</th>
								<th id="actions" width="15%">Actions</th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div> 
		
		<a id="help"></a>
		<div class="one_wrap">
			<div class="widget">
				<div class="widget_title"><span class="iconsweet">8</span>
					<h5 id="help2">Help</h5>
				</div>
				<div class="widget_body">
					<div class="content_pad">
						<h4>About datasources</h4>
						<p>A datasource controls the data that is merged into a document template. Each line in a datasource represents a set of data variables (Typically a customer) while each column indicates the variables associated with that set. Variables are references in the document template by their header row colored green in the datasource editor.</p>
						<br/>
						<h4>Datasource Variables</h4>
						<p>The first row of a datasource must contain its variable reference (Colored in green). These references are what you use to place a variable in the document template editor. For example if a column had a header of 'Name' we could reference it in our editor by placing &lt;Name&gt; anywhere in that document.</p>
						<br/>
						<h4>Datasource Editor - Shortcuts</h4>
						<p>The datasource editor has an easy to use menu if you right click your mouse within it. You can also use common keyboard shortcuts like CTRL+X (Cut), CTRL+C (Copy), CTRL+V (Paste) and CTRL+Z (Undo), CTRL+R (Redo)</p>
						<br/>
						<h4>Uploading - Acceptable file types</h4>
						<p>As a datasource needs a structure in order for our system to import it, we require your data be provided as a CSV (Comma seperated values) file. A CSV can be generated from Excel by clicking File->Save As-> and choosing .csv from the list of file types.</p>
						<br/>
						<h4>Uploading - Copy and Paste</h4>
						<p>Data from Mictorost Excel can also be pasted directly into the datasource editor. Just select the rows and columns you wish to copy in Excel, press CTRL+C on your keyboard, and in the datasource editor press CTRL+P</p>
						<br/>
						<h4>Acceptable data</h4>
						<p>Your data should be consistant. The first row must contain your variable headers. It is considered an error to have more columns of data in a row then you have headers for. For large data you can work on your data in a spreadsheet program like Excel and then upload it to Rocket Mail Merge once it's completed. You can also create your data directly in Rocket Mail Merge by clicking on 'Create a new datasource'.</p>
					</div>
				</div>
			</div>
		</div>

	</form>
</div>
</section>
</div>

<!-- modal-gallery is the modal dialog used for the image gallery -->
<div id="modal-gallery" class="modal modal-gallery hide fade" data-filter=":odd" tabindex="-1">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3 class="modal-title"></h3>
    </div>
    <div class="modal-body"><div class="modal-image"></div></div>
    <div class="modal-footer">
        <a class="btn modal-download" target="_blank">
            <i class="icon-download"></i>
            <span>Download</span>
        </a>
        <a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000">
            <i class="icon-play icon-white"></i>
            <span>Slideshow</span>
        </a>
        <a class="btn btn-info modal-prev">
            <i class="icon-arrow-left icon-white"></i>
            <span>Previous</span>
        </a>
        <a class="btn btn-primary modal-next">
            <span>Next</span>
            <i class="icon-arrow-right icon-white"></i>
        </a>
    </div>
</div>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
		<td></td>
        <td class="linecount"></td>
        <td class="name"><span>{%=file.name%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
			<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="actions">
				<span class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>Start</span>
                </button>
            {% } %}</span>
        {% } else { %}
        {% } %}
		
		{% if (!o.files.valid && !i) { %}
			<td class="actions">
		{% } %}
		
        <span class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>Cancel</span>
            </button>
        {% } %}</span></td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td class="delete"><input type="checkbox" name="delete" value="1"></td>
			<td class="linecount">{%=file.lines%}</td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="error"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else { %}
			<td class="delete"><input type="checkbox" name="delete" value="1"></td>
            <td class="linecount">{%=file.lines%}</td>
            <td class="name"><a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a></td>
        {% } %}

		{% if (!file.error) { %}
		<td class="headerfields">{%=file.headers%}</td>	
		{% } %}
		
		<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>		
		<td class="actions"><center>		
			{% if (!file.error) { %}
				<span class="edit">
					<button class="btn btn-success" filename="{%=file.name%}" subdir="{%=file.subdir%}">
						<i class="icon-trash icon-white"></i>
						<span> Edit </span>
					</button>
				</span>
			{% } %}
			<span class="delete">
				<button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
					<i class="icon-trash icon-white"></i>
					<span>Delete</span>
				</button>
			</span>
			</center>
		</td>
    </tr>
	
{% } %}
</script>

</body>
</html>

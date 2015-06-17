<?php
 	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$start = $time;

	error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	
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
<title>Rocket Mail Merge - Backgrounds</title>
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
<script src="../backgrounds/js/vendor/jquery.ui.widget.js"></script>
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
<script src="../backgrounds/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="../backgrounds/js/jquery.fileupload.js"></script>
<!-- The File Upload file processing plugin -->
<script src="../backgrounds/js/jquery.fileupload-fp.js"></script>
<!-- The File Upload user interface plugin -->
<script src="../backgrounds/js/jquery.fileupload-ui.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="../backgrounds/js/cors/jquery.xdr-transport.js"></script><![endif]-->

<script type="text/javascript" src="js/main.js"> </script>
<script type="text/javascript" src="../backgrounds/js/main.js"> </script>

<!-- Bootstrap Image Gallery styles -->
<link rel="stylesheet" href="css/bootstrap-image-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="../backgrounds/css/jquery.fileupload-ui.css">

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php if($server->outData['statistics']['subscription_plan_id'] == "free" && $server->outData['totals']['background_count'] >= 3) { ?>
<script>
	$(document).ready(function() {
		$("input[type='file']").unbind().click( function(e) {
			e.preventDefault();
			var answer = confirm("Sorry, free accounts are limited to 3 backgrounds maximum. Would you like to upgrade your account now?");
			
			if(answer)
				window.location = "account.php";
				
			return;		
		});
	});
</script>
<?php } ?>
<script>
	<?php if($_GET['tut']) {
	
		//Update the tutorial progression indicator.
		$user_id	= $server->session->get_user_var('id');
		$query = "UPDATE users SET tutorial_position = 3 WHERE id = ? and 3 > tutorial_position";
		$stmt     = $server->sql->link->prepare($query);
		$stmt->bind_param('i', $user_id); 
		$stmt->execute();	
	}
	?> 
	$(document).ready(function() {
				
		guiders.createGuider({
		  attachTo: "#headers",
		  buttons: [{name: "Next"}],					
		  description: "These are your document backgrounds. They become a template for you to merge your data on top of. I've created a sample envelope and invoice for you to take a look at. You can click on the thumbnails to take a closer look. <br/>&nbsp;",
		  id: "first",
		  next: "second",
		  position: "bottom",
		  title: "Your page backgrounds",
		  width: 500,
		  offset: { left: 0, top: 150 }
		})<?=$_GET['tut'] ? '.show();' : ';'?> 
		
		guiders.createGuider({
		  attachTo: "#docTemplates",
		  buttons: [{name: "Close, then click on doc templates.", onclick: guiders.hideAll}],
		  description: "In the next step we'll have a look at the document templates. Click on the link to continue when you've had a play around on this page. <br/>&nbsp;",
		  id: "second",
		  next: "third",
		  position: "right",
		  title: "Document Templates",
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
			<li class="nav_typography"><a href="datasources.php">Datasources</a></li>
			<li class="nav_forms active"><a href="backgrounds.php">Backgrounds</a></li>
            <li id="docTemplates" class="nav_graphs"><a href="doc_templates.php<?=$_GET['tut'] ? '?tut=1' : ''?>">Document Templates</a></li>    
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

<h2>Backgrounds</h2>
<ul>
	<li><a href="#"><span class="iconsweet">a</span>A page background is an image that will be used as the background of a page in the document you're building. It can be either included in the final document or used just as a scaffolding to position text in your document. The choice is up to you.</a></li> 
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
		
	<form id="fileupload" action="../backgrounds/server/" method="POST" enctype="multipart/form-data">
		<!--Quick Actions-->
		<div id="quick_actions" class="fileupload-buttonbar">
			<a class="button_big fileinput-button" href="#"><span class="iconsweet">+ <input type="file" name="files[]" multiple></span>Upload a Background</a>
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
		<div class="one_wrap">
			<div class="widget">
				<div class="widget_title"><span class="iconsweet">f</span><h5>Your Document Backgrounds</h5></div>
				<div class="widget_body">
					<div class="fileupload-loading"></div>
					<!--Activity Table-->
					<table role="presentation" class="activity_datatable" width="100%" border="0" cellspacing="0" cellpadding="8">
						<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
							<tr>
								<th width="2%"><center class="fileupload-buttonbar"><input type="checkbox" class="toggle"></center></th>
								<th id="headers" width="5%">Preview</th>
								<th width="27%">File Name</th>								
								<th width="50%">Sub Pages</th>
								<th width="12%">File Size</th>
								<th width="5%">Actions</th>
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
                        <h5>Help</h5>
                    </div>
                    <div class="widget_body">
                        <div class="content_pad">
							<h4>About backgrounds</h4>
							<p>A background is a static image that you can use as the background image of a document page in the document editor. The idea is for you to place your text on top of your background image. It can be either included in the final document or used just as a scaffolding to position text in your document. The choice is up to you. A page in the document editor can only have 1 background image, it will be stretched horizontally to fit the page.</p>
							<br/>
                        	<h4>Acceptable file types</h4>
							<p>Acceptable background images can be either of the following file types : .jpg, .jpeg, .png, .doc, .docx or .pdf</p>
							<br/>
                            <h4>Your background's dimensions</h4>
                            <p>Your page background will be stretched horizontally to fit the page size you select in the document builder. A background should ideally be in the same aspect ratio as the page size you intend to use otherwise it will either not fill your page or be cropped at the bottom. For example, a standard sized A4 page measures 210 x 297 millimeters or 8.3 x 11.7 inches. To figure out our background dimensions in pixels we can choose a DPI and calculate the size of the background we should upload.
							<br/>The higher the DPI of your background image the better quality it will print. We recommend 150 DPI or higher for the best print results or 72 DPI for computer only use.</p>
							<ul class="arrow">
                            	<li>A4 @ 72 DPI - 595 x 824 pixels</li>
                                <li>A4 @ 96 DPI - 794 x 1123 pixels</li>
								<li>A4 @ 150 DPI - 1240 x 1754 pixels</li>
                                <li>A4 @ 300 DPI - 2480 x 3508 pixels</li>
								<li>A4 @ 600 DPI - 4960 x 7016 pixels</li>
                            </ul>
							<br/>
							<h4>Using a PDF or Word Document as a background</h4>
							<p>You can use a PDF (.pdf) or Word Document (.doc or .docx) as your source of page backgrounds. Simply upload one of those two file types and the system will convert all the internal sub-pages into useable background images. For best results you should ensure your document's dimensions match your intended page size. Please remember backgrounds are static images that you build your mail merge text on top of. None of the text from your PDF or Word document will be editable.</p> 
                        </div>
                    </div>
                </div>
            </div>

	</form>
</div>
</section>
</div>

<!-- modal-gallery is the modal dialog used for the image gallery -->
<div id="modal-gallery" class="modal modal-gallery hide fade" tabindex="-1">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3 class="modal-title"></h3>
    </div>
    <div class="modal-body"><div class="modal-image"></div></div>
    <div class="modal-footer">
        <a class="btn btn-primary modal-next">Next <i class="icon-arrow-right icon-white"></i></a>
        <a class="btn btn-info modal-prev"><i class="icon-arrow-left icon-white"></i> Previous</a>
        <!--<a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000"><i class="icon-play icon-white"></i> Slideshow</a>-->
        <!--<a class="btn modal-download" target="_blank"><i class="icon-download"></i> Download</a>-->
    </div>
</div>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
		<td></td>
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
			<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="actions"><center>
				<span class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>Start</span>
                </button>
            {% } %}</span>
        {% } else { %}
        {% } %}
		
		{% if (!o.files.valid && !i) { %}
			<td class="actions"><center>
		{% } %}
		
        <span class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>Cancel</span>
            </button>
        {% } %}</span></center></td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td class="delete"><input type="checkbox" name="delete" value="1"></td>
			<td class="preview"></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="error"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else { %}
			<td class="delete"><input type="checkbox" name="delete" value="1"></td>
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.preview_url%}" title="{%=file.name%}" data-gallery="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
			</td>
        {% } %}
            <td class="name">
                <a href="{%=file.url%}" target="_blank" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
        {% } %}

		{% if (file.pdf_pages > 0) { %}
			<td class="preview">
				{% for (var pdf_i=1; pdf_i <= file.pdf_pages; pdf_i++) { %}
					<a href="{%=file.preview_folder%}{%=pdf_i%}.png" title="{%=file.name%} Page {%=pdf_i%}" data-gallery="gallery" download="{%=pdf_i%}.png"><img src="{%=file.thumbnail_folder%}{%=pdf_i%}.png"></a>
				{% } %}		
			</td>			
		{% } else if (!file.error) { %}
			<td></td>		
		{% } %}
		
		<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>		
		<td class="actions">
			<span class="delete">
            <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
                <i class="icon-trash icon-white"></i>
                <span>Delete</span>
            </button>
			</span>
		</td>
    </tr>
	
{% } %}
</script>

</body>
</html>

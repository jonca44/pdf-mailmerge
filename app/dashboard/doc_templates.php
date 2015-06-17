<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	
	$server->fetchDashboardData();
	$server->fetchDocumentsTemplates();	
	
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
<title>Rocket Mail Merge - Document Templates</title>
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
<script type="text/javascript" src="js/guiders-1.3.0.js"></script>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
	<link href="../editor/css/redmond/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<script src="../editor/js/jquery-ui-1.9.2.custom.min.js"></script>

<script type="text/javascript" src="js/doc_templates.js"> </script>

<script type="text/javascript" src="js/apprise-1.5.full.js"></script>
<link rel="stylesheet" href="css/apprise.css" type="text/css" />

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php if($server->outData['statistics']['subscription_plan_id'] == "free" && $server->outData['totals']['document_count'] >= 3) { ?>
<script>
	$(document).ready(function() {
		$("#createNewTemplate").unbind().click( function(e) {
			e.preventDefault();
			var answer = confirm("Sorry, free accounts are limited to 3 templates maximum. Would you like to upgrade your account now?");
			
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
		$query = "UPDATE users SET tutorial_position = 4 WHERE id = ? and 4 > tutorial_position";
		$stmt     = $server->sql->link->prepare($query);
		$stmt->bind_param('i', $user_id); 
		$stmt->execute();	
	}
	?> 
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
		
		guiders.createGuider({
		  attachTo: "#headers",
		  buttons: [{name: "Next"}],					
		  description: "You're doing great so far! These are your document templates. I've prepared up a simple example of some invoices for you to take a look at. <br/>&nbsp;",
		  id: "first",
		  next: "second",
		  position: "bottom",
		  title: "Document Templates",
		  width: 500,
		  offset: { left: 0, top: 50 }
		})<?=$_GET['tut'] ? '.show();' : ';'?> 
		
		guiders.createGuider({
		  attachTo: "#actions",
		  buttons: [{name: "Close, then click on edit.", onclick: guiders.hideAll}],
		  description: "You can edit your document templates by clicking on the edit button, let's go ahead and do that now. <br/>&nbsp;",
		  id: "second",
		  position: "left",
		  title: "Document Template Actions",
		  offset: { left: +230, top: +80 },
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
			<li class="nav_forms"><a href="backgrounds.php">Backgrounds</a></li>
            <li class="nav_graphs active"><a href="doc_templates.php">Document Templates</a></li>    
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
    <span class="log_data">Last sign in : <?=gmdate("H:i, jS M Y",strtotime($server->outData['last_login']['created_at']) + $gmtOffset )?></span>
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

<h2>Doc. Templates</h2>
<ul>
	<li><a href="#"><span class="iconsweet">a</span>A document template is the building blocks of your merged document. You can edit a document template to create the layout of your final document. You can change the page background, create static text that doesn't change &amp; position variables from your datasource.</a></li> 
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
	
	<!--Quick Actions-->
		<div id="quick_actions" class="fileupload-buttonbar">
			<a id="createNewTemplate" class="button_big delete" href="#"><span class="iconsweet">f</span>Create a New Template</a>
		</div>
		
	<!--One_Wrap-->
 	<div class="one_wrap">
    	<div class="widget">
        	<div class="widget_title"><span class="iconsweet">f</span><h5>Your document templates</h5></div>
            <div class="widget_body">
            	<!--Activity Table-->
            	<table class="activity_datatable" width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <th width="15%">Created At</th>
                        <th width="22%">Document Name</th>
                        <th id="headers" width="22%">Datasource Name</th>
                        <th width="8%">Datasource Line Count</th>
                        <th id="actions" width="34%">Actions</th>
                    </tr>
					
					<?php foreach($server->outData['document_templates'] as $genDocKey => $genDocData) { ?>
						<tr document_id="<?=$genDocData['document_id']?>">
							<td><?=gmdate("H:i, jS M Y",strtotime($genDocData['created_at']) + $gmtOffset )?></td>
							<td><?=$genDocData['document_name']?></td>
							<td><?=$genDocData['datasource_name']?></td>
							<td><span class="stat_up"><?=isset($genDocData['datasource_name']) ? $genDocData['datasource_lines'] : ''?> <?=$genDocData['datasource_lines'] > 0 ? '<span class="arrow_up iconsweet">]</span>' : ''?></span></td>
							
							<td>
								<center>						
									<a href="/editor/?document_id=<?=$genDocData['document_id']?><?=$_GET['tut'] ? '&tut=1' : ''?>" target="_blank"><button class="btn btn-success" >
										<i class="icon-trash icon-white"></i>
										<span>Edit</span>
									</button></a>
									
									<!--<button class="btn btn-success" rel="<?=$genDocData['document_id']?>">
										<i class="icon-trash icon-white"></i>
										<span>Rename</span>
									</button>-->
									
									<button class="btn btn-info mergeTemplate" rel="<?=$genDocData['document_id']?>">
										<i class="icon-trash icon-white"></i>
										<span>Prepare Merge</span>
									</button>
									
									<button class="btn btn-danger deleteDocumentTemplate" rel="<?=$genDocData['document_id']?>">
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
	
	<a id="help"></a>
	<div class="one_wrap">
		<div class="widget">
			<div class="widget_title"><span class="iconsweet">8</span>
				<h5>Help</h5>
			</div>
			<div class="widget_body">
				<div class="content_pad">
					<h4>About document templates</h4>
					<p>A document template is the building blocks of your merged document. You can edit a document template to create the layout of your final document. You can change the page background, create static text that doesn't change &amp; position variables from your datasource.</p>
					<br/>
					<h4>About datasource variables</h4>
					<p>A datasource is a spreadsheet of your data that you intend to merge into your document. Each line represents a set of data (Typically a customer) while each column represents a variable in that set. For example, you may have a variable column in your datasource called 'Name'. You can place this variable in your document template by entering <Name> in your template. A variable is referenced by the column header; in the datasource editor they are green.</p>
					<br/>
					<h4>How to edit a document template</h4>
					<p>Simply locate the template you wish to edit above and click the 'Edit' button in its row. A document template begins as a single blank page. From the editor you can edit the document's text, size and other features. To rename a document you can click on its name in the top right of the editor. You can also place variables into text areas (After you've chosen a datasource) by clicking on them under the text editor in the field control area.</p>
					<br/>
					<h4>How to merge a template with a datasource</h4>
					<p>Once you've designed your template you can merge it to a PDF by choosing a datasource through the editor and then clicking Finish->Merge to PDF. Alternatively simply find the document template in the table above and click the 'Prepare Merge' button on its row. In the window that pops up you can select the datasource you wish to merge with your template. You can choose to preview the merge or to perform the full merge through this window. Once your merge is completed you will be prompted to download it as a .PDF file ready for printing.</p>
				</div>
			</div>
		</div>
	</div>
	
</div>
</section>
</div>


<div id="dialogMergePDF" title="Merge or Preview your document as a PDF">
	<div id="datasourceSelectForm" style="text-align: left; ">  
	 
        <fieldset>  
			<p class="help-block">To preview a merge of this document template with your datasource or perform a full merge with your datasource please click the appropriate button below.</p> 
			<hr>	
			<!--<h4>Datasource</h4>
			<p id='mergeDSName'></p>-->
			<h4>Progress</h4>
			<br/>
			<p id='mergeProgress'>Click Create to begin the merge.</p>		
		</fieldset>
</div>

</body>
</html>

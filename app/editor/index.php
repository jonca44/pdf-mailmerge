<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require "../php/dataServer.php";
	
	//Fetch the numeric document id.
	if(isset($_GET['document_id']) && is_numeric($_GET['document_id'])){
		$inData['document_id'] = (int)$_GET['document_id'];
	}
	
	$backgroundsData = $server->fetchBackgrounds($inData);
	$datasourceData = $server->fetchDatasources();
	$server->fetchDocumentData($inData);	
	
	//echo "<pre>";
	//die(print_r($server->outData['data']));
?>
<!DOCTYPE html> 
<html lang="en">
  <head>
	<meta charset="utf-8">
	<style>
		
		.container {
				background: white;				
				background-repeat: no-repeat;
				background-position: top left;
				background-size:100%;

				display: inline-block;
				overflow: hidden;
				width: 1000px;
				height: 1414px;         
				border: 4px solid black;
				margin:-5px auto 10px;
				text-align:left;	
		}
		
		.activePage {
			border: 4px solid red;
		}
		
		<?php //Output the page backgrounds as css backgrounds.
			foreach( $server->outData['data'] as $pageCount => $pageData) {		
				$dataPath = $pageData['data_path'];  
				$backgroundData = isset($pageData['background_id']) ? 'background-image: url("'.$dataPath.$pageData['file_name'].'?id='.$pageData['background_id'].'");' : '';
				echo '.container[rel="'.($pageCount).'"] {  '.$backgroundData.'
															height: '.round($pageData['height'] * 4.7609).'px;
															width: '.round($pageData['width'] * 4.7609).'px;
				} '."\n";		
			}
		?>
		
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
	<!--<script src="min/g=js"></script>-->

	<script src="https://d3lp1msu2r81bx.cloudfront.net/kjs/js/lib/kinetic-v4.4.2.min.js"></script>
	<script type="text/javascript" src="js/mColorPicker.min.js"></script>
	<script src="js/editor_clickable.js"></script>
	<script src="js/formgoblin.js"></script>
	<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
	<script type="text/javascript" src="js/formly.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../dashboard/js/guiders-1.3.0.js"></script>

	
	<link href="css/redmond/jquery-ui-1.9.2.custom.css" rel="stylesheet">	
	<link href="css/editor.css" rel="stylesheet" type="text/css">	
	<link rel="stylesheet" href="css/bootstrap.css">
	<link rel="stylesheet" href="css/font-awesome.css">
	<link rel="stylesheet" href="css/formly.min.css" type="text/css" />
	<link rel="stylesheet" href="../dashboard/css/guiders-1.3.0.css" type="text/css" />
	
	<script> 
		documentData = <?=json_encode($server->outData['data'])."\n"?>;
		<?php //Output the page backgrounds as css backgrounds.
			$DSArray = array();
			foreach( $datasourceData as $key => $DSD) { 
				$DSArray[$DSD['datasources_id']] = $DSD['datasources_headers'];
			}
			echo "datasourceData = ".json_encode($DSArray);
		?>
	</script>
	
	<?php if($_GET['tut']) {
	
		//Update the tutorial progression indicator.
		$user_id	= $server->session->get_user_var('id');
		$query = "UPDATE users SET tutorial_position = 5 WHERE id = ? and 5 > tutorial_position";
		$stmt     = $server->sql->link->prepare($query);
		$stmt->bind_param('i', $user_id); 
		$stmt->execute();	
	}
	?> 
	<?php if(isset($_GET['tut'])) { ?> 	
	<script>
		$(document).ready(function() {
				
			var guiderTxtShown = 0;
			
			guiders.createGuider({
			  attachTo: "#headers",
			  buttons: [{name: "Next"}],					
			  description: "<img style='float: left;' src='../dashboard/images/tutorial/dog3.png'/><div style='padding-top: 40px; padding-bottom:30px;'>Wow you're doing great, we're almost at the end of the tutorial already! This is our document template editor. From here you can drag your text areas around on top of your background pictures we saw earlier.</div>",
			  id: "first",
			  next: "second",
			  position: "bottom",
			  title: "",
			  width: 800
			})<?=$_GET['tut'] ? '.show();' : ';'?> 
			
			
			guiders.createGuider({
			  attachTo: "#container_1",
			  buttons: [{name: "Close, then click on the address block", onclick: guiders.hideAll}],
			  description: "This is a text area with some variables (From our datasource) in it. You can drag it around and using the two buttons to its right, either resize it or delete it. We can edit a text area by clicking on it and then modifying its text and styling over there on the left in the field control area. Let's give it a shot! Go ahead and click on the address block here.",
			  id: "second",
			  next: "third",
			  position: "bottom",
			  title: "One of our text areas",
			  offset: { left: -100, top: -1040 },
			  width: 600
			});
			
			$("#fontTextArea").focus( function() {
				if(!guiderTxtShown) {
					guiderTxtShown = 1;
					guider3.show('third');
				}
			
			});
			
			
			guider3 = guiders.createGuider({
			  attachTo: "#fontTextArea",
			  buttons: [{name: "Next"}],
			  description: "This is where we edit our text area's contents. As I said earlier, a text area can contain both plain text and variables from your datasource. Variables will always be wrapped in < and > characters. You can add variables from your datasource just below by clicking on them, they're coloured green. <br/>&nbsp;",
			  id: "third",	
			  next: "fourth",
			  position: "right",
			  title: "The text editing area",
			  width: 600
			});
			
			guiders.createGuider({
			  buttons: [{name: "Next"}],
			  description: "We've got just one step left before we preview our document as a PDF. You've been a great participant! Feel free to have a play around with the system and don't hesitate to contact us on email for support at <a target='_blank' href='mailto:support@rocketmailmerge.com'>support@rocketmailmerge.com</a>.<br/><br/>Good luck! <br/>&nbsp;",
			  id: "fourth",	
			  next: "fifth",
			  position: "bottom",
			  title: "Almost the end",
			  width: 500,
			});
			
			moveArrow = function() { setTimeout(function(){ $(".guiders_arrow_up").last().animate({                
			'left':'+=100px'},750); },200);	 };
			
			guiders.createGuider({
			  attachTo: "#final-menu-dropdown",
			  buttons: [{name: "Close, then click on Finish and 'Preview as PDF'", onclick: guiders.hideAll}],
			  description: "When you're happy with how your document looks you can preview it as a PDF. We can do that by clicking on this menu here. You can also click on 'Merge to PDF' to perform a final full mail merge once you're happy with the results. Lets give a preview a try. Click on Finish and then click on Preview as PDF and follow the prompts. <br/>&nbsp;",
			  id: "fifth",
			  position: "bottom",
			  title: "Preview as PDF",
			  offset: { left: -100, top: 0 },
			  onShow: moveArrow,
			  width: 300,
			  height: 600
			});
			
		});
	</script>
	<?php } ?> 	

  </head>
  <body>
	<header>
		<div class="relative">
			<div id="h" class="clearfix">
				<div class="left clearfix">
					<div class="left logo"><img src="img/logo.png" width=42><div class="loading"></div></div>
					<div class="left project proxima show-projects">
						<span title="Click rename the document." class="name" id="document_name">RMM <?= isset($server->outData['data'][1]['document_name']) ? "- ".$server->outData['data'][1]['document_name'] : ""?></span>
					</div>
					<div class="left project proxima show-projects cogmenu btn-group">
						<div class="icon dropdown-toggle" data-toggle="dropdown"></div>
						<ul class="dropdown-menu">
							<li class="dropdown-submenu">
							  <a id="gridToggle" rel="off" href="#"><i class="icon-check-empty"></i> Grid</a>
							  <ul class="dropdown-menu">
								<li><a href="#" class="gridSize" rel="on"  size="50"> <i class="icon-check"></i> 50px</a></li>
								<li><a href="#" class="gridSize" rel="off" size="100"><i class="icon-check-empty"></i> 100px</a></li>
								<li><a href="#" class="gridSize" rel="off" size="150"><i class="icon-check-empty"></i> 150px</a></li>
								<li><a href="#" class="gridSize" rel="off" size="200"><i class="icon-check-empty"></i> 200px</a></li>
								<li class="divider"></li>
								<li id="gridCustomSize"><a href="#">Other Size</a></li>
								<li class="divider"></li>
								<li><a href="#" id="gridOn"><i class="icon-check-empty"></i> Grid On</a></li>
								<li><a href="#" id="gridOff"><i class="icon-check"></i> Grid Off</a></li>
							  </ul>
							</li>
							<li><a href="#" id="gridHighlightToggle" rel="on"> <i class="icon-check"></i> Grid Highlight</a></li>
							<li><a href="#" id="snapToGridToggle" rel="off"><i class="icon-check-empty"></i> Grid Snap</a></li>
							<li style="display: none;"><a href="#" id="rulerTopToggle" rel="on"><i class="icon-check"></i> Ruler - Top</a></li>
							<!--<li><a href="#"><i class="icon-check-empty"></i> Ruler - Side</a></li>-->
						</ul>
					</div>
					
					
					
					<div class="left fontbtns btn-group newfield">
						<a class="btn btn-green" id="addField">New Text Area</a>
						<!--<a class="btn btn-green" id="addFieldVariable">New Variable Area</a>-->
					</div>
					
					<div class="left fontbtns btn-group newfield">
						<a class="btn btn-green addNewPage" id="addField">New Page</a>
					</div>
					
					
					<div class="left icons">            
						<span class="saving" style="display: none"><img src="img/saving_animation.gif"> Saving</span><span class="all-saved" style="display: none"><img src="img/check.png"> All changes saved</span>
					</div>
					 
				</div>
				
				<div class="right clearfix">
					<!--<div class="left sizes">
						<a href="#" class="size" data-size="phone"><img src="http://jetstrap-site.s3.amazonaws.com/images/builder/phone_icon.png"></a>
						<a href="#" class="size" data-size="tablet"><img src="http://jetstrap-site.s3.amazonaws.com/images/builder/tablet_icon.png"></a>
						<a href="#" class="size selected" data-size="laptop"><img src="http://jetstrap-site.s3.amazonaws.com/images/builder/laptop_icon.png"></a>
						<a href="#" class="size last" data-size="desktop"><img src="http://jetstrap-site.s3.amazonaws.com/images/builder/desktop_icon.png"></a>
					</div>-->

					<!--<div class="left switch switch-teal active" id="toggle-preview" data-checkbox="yes" data-on="" data-off="" data-toggle="switch">
						<input type="checkbox" name="yes" checked="checked">
						<span class="switch-track"></span>
						<span class="switch-thumb" data-on="" data-off=""></span>
					</div>-->
					
					<div class="left sizes btn-group">
						<a class="btn btn-green" href="#" id="setPageDatasource">Datasource</a>
					</div>
					
					<div class="left sizes btn-group">
						<a class="btn btn-green dropdown-toggle" data-toggle="dropdown" href="#">Page Setup<!-- »--></a>
						<ul class="dropdown-menu">
							<li><a id="setPageBackground" name="background" href="#"> <i class="icon-picture"></i> Set Page Background</a></li>
							<li><a id="setPageSize" name="pageSize" href="#"> <i class="icon-resize-full"></i> Set Page Size</a></li>
							<!--<li><a href="#"> <i class="icon-share"></i> Upload Image</a></li>-->
							<li class="divider"></li>
							<li class="addNewPage"><a href="#"> <i class="icon-copy"></i> Add a New Page</a></li>
							<li class="deleteSelectedPage"><a href="#"> <i class="icon-copy"></i> Delete Selected Page</a></li>
						</ul>
					</div>
					
					<div class="left sizes btn-group" id="final-menu-dropdown">
					  <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">Finish<!-- »--></a>
					  <ul class="dropdown-menu">
						
						<li><a href="#" id="saveDocument"><i class="icon-file"></i> Save Changes</a></li>
						<li><a href="#" id="previewPDF"><i class="icon-tint"></i> Preview As PDF</a></li>
						<li><a href="#" id="createPDF"><i class="icon-briefcase"></i> Merge To PDF</a></li>
						<!--<li class="divider"></li>
						<li><a href="#"><i class="icon-home"></i> Save &amp; Exit</a></li>
						<li><a href="#"><i class="icon-check"></i> Auto-save Enabled</a></li>-->
					  </ul>
					</div>
					
					<!--<div class="left btn-group" id="account-dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="background-image: url('http://www.gravatar.com/avatar/adc43fd457ba3d75a9ff478f26095337?s=28&amp;d=mm')">
							<div class="niblet"></div>
						</a>
						<ul class="dropdown-menu">
							<li><a href="#" id="show-tutorial">Support</a></li>
							<li><a href="/logout">Logout</a></li>
						</ul>
					</div>-->
				</div>
			
			</div>
		</div>
	</header>
	

	<div id="b">
		<div id="frame-content">
			<div class="container-fluid">
			  <div class="row-fluid row">
				<div class="span3">
				  <div class="well sidebar-nav-fixed">
				  
					<h3 style="margin-top:-10px;">Field Control</h3>

					<span class="fontbtns btn-group fontFamily">
					  <a class="btn btn-primary" href="#"><span id="fontFamilyText">Arial</span></a>
					  <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="icon-caret-down"></span></a>
					  <ul class="dropdown-menu">
						<li><a href="#" rel="Arial"> Arial</a></li>
						<li><a href="#" rel="Times"> Times New Roman</a></li>
						<!--<li><a href="#" rel="Helvetica"> Helvetica</a></li>-->
						<li><a href="#" rel="Courier"> Courier</a></li>
						<!--<li class="divider"></li> 
						<li><a href="#"><i class="i"></i> Upload a font</a></li>-->
					  </ul>
					</span>							

					<span class="fontbtns btn-group fontSize">					
					  <a class="btn btn-primary" href="#"><span id="fontSizeText">24</span></a>
					  <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="icon-caret-down"></span></a>
					  <ul class="dropdown-menu">
						<li><a href="#" rel="8"> 8</a></li>
						<li><a href="#" rel="9"> 9</a></li>
						<li><a href="#" rel="10"> 10</a></li>
						<li><a href="#" rel="12"> 12</a></li>
						<li><a href="#" rel="14"> 14</a></li>
						<li><a href="#" rel="16"> 16</a></li>
						<li><a href="#" rel="18"> 18</a></li>
						<li><a href="#" rel="20"> 20</a></li>
						<li><a href="#" rel="24"> 24</a></li>
						<li><a href="#" rel="26"> 26</a></li>
						<li><a href="#" rel="32"> 32</a></li>
						<li><a href="#" rel="48"> 48</a></li>
						<li><a href="#" rel="64"> 64</a></li>
						<li><a href="#" rel="72"> 72</a></li>
						<!--<li class="divider"></li>
						<li><a href="#"><i class="i"></i> Other</a></li>-->
					  </ul>
					</span>
					
					<span class="btn-group">	
						<input id="textColor" type="color" name="textColor" data-text="hidden" style="height:21px;width:21px;" value="#000" class="color">
					</span>
				
			
					<div class="fontbtns btn-group fontBIU">
					  <a class="btn" rel="bold"      href="#"><i class="icon-bold"></i></a>
					  <a class="btn" rel="italic"    href="#"><i class="icon-italic"></i></a>
					  <!--<a class="btn" rel="underline" href="#"><i class="icon-underline"></i></a>-->
					</div>
					
					<div class="fontbtns btn-group fontAlign">
					  <a class="btn" rel="left" href="#"><i class="icon-align-left"></i></a>
					  <a class="btn" rel="center" href="#"><i class="icon-align-center"></i></a>
					  <a class="btn" rel="right" href="#"><i class="icon-align-right"></i></a>
					  <!--<a class="btn" rel="justify" href="#"><i class="icon-align-justify"></i></a>-->
					</div>
					
					<hr>
					<h6 style="text-align: left;">Text Editor</h6>
					<textarea id="fontTextArea"></textarea>
					<h6 style="text-align: left;">Variable Picker</h6>
					<div id="dsVarPicker">
					<?php
						foreach( $datasourceData as $key => $DSD) { 
							$selected = "";
							if($server->outData['data'][1]['datasource_id'] == $DSD['datasources_id'] ) {
								$headers = array_filter(json_decode($DSD['datasources_headers'], 1));
								$selected = $DSD['datasources_id'];
								
								$rowCount = 1;
								$rowMax   = 2;
								 
								echo "<center><div style='overflow : auto; max-height: 300px; max-width: 250px; font-weight: bold;'><table border='0' cellpadding='5'>"; 								 
								foreach( $headers as $tdKey => $td ){ 
									if($rowCount == 1)
										echo "<tr width='100'>";
									
									echo "<td class='vPickerVar' style='color: green; cursor : pointer;'>$td</td>"; 	
									
									if($rowCount == $rowMax) {
										echo "</tr>"; 
										$rowCount = 0;
									}
									
									$rowCount++;
								} 								 
								echo "</table></div><center>"; 
								break;
							}							
						}
						
						if($selected == "")
							echo "Please pick a datasource.";
					?>
					</div>
				  </div><!--/.well -->
				</div><!--/span-->

				<div class="span9 span-fixed-sidebar">		
				
					<?php //Output the page backgrounds as css backgrounds.
						$pageCounter = 1;
						
						foreach( $server->outData['data'] as $pageID => $pageData) {		
							echo '<div class="pageNum" rel="'.$pageID.'">'.$pageCounter.'</div>';
							echo '<div class="container '.($pageCounter == 1 ? 'activePage' : '').'" id="container_'.$pageID.'"  rel="'.$pageID.'"> '.($pageCounter == 1 ? '<div style="display: none" class="topRuler"></div>' : '').' </div> ';	
 
							$pageCounter++;
						}
					?>
					
				</div><!--/span-->
			  </div><!--/row-->

			  <hr>

			  <footer>
				<p>&copy; Rocket Mail Merge 2012</p>
			  </footer>
			  
			  <div style="clear: both;"></div>

			</div><!--/.fluid-container-->​
		</div>
	</div>
	
	
<!-- ui-dialog -->
<div class="ui-overlay"><div class="ui-widget-overlay" style="display: none;"></div></div>
<div id="dialogBackground" title="Choose a background">
	<div id="backgroundSelectForm" style="text-align: left; ">  
        <fieldset>  
			<p class="help-block">Choose a background from the images below to apply to your page. You can upload more backgrounds from the 'backgrounds' tab on your dashboard. Selecting a background group will show all the pages in that group.</p> 
			<hr>
		  
			<h4>Apply to page number</h4>
			<div class="control-group"> 
				<div class="controls">  
				  <select id="backgroundSelectPage">  
					<option value="all">All Pages</option>  
					<option value="1">1</option>  
					<option value="2">2</option> 
				  </select>  
				</div>  
			</div>
			<h4>Backgrounds</h4>
		</fieldset>
	</form>
	<p>
		<?php 
			$pageTable = array();
			
			echo "<table class='backgroundsTable' cellpadding='5' border='1'><tr>";
			$pageCount = 0;
			foreach($backgroundsData as $key => $data) {
				$pageData = $data['pages'][key($data['pages'])];
				echo '<td><img src="'.$data['background_thumb_path'].$pageData['background_file_name'].'" background_id="'.$data['background_id'].'" background_page_id="'.$pageData['background_page_id'].'" background_data_path="'.$data['background_data_path'].$pageData['background_file_name'].'"></td>';
						
				if(sizeof($data['pages']) > 1) {
					foreach($data['pages'] as $pageKey => $pageData) {
						$pageTable[$data['background_id']][$pageKey] = '<td><img src="'.$data['background_thumb_path'].$pageData['background_file_name'].'" background_id="'.$data['background_id'].'" background_page_id="'.$pageData['background_page_id'].'" background_data_path="'.$data['background_data_path'].$pageData['background_file_name'].'"></td>';
					}
				}
				
				$pageCount++;
						
				if($pageCount == 5) {//4 per row
					echo "</tr><tr>";
					$pageCount = 0;
				}			
			}
			echo "</tr></table>";
		
		
		
			foreach($pageTable as $backgroundKey => $backgroundData) {
				echo "<table class='pagesTable' cellpadding='5' border='1' style='display: none;' background_id='".$backgroundKey."'><tr>";
				$pageCount = 0;
				
				foreach($backgroundData as $pageKey => $pageData) {
					echo $pageData;				
					$pageCount++;
							
					if($pageCount == 5) {//4 per row
						echo "</tr><tr>";
						$pageCount = 0;
					}			
				}
				echo "</tr></table>";
			}
			
			/*
			
			
			
			
			$pageCount = 0;
			foreach($backgroundsData as $key => $data) {
				//$pageData = $data['pages'][key($data['pages'])];
				//echo '<img src="'.$data['background_thumb_path'].$pageData['background_file_name'].'" background_id="'.$data['background_id'].'" background_page_id="'.$pageData['background_page_id'].'">';
								
				foreach($data['pages'] as $pageKey => $pageData) {
					echo '<td '.($pageData['background_pg_num'] > 1 ? "style='display:none;'" : "").' ><img src="'.$data['background_thumb_path'].$pageData['background_file_name'].'" background_id="'.$data['background_id'].'" background_page_id="'.$pageData['background_page_id'].'"></td>';
					
					if($pageData['background_pg_num'] == 1)
						$pageCount++;
						
					if($pageCount == 5) {//4 per row
						echo "</tr><tr>";
						$pageCount = 0;
					}
				}				
			}
			echo "</tr></table>";*/
		?>	
	</p>
</div>

<div id="dialogDatasource" title="Choose a datasource">
	<div id="datasourceSelectForm" style="text-align: left; ">  
	 
        <fieldset>  
			<p class="help-block">Choose a datasource for your document. It will be used when you merge this template.</p> 
			<hr>	
			<h4>Datasource</h4>
			<div class="control-group"> 
				<div class="controls">  
				  <select id="datasourceSelect">  
					<?php //Output the page backgrounds as css backgrounds.
						echo '<option value="" lc="n/a" vars="n/a">No Datasource</option> '."\n";	
						foreach( $datasourceData as $key => $DSD) { 
							$selected = "";
							$headers = implode(", ", array_filter(json_decode($DSD['datasources_headers'], 1)) );
							
							if($server->outData['data'][1]['datasource_id'] == $DSD['datasources_id'] ) {
								$selected = " selected = 'selected'";
								$lines = $DSD['datasources_lines'];
								$headers2 = implode(", ", array_filter(json_decode($DSD['datasources_headers'], 1)) );
							}
							
							echo '<option value="'.$DSD['datasources_id'].'" lc="'.$DSD['datasources_lines'].'" vars="'.$headers.'" '.$selected.'>'.$DSD['datasources_name'].'</option> '."\n";		
						}
					?>
				  </select>  
				</div>  
			</div>
			<h4>Details</h4>
			
			<b><p>Line Count : </b> <span id='datasourceLC'><?= $lines ?></span></p>
			
			<b><p>Variables : </b>  <span id='datasourceVars'><?= $headers2 ?></span></p>
		
		</fieldset>
</div>

<div id="dialogMergePDF" title="Merge your document to PDF">
	<div id="datasourceSelectForm" style="text-align: left; ">  
	 
        <fieldset>  
			<p class="help-block">To merge this template with your datasource click the 'Create' button below. This will use pages from your quota.</p> 
			<hr>	
			<!--<h4>Datasource</h4>
			<p id='mergeDSName'></p>-->
			<h4>Merge Status</h4>			
			<p id='mergeProgress'>Click Create to begin the merge.</p>		
		</fieldset>
</div>

<div id="dialogPreviewPDF" title="Preview your document as PDF">
	<div id="datasourceSelectForm" style="text-align: left; ">  
	 
        <fieldset>  
			<p class="help-block">To preview merging your datasource with this document template click the 'Create' button below. Previews do not use your page quota.</p> 
			<hr>	
			<!--<h4>Datasource</h4>
			<p id='mergeDSNamePreview'></p>-->
			<h4>Merge Status</h4>			
			<p id='mergeProgressPreview'>Click Create to begin the preview merge.</p>		
		</fieldset>
</div>

<div id="dialogPagesize" title="Document page size">
	<div id="pageSizeForm" style="text-align: left; ">
		
	<form class="form-horizontal">  
        <fieldset>  
		  <p class="help-block">The page dimensions govern the final size the page will be created at in your merged PDF/print job.</p> 
		  <hr>
		  
		<h4>Settings</h4>
		<div class="control-group"> 
			<label class="control-label" for="select01">Apply Changes to Page</label>  
            <div class="controls">  
              <select id="pageSizePageSelect">  
				<option value="all">All Pages</option>  
                <option value="1">1</option>  
				<option value="2">2</option> 
              </select>  
            </div>  
        </div>
		
		<div class="control-group">  
            <label class="control-label" for="optionsCheckbox">Page Size</label>  
            <div class="controls">  
              <label class="radio">  
                <input type="radio" name="pageSizeRadio" id="pageSizeRadioPreset" value="preset" checked="true">  
                Preset
              </label>  
			  <label class="radio">  
                <input type="radio" name="pageSizeRadio" id="pageSizeRadioCustom" value="custom">  
                Custom
              </label>  
            </div>  
        </div> 
		
		<div class="control-group"> 
			<label class="control-label" for="select01">Preset</label>  
            <div class="controls">  
              <select id="pageSizePreset">                  
				<option value="a4"     width="210" height="297">A4</option>
				<option value="b5"     width="176" height="250">B5</option>
				<option value="le" width="216" height="280">US Letter</option>  
				<option value="ti" width="125" height="75">Ticket</option>
              </select>  
            </div>  
        </div>  
		
		<h4>Dimensions</h4>
		<div class="control-group"> 
			<label class="control-label" for="select01">Unit of Measure</label>  
            <div class="controls">  
              <select name="customUnit" id="pageSizeCustomUnit">
					<option value="mm">Millimetres</option>
					<option value="inch">Inches</option>
			  </select>
            </div>  
        </div> 
		<div class="control-group">  
            <label class="control-label" for="input01">Width</label>  
            <div class="controls">  
              <input type="text" disabled="true" value="210" class="input pageSizeDimension" id="pageSizeWidth">  
            </div>  
        </div>
		
		<div class="control-group">  
			<label class="control-label" for="input01">Height</label>  
			<div class="controls">  
			  <input type="text" disabled="true" value="297" class="input pageSizeDimension" id="pageSizeHeight">   
			</div>  
		</div> 
		</fieldset>
		</form>
				
	</div>
</div>

<div id="dialogGridSize" title="Choose a grid size">
	<p style="text-align: left;">Please enter a grid size in pixels.</p>
	<label style="float: left;" for="gridPx">Size</label><input name="gridPx" id="dialogGridSizePx"></input>
</div>

<div id="dialogDocumentName" title="Choose a document name">
	<p style="text-align: left;">Please enter a new document name.</p>
	<label style="float: left;" for="documentName">Size</label><input name="documentName" id="dialogDocumentNameData"></input>
</div>
	
  </body>
</html>

function isInt(n) {
   return typeof n === 'number' && n % 1 == 0;
}

jQuery.fn.extend({
insertAtCaret: function(myValue){
  return this.each(function(i) {
    if (document.selection) {
      //For browsers like Internet Explorer
      this.focus();
      sel = document.selection.createRange();
      sel.text = myValue;
      this.focus();
    }
    else if (this.selectionStart || this.selectionStart == '0') {
      //For browsers like Firefox and Webkit based
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  })
}
});

function fetchPageData() {
	
	var page_data  = {};		
	var pageKeys = Object.keys(documentData);
	for( var pg in pageKeys ) {
	  
		var variableData = [];
	  
		$.each(pageStages[pageKeys[pg]].variableLayer.getChildren(), function(index, data) {
			if( data.children.length > 0 ) { //dont look at deleted vars.
				var width  = data.get('.bottomRight')[0].getX(); //bottom right.
				var height = data.get('.bottomRight')[0].getY(); //bottom right
				
				variableData.push({name : data.attrs.name, 
								 x : data.attrs.x, 
								 y: data.attrs.y, 
								 width: width,  
								 height: height, 
								 text: data.get('.text')[0].getText(),
								 font_family : data.get('.text')[0].getFontFamily(),
								 font_size : Math.round(data.get('.text')[0].getFontSize() / (96 * (1/72))),
								 font_style : data.get('.text')[0].getFontStyle(),
								 font_padding : data.get('.text')[0].getPadding(),
								 font_color : data.get('.text')[0].getFill(),
								 font_align : data.get('.text')[0].getAlign().charAt(0).toUpperCase()});	

			}
		});
		
		var pageDataSet = { background_id : documentData[pageKeys[pg]].background_id,
						  background_pg_id : documentData[pageKeys[pg]].background_pg_id,
						  width : documentData[pageKeys[pg]].width,
						  height : documentData[pageKeys[pg]].height,
						  preset : documentData[pageKeys[pg]].preset,
						  selected_measurement_unit : documentData[pageKeys[pg]].selected_measurement_unit,
						  variables : variableData.length > 0 ? variableData : null,
						  pg_num : pageKeys[pg]
					  };
					  
		
		
		//If the page has been flagged for deletion.
		if(typeof(documentData[pageKeys[pg]].deleted_at) != 'undefined') {
			pageDataSet.deleted_at = documentData[pageKeys[pg]].deleted_at;
		}
		
		page_data[pageKeys[pg]] = pageDataSet;
				  
	}
	
	return page_data;
	
}

function redrawPageNumbers () {

	$(".pageNum:visible").each( function(key, data) {
		$(data).html(key+1);
	});
}

function assignButtonClicks() {

	//To create the PDF itself.
	$("#createPDF").click( function(e) {
		$( "#dialogMergePDF" ).dialog( "open" );			
	});
	
	$("#previewPDF").click( function(e) {
		$( "#dialogPreviewPDF" ).dialog( "open" );			
	});
	
	function gridLayerRedraw() {
		
		for( var pg in pageKeys ) {
			
			pageStages[pageKeys[pg]].gridLayer.remove();
			pageStages[pageKeys[pg]].highlightGridLayer.remove();
			
			pageStages[pageKeys[pg]].gridLayer = createAndDrawGrid(pageStages[pageKeys[pg]].stage, gridSize, ($("#gridToggle").attr('rel') == 'on') );
			//highlightGridLayer = createHighlightGridLayer(pageStages[pageKeys[pg]].stage,gridSize);	
			pageStages[pageKeys[pg]].highlightGridLayer = createHighlightGridLayer(pageStages[pageKeys[pg]].stage,gridSize);	
			
		}
	
	}
	
	$('#dsVarPicker').on('click', ".vPickerVar", function(event) {
		$("#fontTextArea").insertAtCaret("<"+$(this).text()+">").keyup();
	});
	 
	
	$("#saveDocument").click(function(e) {
		e.preventDefault();
		
		var page_data  = fetchPageData();
				
		$(".saving").show();
		$(".all-saved").hide();
		
		$.ajax({
            type: "POST",
            url: '../php/dataServer.php',
			dataType : 'json',
			timeout: 8000,
            data: {	  rex: 3, 
					  page_data : page_data, 
					  document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id,
					  //doc_name : documentData[parseInt(Object.keys(documentData)[0])].document_name
				  },
            success: function (retData) {
                $(".saving").hide();
				$(".all-saved").html('<img src="img/check.png"> '+retData.text).show().delay(5000).fadeOut(400);
            },
			error: function (x, t, m) { 
                $(".saving").hide();
				$(".all-saved").html('<img src="img/x.png"> Error saving your data, unable to connect to the server!').show();
            }
        });
	
	});
	
	//To add a new field to the document.
	$("#addField").click( function(e) {
	
		//Position the new text area in the center of the page.
		editor.addVariable(pageStages[activePage], pageStages[activePage].stage.getWidth()/2 - 100, pageStages[activePage].stage.getHeight()/2 - 17, 200, 35, 'New Text Area.', 'Arial', 12, '', 10, 'left');
		pageStages[activePage].variableLayer.draw();
		var virtualPageCount = $(".container[rel='"+(activePage)+"']").prev('.pageNum').html()
		
		$(".all-saved").html('<img src="img/check.png"> Created a new text area at the center of page '+virtualPageCount).show().delay(5000).fadeOut(400);
		
	});
		

	
	//To add a new page to the document.
	$(".addNewPage").click( function(e) {
		e.preventDefault();
		var newPageID = editor.getNewPageID(); //will always be +1 from the last page.
	
		var newPage = $(".container:visible").last().clone();
		newPage.children().remove(); //remove all children of the exising page, this includes the ruler if it's there and any existing kineticjs items.
		newPage.attr("rel",newPageID);
		newPage.attr("id","container_"+newPageID);
		
		
		newPage.css("width", $(".container:visible").last().css("width"));
		newPage.css("height",$(".container:visible").last().css("height"));
		
		//Remove highlighting on new page.
		newPage.removeClass("activePage");			
		
		$(".container:visible").last().parent().append('<div class="pageNum" rel="'+newPageID+'">'+newPageID+'</div>');  
		$(".container:visible").last().parent().append(newPage);
								  
		documentData[newPageID] = {	  background_id : null,
									  background_pg_id : null,
									  width : documentData[newPageID - 1].width,
									  height : documentData[newPageID - 1].height,
									  preset : documentData[newPageID - 1].preset,
									  selected_measurement_unit : documentData[newPageID - 1].selected_measurement_unit,
									  variables : null,
									  pg_num : newPageID
								  };
								  
		var pageLayers = editor.initNewPageLayers(newPageID);									  
		editor.addPageStages(newPageID, pageLayers);
										  
		pageKeys = Object.keys(documentData);
		
		redrawPageNumbers();
				
		$(".all-saved").html('<img src="img/check.png"> Added a new page to the bottom of your document.').show().delay(5000).fadeOut(400);
				
	});
	
	
	//To delete the current page.
	$(".deleteSelectedPage").click( function(e) {
		e.preventDefault();
		var newPageID = activePage; //will always be +1 from the last page.
		
		if($(".container:visible").length == 1) {
			alert("Cannot delete this page. Your template must have at least 1 page.");
			return;
		}
	
		var virtualPageCount = $(".container[rel='"+(activePage)+"']").prev('.pageNum').html()
		var answer = confirm("Are you sure you want to delete page "+virtualPageCount+"?");
				
		if(answer) {
			var newPage = $(".container[rel='"+(activePage)+"']").hide();
			$(".pageNum[rel='"+(activePage)+"']").hide();
			
			//Remove highlighting on new page.
			newPage.removeClass("activePage");			
											  
			documentData[newPageID].deleted_at = 1;
								  
			pageKeys = Object.keys(documentData);
			
			redrawPageNumbers();
			
			$(".all-saved").html('<img src="img/check.png"> Page '+virtualPageCount+' was successfully deleted.').show().delay(5000).fadeOut(400);
		}
					
	});
	
	//Changing text color the variable text editor
	$("#fontTextArea").keyup( function(e) {
		var actVar = getActiveVariable();
		
		actVar.get('.text')[0].setText($(this).val());	
				
		updateGroupDragDimensions(actVar);
		pageStages[activePage].variableLayer.draw();
	});
	
	
	//Changing text in the variable text editor
	$('#textColor').change( function () {
		var actVar = getActiveVariable();
				
		actVar.get('.text')[0].setFill($(this).val());	
				
		pageStages[activePage].variableLayer.draw();
	});
	
	$(".fontBIU a").click( function(e) {
		e.preventDefault();
		
		var actVar = getActiveVariable();		
		var style = "";
		
		if($(this).hasClass("active"))
			$(this).removeClass("active");
		else
			$(this).addClass("active");
		
		$(".fontBIU a").each( function(key, index) {
			
			if($(index).hasClass("active"))
				style = style + " " + $(index).attr("rel");
				
		});
		
		actVar.get('.text')[0].setFontStyle(style);
				
		pageStages[activePage].variableLayer.draw();
	
	});
	
	$(".fontFamily li a").click( function(e) {
		e.preventDefault();
		
		var actVar = getActiveVariable();						
		actVar.get('.text')[0].setFontFamily($(this).attr("rel"));		
		$("#fontFamilyText").html($(this).text());				
		pageStages[activePage].variableLayer.draw();
	
	});
	
	$(".fontSize li a").click( function(e) {
		e.preventDefault();
		
		var actVar = getActiveVariable();	
		var currentHeight = actVar.get('.text')[0].getHeight();
		var newMinHeight  = parseInt($(this).attr("rel"))+(parseInt(actVar.get('.text')[0].getPadding())*2);
		
		if(currentHeight < newMinHeight) {
			//console.log(newMinHeight);
			actVar.get('.text')[0].setHeight(newMinHeight);
		}
		actVar.get('.text')[0].setFontSize($(this).attr("rel") * (96 * (1/72)));		
		$("#fontSizeText").html($(this).text());				
		pageStages[activePage].variableLayer.draw();
	
	});	
	
	$(".fontAlign a").click( function(e) {
		e.preventDefault();
		
		var actVar = getActiveVariable();		
		
		$(".fontAlign a").each( function(key, index) {			
			$(this).removeClass("active");							
		});
		
		$(this).addClass("active");
			
		actVar.get('.text')[0].setAlign($(this).attr("rel"));
		
		$("#fontTextArea").css("text-align", $(this).attr("rel"));
							
		pageStages[activePage].variableLayer.draw();
	
	});
	
	
	//Settings menu toggles
	//toggle the grid on/off
	$("#gridToggle").click( function(e) {
		if($(this).attr('rel') == "off") {
			
			for( var pg in pageKeys ) {			
				pageStages[pageKeys[pg]].gridLayer.show();	
			}
			$(this).attr('rel','on');
			$(this).children('i').removeClass('icon-check-empty').addClass('icon-check');
			
			$("#gridOn").children('i').removeClass('icon-check-empty').addClass('icon-check');
			$("#gridOff").children('i').removeClass('icon-check').addClass('icon-check-empty');
		} else {
			for( var pg in pageKeys ) {		
				pageStages[pageKeys[pg]].gridLayer.hide();	
			}			
			$(this).attr('rel','off');
			$(this).children('i').removeClass('icon-check').addClass('icon-check-empty');
			
			$("#gridOn").children('i').removeClass('icon-check').addClass('icon-check-empty');
			$("#gridOff").children('i').removeClass('icon-check-empty').addClass('icon-check');
		}
		
		gridLayerRedraw();	
	});
	
	//turn on the grid
	$("#gridOn").click( function(e) {
		for( var pg in pageKeys ) {		
			pageStages[pageKeys[pg]].gridLayer.show();
		}
		$("#gridToggle").attr('rel','on');
		$("#gridToggle").children('i').removeClass('icon-check-empty').addClass('icon-check');
		
		$("#gridOn").children('i').removeClass('icon-check-empty').addClass('icon-check');
		$("#gridOff").children('i').removeClass('icon-check').addClass('icon-check-empty');
	});
	
	//turn off the grid
	$("#gridOff").click( function(e) {
		for( var pg in pageKeys ) {		
			pageStages[pageKeys[pg]].gridLayer.hide();	
		}
		$("#gridToggle").attr('rel','off');
		$("#gridToggle").children('i').removeClass('icon-check').addClass('icon-check-empty');
		
		$("#gridOn").children('i').removeClass('icon-check').addClass('icon-check-empty');
		$("#gridOff").children('i').removeClass('icon-check-empty').addClass('icon-check');
	});
	
	//Turn on/off the top ruler
	$("#rulerTopToggle").click( function(e) {
		if($(this).attr('rel') == "off") {
			$(".topRuler").show();	
			$(this).attr('rel','on');
			$(this).children('i').removeClass('icon-check-empty').addClass('icon-check');
		} else {
			$(".topRuler").hide();		
			$(this).attr('rel','off');
			$(this).children('i').removeClass('icon-check').addClass('icon-check-empty');
		}
	});
	
	//Turn on/off the red grid
	$("#snapToGridToggle").click( function(e) {
		if($(this).attr('rel') == "off") {
			snapToGrid = 1;	
			$(this).attr('rel','on');
			$(this).children('i').removeClass('icon-check-empty').addClass('icon-check');
		} else {
			snapToGrid = 0;	
			$(this).attr('rel','off');
			$(this).children('i').removeClass('icon-check').addClass('icon-check-empty');
		}
	});
	
	
	//Turn on/off the grid Highlight
	$("#gridHighlightToggle").click( function(e) {
		if($(this).attr('rel') == "off") {
			highlightGridToggle = 1;	
			$(this).attr('rel','on');
			$(this).children('i').removeClass('icon-check-empty').addClass('icon-check');
		} else {
			highlightGridToggle = 0;	
			$(this).attr('rel','off');
			$(this).children('i').removeClass('icon-check').addClass('icon-check-empty');
		}
	});
	
	
	
	//Change the size of the grid
	$(".gridSize").click( function(e) {
		$(".gridSize").children('i').removeClass('icon-check').addClass('icon-check-empty');
		$(this).children('i').removeClass('icon-check-empty').addClass('icon-check');
		gridSize = parseInt($(this).attr('size'));
		
		gridLayerRedraw();	
	});
	
	
	
	// Link to open the dialog
	$( "#setPageBackground" ).click(function( event ) {
		
		$("#backgroundSelectPage").children().remove();
		$("#backgroundSelectPage").append('<option value="all">All Pages</option>');
		var pageCount = 1;
		for( var pg in pageKeys ) {
			if(typeof(documentData[pageKeys[pg]].deleted_at) == "undefined") { //Exclude pages we've deleted.
				$("#backgroundSelectPage").append('<option '+(pageKeys[pg] == activePage ? 'selected=true' : '')+' value="'+pageKeys[pg]+'">'+pageCount+'</option>');
				pageCount++;
			}
		}
		
		$(".pagesTable").hide();
		$(".backgroundsTable").show();
		$( "#dialogBackground" ).dialog( "open" );
		event.preventDefault();
	});

	$( "#dialogBackground" ).dialog({
		autoOpen: false,
		width: 400,
		open: function(){
            $('.ui-widget-overlay').hide().fadeIn();
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-danger",
				text: "Clear Background",
				click: function() {
					
					var pageNum = $("#backgroundSelectPage").val();
					var act = 1;

					if(pageNum == "all") {
						act = confirm("Are you sure you want to clear the background image from all pages?");
					}
					
					if(act) {
						//for( var pg = 1; pg <= Object.keys(documentData).length; pg++) {
						var pageKeys = Object.keys(documentData);
						for( var pg in pageKeys ) {
						
							if(pageKeys[pg] == pageNum || pageNum == "all") {
							
								documentData[pageKeys[pg]].background_id = null;
								documentData[pageKeys[pg]].background_pg_id = null;		
								
								$('.container[rel="'+pageKeys[pg]+'"]').css("background-image", 'none');	
										
							}
						}
						
						$( this ).dialog( "close" );
					}
					
				}
			},
			{
				class: "btn",
				text: "Close",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	$( "#dialogMergePDF" ).dialog({
		autoOpen: false,
		width: 400,
		open: function(){
			$("#mergeProgress").html("Click Create to begin the merge.");
            $('.ui-widget-overlay').hide().fadeIn();
			$(".ui-dialog-buttonset .btn-pdf-creation").removeAttr('disabled');
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-primary btn-pdf-creation",
				text: "Create",
				click: function() {
				
					
					//Disable the dialog buttons.
					$(".ui-dialog-buttonset button").attr('disabled', 'disabled');
					
					var page_data  = fetchPageData();
				
					$(".saving").show();
					$(".all-saved").hide();
					
					$("#mergeProgress").html("We're saving your changes...<br/><br/><center><img src='img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
					$(".mergeStatus").hide().html("Saving your changes").fadeIn();
					
					//Save document first.
					$.ajax({
						type: "POST",
						url: '../php/dataServer.php',
						dataType : 'json',
						timeout: 8000,
						data: {	  rex: 3, 
								  page_data : page_data, 
								  document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id,
								  //doc_name : documentData[parseInt(Object.keys(documentData)[0])].document_name
							  },
						success: function (retData) {
							$(".saving").hide();
							$(".all-saved").html('<img src="img/check.png"> '+retData.text).show().delay(5000).fadeOut(400);
							
							$("#mergeProgress").html("We're checking your available quota. This will only take a moment, please be patient...<br/><br/><center><img src='img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
							$(".mergeStatus").hide().html("Checking your quota").fadeIn();
							
							$.ajax({
								type: "POST",
								url: '../php/dataServer.php',
								dataType : 'json',
								timeout: 8000,
								data: { rex: 20,   document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id, out: 1 },
								success: function (retData) {
															
									var answer = 1;
									
									if(retData.alert == 1) {
																
										if(retData.freeTrial == true) {
											var answer = confirm("Merging this document will exceed your free trial quota by "+retData.totalMergePagesDiff+" pages. You'll need to upgrade your account to continue. We've saved your work so you won't lose any data. Would you like to upgrade now?");
											if(answer) {
												window.location = '/dashboard/account.php';
											} else {
												$("#mergeProgress").html("Click Create to begin the merge.");
												$(".ui-dialog-buttonset button").removeAttr('disabled');	
											}	
											return;
										}	
									
										if(retData.cardValid == false) {
											var answer = confirm("Merging this document will exceed your monthly quota by "+retData.totalMergePagesDiff+" extra pages. You need to add a valid credit card to your account before you can continue. Would you like to do this now?");
											if(answer) {
												window.location = '/dashboard/account.php';
											} else {
												$("#mergeProgress").html("Click Create to begin the merge.");
												$(".ui-dialog-buttonset button").removeAttr('disabled');	
											}	
											return;
										}
										if(retData.quotaCurrentlyExceeded == 0) {
											var answer = confirm("Merging this document will exceed your monthly quota by "+retData.totalMergePagesDiff+" extra pages, they will be added to your bill. Continue with your merge?");
										}
										if(retData.quotaCurrentlyExceeded == 1) {
											var answer = confirm("You have exceeded your monthly quota. If you continue this merge "+retData.totalMergePagesDiff+" extra pages will be added to your bill. You should consider upgrading your subscription. Continue with your merge?");
										}
									}
									
									if(answer) {
										$("#mergeProgress").html("We're creating your mail merge now. This will only take a moment, please be patient...<br/><br/><center><img src='img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
								
										$(".mergeStatus").hide().html("Reading your data files").fadeIn();
										setTimeout(function() {  $(".mergeStatus").hide().html("Compressing your images").fadeIn() }, 1000);
										setTimeout(function() {  $(".mergeStatus").hide().html("Writing the PDF file").fadeIn() }, 3000);
										
										$.ajax({
											type: "POST",
											url: '../createpdf/createPDF.php',
											dataType : 'json',
											//timeout: 60000,
											data: { document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id},
											success: function (retData) {
												$(".ui-dialog-buttonset button").removeAttr('disabled');
												$(".ui-dialog-buttonset button.btn-pdf-creation").attr('disabled', 'disabled'); //Disable the create button after a successful creation.
												
												$("#mergeProgress").html("Your mail merge has been successfully created.<br/><br/><b><a target='_blank' href='/user_files/"+retData['url']+"'><img width='64' height='64' src='img/download.png'> <h2 style='float:right; color: red;'>Click to download</h2></a></b>");
											},
											error: function (x, t, m) { 
												$(".ui-dialog-buttonset button").removeAttr('disabled');							
												$("#mergeProgress").html("There was an error merging your document. Please try again or email us at support@rocketmailmerge.com for help.");
												alert("There was an error merging your document. Please try again or email us at support@rocketmailmerge.com for help.");
											}
										});
									
									} else {
										$("#mergeProgress").html("Click Create to begin the merge.");
										//$('.ui-widget-overlay').hide().fadeIn();
										$(".ui-dialog-buttonset button").removeAttr('disabled');	
									}
									
								},
								error: function (x, t, m) { 
									$(".ui-dialog-buttonset button").removeAttr('disabled');														
									$("#mergeProgress").html("Unable to connect to the server, please try again!"); 
									alert("Unable to connect to the server, please try again!");
								}
							});
							
							
						},
						error: function (x, t, m) { 
							$(".saving").hide();
							$(".all-saved").html('<img src="img/x.png"> Error saving your data, unable to connect to the server!').show();
							
							$(".ui-dialog-buttonset button").removeAttr('disabled');														
							$("#mergeProgress").html("There was an error saving your document. Please try again or email us at support@rocketmailmerge.com for help."); 
							alert("There was an error saving your document. Please try again or email us at support@rocketmailmerge.com for help.");
						}
					});
										
				}
			},
			{
				class: "btn",
				text: "Close",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	
	
	$( "#dialogPreviewPDF" ).dialog({
		autoOpen: false,
		width: 400,
		open: function(){
			$("#mergeProgressPreview").html("Click Create to begin the preview merge.");
            $('.ui-widget-overlay').hide().fadeIn();
			$(".ui-dialog-buttonset .btn-pdf-creation").removeAttr('disabled');
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-primary btn-pdf-creation",
				text: "Create",
				click: function() {
					//Disable the dialog buttons.
					$(".ui-dialog-buttonset button").attr('disabled', 'disabled');
					
					
					var page_data  = fetchPageData();
				
					$(".saving").show();
					$(".all-saved").hide();
					
					$("#mergeProgressPreview").html("We're saving your changes...<br/><br/><center><img src='img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
					$(".mergeStatus").hide().html("Saving your changes").fadeIn();
					
					$.ajax({
						type: "POST",
						url: '../php/dataServer.php',
						dataType : 'json',
						timeout: 8000,
						data: {	  rex: 3, 
								  page_data : page_data, 
								  document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id,
								  //doc_name : documentData[parseInt(Object.keys(documentData)[0])].document_name
							  },
						success: function (retData) {
							$(".saving").hide();
							$(".all-saved").html('<img src="img/check.png"> '+retData.text).show().delay(5000).fadeOut(400);
							
							
							$("#mergeProgressPreview").html("We're creating your preivew mail merge now. This will only take a moment, please be patient...<br/><br/><center><img src='img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
					
							$(".mergeStatus").hide().html("Reading your data files").fadeIn();
							setTimeout(function() {  $(".mergeStatus").hide().html("Compressing your images").fadeIn() }, 1000);
							setTimeout(function() {  $(".mergeStatus").hide().html("Writing the PDF file").fadeIn() }, 3000);
							
							$.ajax({
								type: "POST",
								url: '../createpdf/createPDF.php',
								dataType : 'json',
								//timeout: 60000,
								data: { document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id, preview: 1},
								success: function (retData) {
									$(".ui-dialog-buttonset button").removeAttr('disabled');
									//$(".ui-dialog-buttonset button.btn-pdf-creation").attr('disabled', 'disabled'); //Disable the create button after a successful creation. //Leon 18-01-13 - Allow multiple previews, if they're editing the datasource.
									
									$("#mergeProgressPreview").html("Your preview mail merge has been successfully created.<br/><br/><b><a target='_blank' href='/user_files/"+retData['url']+"'><img width='64' height='64' src='img/download.png'> <h2 style='float:right; color: red;'>Click to download</h2></a></b>");
								},
								error: function (x, t, m) { 
									$(".ui-dialog-buttonset button").removeAttr('disabled');														
									$("#mergeProgressPreview").html("Unable to connect to the server, please try again!"); 
									alert("Unable to connect to the server, please try again!");
								}
							});
							
							
						},
						error: function (x, t, m) { 
							$(".saving").hide();
							$(".all-saved").html('<img src="img/x.png"> Error saving your data, unable to connect to the server!').show();
							
							$(".ui-dialog-buttonset button").removeAttr('disabled');														
							$("#mergeProgressPreview").html("Unable to connect to the server, please try again!"); 
							alert("Unable to connect to the server, please try again!");
						}
					});
					
				}
			},
			{
				class: "btn",
				text: "Close",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	
	
	
	
	// Link to open the dialog
	$( "#setPageDatasource" ).click(function( event ) {
		$( "#dialogDatasource" ).dialog( "open" );
		event.preventDefault();
	});

	$( "#dialogDatasource" ).dialog({
		autoOpen: false,
		width: 400,
		open: function(){
            $('.ui-widget-overlay').hide().fadeIn();
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-primary",
				text: "Save Changes",
				click: function() {
					var page_data  = [];						
					var pageDataSet = { pg_num : 1 }; //all doucments will atleast have 1 page.
		
					page_data.push( pageDataSet );
										
					$.ajax({
						type: "POST",
						url: '../php/dataServer.php',
						dataType : 'json',
						timeout: 8000,
						data: { rex: 3, 
								  page_data : page_data, 
								  document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id,
								  doc_datasource : $("#datasourceSelect").val() },
						success: function (retData) {
							$(".all-saved").html('<img src="img/check.png"> Your datasource change has been saved.').show().delay(5000).fadeOut(400);
							
							var datasourceSelData = jQuery.parseJSON(  datasourceData[parseInt($("#datasourceSelect").val())]  );
								
								var rowCount = 1;
								var rowMax   = 2;
								 
								var table = "<center><div style='overflow : auto; max-height: 300px; max-width: 250px; font-weight: bold;'><table border='0' cellpadding='5'>"; 								 
								for( var td in datasourceSelData ){ 
									if( rowCount == 1 )
										table += "<tr width='100'>";
									
									table +=  "<td class='vPickerVar' style='color: green; cursor : pointer;'>"+datasourceSelData[td]+"</td>"; 	
									
									if(rowCount == rowMax) {
										table +=  "</tr>"; 
										rowCount = 0;
									}
									
									rowCount++;
								} 								 
								table +=  "</table></div><center>"; 
								
								$("#dsVarPicker").html(table);
							
						},
						error: function (x, t, m) { 
							$(".all-saved").html('<img src="img/x.png"> Error saving your data, unable to connect to the server!').show();
						}
					});
						
					$( this ).dialog( "close" );
				}
			},
			{
				class: "btn",
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	
	// Link to open the pagesize dialog
	$( "#setPageSize" ).click(function( event ) {
		
		$("#pageSizePageSelect").children().remove();
		$("#pageSizePageSelect").append('<option value="all">All Pages</option>');
		
		var width = documentData[activePage].width;
		var height = documentData[activePage].height;
		if( documentData[activePage].selected_measurement_unit == 1) { //inches
			width = (parseInt(width) / 25.4).toFixed(2);
			height = (parseInt(height) / 25.4).toFixed(2);
		}
		
		$("#pageSizeWidth").val(width);
		$("#pageSizeHeight").val(height);			
		$("#pageSizeRadioPreset").attr("checked", (documentData[activePage].preset != null) );
		$("#pageSizeCustomUnit").val(documentData[activePage].selected_measurement_unit == 0 ? "mm" : "inch")
		$("#pageSizePreset").val(documentData[activePage].preset);
									
		var pageCount = 1;
		for( var pg in pageKeys ) {
			if(typeof(documentData[pageKeys[pg]].deleted_at) == "undefined") { //Exclude pages we've deleted.
				$("#pageSizePageSelect").append('<option '+(pageKeys[pg] == activePage ? 'selected=true' : '')+' value="'+pageKeys[pg]+'">'+pageCount+'</option>');
				pageCount++;
			}
		}
		$( "#dialogPagesize" ).dialog( "open" );
		event.preventDefault();
	});

	$( "#dialogPagesize" ).dialog({
		autoOpen: false,
		width: 450,
		open: function(){
            $('.ui-widget-overlay').hide().fadeIn();
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-primary",
				text: "Save Changes",
				click: function() {
									
						var pageText = (isInt(parseInt($("#pageSizePageSelect").val())) ? "page "+$("#pageSizePageSelect").val()+"?" : "all pages?");
						var modifier = 4.7609; //pixels to dots per mm, roughly 120dpi.
						var selected = $("#pageSizePreset :selected");
						var pixelsX = parseFloat($("#pageSizeWidth").val());
						var pixelsY = parseFloat($("#pageSizeHeight").val()); 
							
						if(isNaN(pixelsX)) {
							alert("Sorry, your width isn't a number");
							return;
						}
						
						if(isNaN(pixelsY)) {
							alert("Sorry, your height isn't a number");
							return;
						}						
						
												
						var answer = confirm("Are you sure you want to apply these changes to "+pageText);						
						if(answer) {
												
							//Convert inches to mm.
							if($("#pageSizeCustomUnit").val() == "inch") {
								pixelsX = ( Math.round(parseFloat($("#pageSizeWidth").val()) * 25.4)  );
								pixelsY = ( Math.round(parseFloat($("#pageSizeHeight").val()) * 25.4) );
							}
							
							var SizeXmm = pixelsX;
							var SizeYmm = pixelsY;
							
							//Convert mm to pixels.
							pixelsX = Math.round(pixelsX * modifier);
							pixelsY = Math.round(pixelsY * modifier);
															
							var pageNum = $("#pageSizePageSelect").val();
							
							//Apply the page size change to the page in question.
							//for( var pg = 1; pg <= Object.keys(documentData).length; pg++) {
							var pageKeys = Object.keys(documentData);
							for( var pg in pageKeys ) {
		
								if(pageKeys[pg] == pageNum || pageNum == "all") {
								
									$('.container[rel="'+pageKeys[pg]+'"]').css("width", pixelsX);
									$('.container[rel="'+pageKeys[pg]+'"]').css("height", pixelsY);
									
									$( "#dialogPagesize" ).dialog( "close" );			
									
									documentData[pageKeys[pg]].width  = SizeXmm;
									documentData[pageKeys[pg]].height = SizeYmm;	
									
									documentData[pageKeys[pg]].preset = $(":radio[name='pageSizeRadio']:checked").val() == "preset" ? $("#pageSizePreset").val() : null
									//documentData[pg].orientation = ;
									documentData[pageKeys[pg]].selected_measurement_unit = ($("#pageSizeCustomUnit").val() == "mm" ? 0 : 1); //0 = mm, 1 = inch
									
									
									//Update the canvas sizes.
									pageStages[pageKeys[pg]].variableLayer.setWidth(pixelsX);
									pageStages[pageKeys[pg]].variableLayer.setHeight(pixelsY);
									
									pageStages[pageKeys[pg]].stage.setWidth(pixelsX);
									pageStages[pageKeys[pg]].stage.setHeight(pixelsY);
									
									pageStages[pageKeys[pg]].tooltipLayer.setWidth(pixelsX);
									pageStages[pageKeys[pg]].tooltipLayer.setHeight(pixelsY);
									
									pageStages[pageKeys[pg]].gridLayer = createAndDrawGrid(pageStages[pageKeys[pg]].stage, gridSize, ($("#gridToggle").attr('rel') == "on"));		
									pageStages[pageKeys[pg]].highlightGridLayer = createHighlightGridLayer(pageStages[pageKeys[pg]].stage,gridSize);	
																		
								}
							}
									
						}  	

						
				}
			},
			{
				class: "btn",
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	
	//Setting a preset page size
	$("#pageSizePreset").change( function(e) {
	
		var modifier = 4.7609; //pixels to dots per mm, roughly 120dpi.
		var mmToInch = 25.4; //mm per inch. 
		
		var selected = ($(this).find(":selected"));
		
		if($("#pageSizeCustomUnit").val() == "mm") {
			$("#pageSizeWidth").val( parseInt(selected.attr("width")) );
			$("#pageSizeHeight").val( parseInt(selected.attr("height")) );
		} else {
			$("#pageSizeWidth").val(  (parseInt(selected.attr("width")) / mmToInch).toFixed(2) );
			$("#pageSizeHeight").val( (parseInt(selected.attr("height")) / mmToInch).toFixed(2) );
		}	
			
	});
	
	//Setting a preset page size
	$("#datasourceSelect").change( function(e) {
	
		$("#datasourceLC").html( $("#datasourceSelect :selected").attr('lc') );
		$("#datasourceVars").html( $("#datasourceSelect :selected").attr('vars') );
			
	});
	
	
	
	//Choosing custom or preset page size.
	$(':radio[name="pageSizeRadio"]').change( function(e) {
	
		if($(this).val() == "preset") {
			$(".pageSizeDimension").attr('disabled','disabled');
			$("#pageSizePreset").removeAttr('disabled');
		} else {
			$(".pageSizeDimension").removeAttr('disabled');		
			$("#pageSizePreset").attr('disabled','disabled');
		}
	});
	
	
	//Changing from inches to mm
	$('#pageSizeCustomUnit').change( function(e) {
			
		if($(this).val() == "mm") { //inches changing to mm
			$("#pageSizeWidth").val( Math.round(parseFloat($("#pageSizeWidth").val()) * 25.4) );
			$("#pageSizeHeight").val( Math.round(parseFloat($("#pageSizeHeight").val()) * 25.4)  );
		} else { //mm changing to inches.
			$("#pageSizeWidth").val(  (parseFloat($("#pageSizeWidth").val()) / 25.4).toFixed(2) );
			$("#pageSizeHeight").val( (parseFloat($("#pageSizeHeight").val()) / 25.4).toFixed(2) );
		}
	});
	

	
	
	$( "#gridCustomSize" ).click(function( event ) {
		$( "#dialogGridSize" ).dialog( "open" );
		event.preventDefault();
	});

	$( "#dialogGridSize" ).dialog({
		autoOpen: false,
		width: 300,
		open: function(){
            $('.ui-widget-overlay').hide().fadeIn();
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-primary",
				text: "Apply",
				click: function() {
					$( this ).dialog( "close" );
					
					var newGridSize = parseInt($("#dialogGridSizePx").val().replace(/,/g,'').replace(/^[^-0-9]*/,''));		
					if(isNaN(newGridSize))						
						alert("Sorry, that's not a valid number");
					else {
						gridSize = newGridSize;
						gridLayerRedraw();	
					}
				}
			},
			{
				class: "btn",
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	
	
	$( "#document_name" ).click(function( event ) {
		$( "#dialogDocumentName" ).dialog( "open" );
		event.preventDefault();
	});

	$( "#dialogDocumentName" ).dialog({
		autoOpen: false,
		width: 300,
		open: function(){
            $('.ui-widget-overlay').hide().fadeIn();
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: [
			{
				class: "btn btn-primary",
				text: "Apply",
				click: function() {
					$( this ).dialog( "close" );
					
					var page_data  = [];						
					var pageDataSet = { pg_num : 1 }; //all doucments will atleast have 1 page.
		
					page_data.push( pageDataSet );
					
					$.ajax({
						type: "POST",
						url: '../php/dataServer.php',
						dataType : 'json',
						timeout: 8000,
						data: { rex: 3, 
								  page_data : page_data, 
								  document_id : documentData[parseInt(Object.keys(documentData)[0])].document_id,
								  doc_name : $("#dialogDocumentNameData").val() },
						success: function (retData) {
							$("#document_name").html("RMM - "+ $("#dialogDocumentNameData").val());
							$(".all-saved").html('<img src="img/check.png"> Your document name has been saved.').show().delay(5000).fadeOut(400);
							
						},
						error: function (x, t, m) { 
							$(".all-saved").html('<img src="img/x.png"> Error saving your data, unable to connect to the server!').show();
						}
					});
				}
			},
			{
				class: "btn",
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	
	$(".pagesTable td img,.backgroundsTable td img").click(function(e) {
		var pagesTable = $('.pagesTable[background_id="'+$(this).attr('background_id')+'"]');
		if(pagesTable.length > 0) {
			$(".backgroundsTable").fadeOut('fast', function() {
				pagesTable.show();
			  });
		} else {
			var pageNum = $("#backgroundSelectPage").val();
						
			//for( var pg = 1; pg <= Object.keys(documentData).length; pg++) {
			var pageKeys = Object.keys(documentData);
			for( var pg in pageKeys ) {
			
				if(pageKeys[pg] == pageNum || pageNum == "all") {
				
					documentData[pageKeys[pg]].background_id = parseInt($(this).attr('background_id'));
					documentData[pageKeys[pg]].background_pg_id = parseInt($(this).attr('background_page_id'));		
					
					$('.container[rel="'+pageKeys[pg]+'"]').css("background-image", 'url("' + $(this).attr('background_data_path') + '?id='+documentData[pageKeys[pg]].background_id + '")');	
							
				}
			}
			
			$( "#dialogBackground" ).dialog( "close" );			
		}
			
	});
	
	$(".pagesTable td img").click(function(e) {
		
		var pageNum = $("#backgroundSelectPage").val();
						
		var pageKeys = Object.keys(documentData);
		for( var pg in pageKeys ) {
		
			if(pageKeys[pg] == pageNum || pageNum == "all") {
			
				documentData[pageKeys[pg]].background_id = parseInt($(this).attr('background_id'));
				documentData[pageKeys[pg]].background_pg_id = parseInt($(this).attr('background_page_id'));				
				
				$('.container[rel="'+pageKeys[pg]+'"]').css("background-image", 'url("' + $(this).attr('background_data_path') + '?id='+documentData[pageKeys[pg]].background_id+'")');				
				
			}
		}
	
			
	});
	
	
	//Setup an auto save function, saving every 2 minutes.
	setInterval(function(){ $("#saveDocument").click(); },120000);

}

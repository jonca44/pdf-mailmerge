// JavaScript Document		
jQuery(document).ready(function() {
		
	/**********************
	*
	* Leon Functions
	*
	**********************************************/
	
	$(".activity_datatable").on("click", ".deleteDocumentTemplate", function(event){
	
		var me = $(this);		
		var answer = confirm("Are you sure you want to delete this document template?");
		
		if(answer) {
			$.post("/php/dataServer.php", { "rex": "18", "document_id" : $(this).parents("tr").attr('document_id'), "out" : 1 },
				 function(data){
					if(data.success == 1)
						me.parents("tr").fadeOut();
					else {
						alert("Unable to delete the document");
					}
				 }, "json");
		}
	});
	
	$('#createNewTemplate').click(function(event){
		event.preventDefault();
		me = $(this).children("button");
				
		apprise('What would you like to call your document?', {'input':true}, function(filename) {

			if(filename) { 
				$.ajax({
					url: "/php/dataServer.php",
					dataType: 'json',
					data : { rex: 6, document_name: filename, out: 1 },
					type: 'POST',
					success: function (res) {
					
						if(res.return == 1) {
							dataRow = $( '	<tr document_id="'+res.data.document_id+'"><td>'+res.data.date+'</td>'+
												'<td>'+res.data.document_name+'</td>'+
												'<td></td>'+
												'<td>0</td>'+
												'<td><center>\
														<a href="/editor/?document_id='+res.data.document_id+'" target="_blank">\
															<button class="btn btn-success" >\
																<i class="icon-trash icon-white"></i>\
																<span>Edit</span>\
															</button>\
														</a>\
														<!--<button class="btn btn-success" rel="'+res.data.document_id+'">\
															<i class="icon-trash icon-white"></i>'+
														'	<span>Rename</span>'+
														'</button>-->			'+											
														'<button class="btn btn-info">'+
														'	<i class="icon-trash icon-white"></i>'+
														'	<span>Prepare Merge</span>'+
														'</button>					'+									
														'<button class="btn btn-danger deleteDocumentTemplate" rel="'+res.data.document_id+'">'+
															'<i class="icon-trash icon-white"></i>'+
															'<span>Delete</span>'+
														'</button>	'+
												'</center></td>'+
											'</tr>');
							
							
							$(".activity_datatable").find("tr:first").after(dataRow);							
							dataRow.css("background-color", "#FFFF9C");
						} else if(res.return == 2) {
							alert(res.text);
						}
					}
				});
			} else {
			
			}
		});
	
	});
	
	
	$( "#dialogMergePDF" ).dialog({
		autoOpen: false,
		width: 400,
		open: function(){
			$("#mergeProgress").html("Click Create to begin the merge.");
            $('.ui-widget-overlay').hide().fadeIn();
        },
		close: function(){
            $('.ui-widget-overlay').fadeOut();
        }, 
		buttons: []
	});
	
	$(".mergeTemplate").click( function(e) {
	
		var docID = $(this).attr('rel');
	
		$("#dialogMergePDF").dialog({ buttons : [
			{
				class: "btn btn-primary",
				text: "Preview Merge",
				click: function() {
					
					$("#mergeProgress").html("We're creating your mail merge now. This will only take a moment, please be patient...<br/><br/><center><img src='/editor/img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
			
					$(".mergeStatus").hide().html("Reading your data files").fadeIn();
					setTimeout(function() {  $(".mergeStatus").hide().html("Compressing your images").fadeIn() }, 1000);
					setTimeout(function() {  $(".mergeStatus").hide().html("Writing the PDF file").fadeIn() }, 3000);
					
					$.ajax({
						type: "POST",
						url: '../createpdf/createPDF.php',
						dataType : 'json',
						//timeout: 60000,
						data: { document_id : docID, preview: 1},
						success: function (retData) {
							$(".ui-dialog-buttonset button").removeAttr('disabled');
							$(".ui-dialog-buttonset button.btn-pdf-creation").attr('disabled', 'disabled'); //Disable the create button after a successful creation.
							
							$("#mergeProgress").html("Your mail merge has been successfully created.<br/><br/><b><a target='_blank' href='/user_files/"+retData['url']+"'><img width='64' height='64' src='/editor/img/download.png'> <h2 style='float:right; color: red;'>Click to download</h2></a></b>");
						},
						error: function (x, t, m) { 
							$(".ui-dialog-buttonset button").removeAttr('disabled');							
							$("#mergeProgress").html("Unable to connect to the server, please try again!");
							alert("Unable to connect to the server, please try again!");
						}
					});
					
				}
			},
						
			{
				class: "btn btn-info",
				text: "Full Merge",
				click: function() {
					//Disable the dialog buttons.
					$(".ui-dialog-buttonset button").attr('disabled', 'disabled');
					
					$("#mergeProgress").html("We're checking your available quota. This will only take a moment, please be patient...<br/><br/><center><img src='/editor/img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
					$(".mergeStatus").hide().html("Checking your quota").fadeIn();
					
					$.ajax({
						type: "POST",
						url: '../php/dataServer.php',
						dataType : 'json',
						timeout: 8000,
						data: { rex: 20,   document_id : docID, out: 1 },
						success: function (retData) {
													
							var answer = 1;
							
							if(retData.alert == 1) {
							
								if(retData.freeTrial == true) {
									var answer = confirm("Merging this document will exceed your free account quota by "+retData.totalMergePagesDiff+" pages. You'll need to upgrade your account to continue. Would you like to do this now?");
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
										window.location = 'account.php';
									} else {
										$("#mergeProgress").html("Click Create to begin the merge.");
										$(".ui-dialog-buttonset button").removeAttr('disabled');	
									}	
									
									return;
								}
								if(retData.quotaCurrentlyExceeded == 0) {
									var answer = confirm("Merging this document will exceed your monthly quota by "+retData.totalMergePagesDiff+" extra pages. Continue?");
								}
								if(retData.quotaCurrentlyExceeded == 1) {
									var answer = confirm("You have exceeded your monthly quota. If you continue this merge "+retData.totalMergePagesDiff+" extra pages will be added to your bill. You should consider upgrading your subscription. Continue?");
								}
							}
							
							if(answer) {
								$("#mergeProgress").html("We're creating your mail merge now. This will only take a moment, please be patient...<br/><br/><center><img src='/editor/img/loadingBar.gif'><br/><p class='mergeStatus'></p></center>");
						
								$(".mergeStatus").hide().html("Reading your data files").fadeIn();
								setTimeout(function() {  $(".mergeStatus").hide().html("Compressing your images").fadeIn() }, 1000);
								setTimeout(function() {  $(".mergeStatus").hide().html("Writing the PDF file").fadeIn() }, 3000);
								
								$.ajax({
									type: "POST",
									url: '../createpdf/createPDF.php',
									dataType : 'json',
									//timeout: 60000,
									data: { document_id : docID},
									success: function (retData) {
										$(".ui-dialog-buttonset button").removeAttr('disabled');
										$(".ui-dialog-buttonset button.btn-pdf-creation").attr('disabled', 'disabled'); //Disable the create button after a successful creation.
										
										$("#mergeProgress").html("Your mail merge has been successfully created.<br/><br/><b><a target='_blank' href='/user_files/"+retData['url']+"'><img width='64' height='64' src='/editor/img/download.png'> <h2 style='float:right; color: red;'>Click to download</h2></a></b>");
									},
									error: function (x, t, m) { 
										$(".ui-dialog-buttonset button").removeAttr('disabled');							
										$("#mergeProgress").html("Unable to connect to the server, please try again!");
										alert("Unable to connect to the server, please try again!");
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
					
				}
			},
			
			{
				class: "btn",
				text: "Close",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]});
		
		$("#dialogMergePDF").dialog("open");	
		
	
	});
				
});

	

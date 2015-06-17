// JavaScript Document		
jQuery(document).ready(function() {
		
	/**********************
	*
	* Leon Functions
	*
	**********************************************/
	var blockStyle = { message: '<img style="margin:5px 10px;" src="images/loaders/load3.gif" alt=""> <h1 style="margin-bottom: 10px;">Just a moment...</h1>',
					css: { backgroundColor: '#fff', color: '#000'}};
	$(".planChange").click( function(e){
		e.preventDefault();
		var me = $(this);	

		if($(this).hasClass("disabled")) {
			alert("You are already subscribed to this plan.");
			return;
		}
		
		if($(this).hasClass("overage")) {
			alert("Sorry, your account doesn't meet the criteria for the free account. You have more than 3 backgrounds, datasources or document templates.");
			return;
		}
		
		var answer = 1;
		if($(this).attr('rel') == "free") {
			answer = confirm("Are you sure you want to change to this new plan?");
		}
		
		$.blockUI(blockStyle);
		if(answer) {
			$.post("/php/dataServer.php", { "rex": "19", "plan" : $(this).attr('rel'), "out" : 1 },
				 function(data){
					$.unblockUI();
					
					if(data.return == '1') {
						$("#subscriptionArea").html($("<iframe />"));
						$("iframe").contents().find('body').html('<h3><img style="margin:5px 10px;" src="images/loaders/load3.gif" alt=""> Connecting to our financial provider ...</h3>');
						$("iframe").attr("src", data.url).css({width: '100%', height: '840px'});
					}
					
					if(data.return == '2') {
						alert("Successfully updated your subscription to a free account");
						window.location = 'account.php?state=succeeded';
					}
					
					if(data.return == '6') {
						alert("Sorry, your account doesn't meet the criteria for the free account. You have more than 3 backgrounds, datasources or document templates.");
					}
					
					if(data.return == '3') {
						alert("Unable to connect to the server :(\n Please try again later.");
					}
				 }, "json").error(function() { $.unblockUI(); alert("Unable to connect to the server :(\n Please try again later."); });
		}
	});
	
	
	$(".cardUpdate").click( function(e){
		e.preventDefault();
		var me = $(this);	
		
		$.blockUI(blockStyle);
		if(1) {
			$.post("/php/dataServer.php", { "rex": "19", "plan" : 'card_update', "out" : 1 },
				 function(data){
					$.unblockUI();
					
					if(data.return == '1') {
						$("#subscriptionArea").html($("<iframe />"));
						$("iframe").contents().find('body').html('<h3><img style="margin:5px 10px;" src="images/loaders/load3.gif" alt=""> Connecting to our financial provider ...</h3>');
						$("iframe").attr("src", data.url).css({width: '100%', height: '840px'});
					}
										
					if(data.return == '3') {
						alert("Unable to connect to the server :(\n Please try again later.");
					}
				 }, "json").error(function() { $.unblockUI(); alert("Unable to connect to the server :(\n Please try again later."); });
		}
	});
	
	
	$(".cancelAccount").click( function(e){
		e.preventDefault();
		var me = $(this);	
		var answer = confirm("Are you sure you want to cancel your subscription? Your account will become closed at the end of your billing period.");
		
		if(answer) {
			$.blockUI(blockStyle);
					
			$.post("/php/dataServer.php", { "rex": "19", "plan" : 'cancel', "out" : 1 },
				 function(data){
					$.unblockUI();
					
					if(data.return == '4') {
						alert("We successfully cancelled your subscription.\nWe'll miss you :(");
						window.location = 'account.php?state=succeeded';
					}
					
					if(data.return == '3') {
						alert("Unable to connect to the server :(\n Please try again later.");
					}
				 }, "json").error(function() { $.unblockUI(); alert("Unable to connect to the server :(\n Please try again later."); });
		}
	});
	
	
	$(".reactivateAccount").click( function(e){
		e.preventDefault();
		var me = $(this);	
		var answer = confirm("Are you sure you want to reactivate your subscription?");
		
		if(answer) {
			$.blockUI(blockStyle);
					
			$.post("/php/dataServer.php", { "rex": "19", "plan" : 'reactivate', "out" : 1 },
				 function(data){
					$.unblockUI();
					
					if(data.return == '5') {
						alert("We successfully reactivated your subscription. Welcome back!");
						window.location = 'account.php?state=succeeded';
					}
					
					if(data.return == '3') {
						alert("Unable to connect to the server :(\n Please try again later.");
					}
				 }, "json").error(function() { $.unblockUI(); alert("Unable to connect to the server :(\n Please try again later."); });
		}
	});

	
				
});

	
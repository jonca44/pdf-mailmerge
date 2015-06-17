	$(document).ready(function(){
		$(".language").hide(); // Hide all tab conten divs by default
		$(".language:first").show(); // Show the first div of tab content by default
		
		$(".language a").click(function(){ //Fire the click event
			var activeTab = $(this).attr("href"); // Catch the click link
			$(".language a").removeClass("active"); // Remove pre-highlighted link
			$(this).addClass("active"); // set clicked link to highlight state
			$(activeTab).fadeIn(); // show the target tab content div by matching clicked link.
		});		
		
		$(".forget").click( function(e) {
			e.preventDefault();
			alert("Please email your username to the team at support@rocketmailmerge.com. We will email a password reset link to your account's registered email address.");		
		});
	});
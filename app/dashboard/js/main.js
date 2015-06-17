// JavaScript Document		
jQuery(document).ready(function() {
	
//===== CHART LINEAR =====//
	if(jQuery('#chart_linear').length) /*Check if the element exists*/
	{
		var sin = [], cos = [];
		
		for (var i = 0; i < 10; i += 0.5) {
			sin.push([i, Math.sin(i)]);
			cos.push([i, Math.cos(i)]);
		}
		
		var plot = $.plot($("#chart_linear"), [ { data: sin, label: "sin(x)"}, { data: cos, label: "cos(x)" } ], {
			series: {
				lines: { show: true },
				points: { show: true }
			},
			grid: { hoverable: true, clickable: true },
			yaxis: { min: -1.1, max: 1.1 },
			xaxis: { min: 0, max: 9 }
		});
		
		function showTooltip(x, y, contents)
		{
			$('<div id="tooltip" class="tooltip">' + contents + '</div>').css({
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				
				padding: '6px 5px',
				'z-index': '9999',
				'background-color': '#31383a',
				'color': '#fff',
				'font-size': '11px',
				opacity: 0.85,
				'border-radius': '3px',
				'-webkit-border-radius': '3px',
				'-moz-border-radius': '3px'
			}).appendTo("body").fadeIn(200);
		}
		
		var previousPoint = null;
		
		$("#chart_linear").bind("plothover", function (event, pos, item){
			$("#x").text(pos.x.toFixed(2));
			$("#y").text(pos.y.toFixed(2));
			if ($("#chart_linear").length > 0)
			{
				if(item)
				{
					if(previousPoint != item.dataIndex)
					{
						previousPoint = item.dataIndex;
						$("#tooltip").remove();
						var x = item.datapoint[0].toFixed(2), y = item.datapoint[1].toFixed(2);
						showTooltip(item.pageX, item.pageY, item.series.label + " of " + x + " = " + y);
					}
				}
				else
				{
					$("#tooltip").remove();
					previousPoint = null;
				}
			}
		});
		
		$("#chart_linear").bind("plotclick", function (event, pos, item){
			if(item) {
				$("#clickdata").text("You clicked point " + item.dataIndex + " in " + item.series.label + ".");
				plot.highlight(item.series, item.datapoint);
			}
		});
	}
	
	
//===== CHART -  AUTOUPDATE =====//
	if(jQuery('#auto_update').length) /*Check if the element exists*/
	{
		// we use an inline data source in the example, usually data would
		// be fetched from a server
		var data = [], totalPoints = 300;
		function getRandomData() {
			if (data.length > 0)
				data = data.slice(1);
	
			// do a random walk
			while (data.length < totalPoints) {
				var prev = data.length > 0 ? data[data.length - 1] : 50;
				var y = prev + Math.random() * 10 - 5;
				if (y < 0)
					y = 0;
				if (y > 100)
					y = 100;
				data.push(y);			
			}
	
			// zip the generated y values with the x values
			var res = [];
			for (var i = 0; i < data.length; ++i)
				res.push([i, data[i]])
			return res;
		}
	
		// setup control widget
		var updateInterval = 120;
		$("#updateInterval").val(updateInterval).change(function () {
			var v = $(this).val();
			if (v && !isNaN(+v)) {
				updateInterval = +v;
				if (updateInterval < 1)
					updateInterval = 1;
				if (updateInterval > 2000)
					updateInterval = 2000;
				$(this).val("" + updateInterval);
			}
		});
	
		// setup plot
		var options = {
			series: { shadowSize: 0 }, // drawing is faster without shadows
			yaxis: { min: 0, max: 100 },
			xaxis: { show: false }
		};
		var plot = $.plot($("#auto_update"), [ getRandomData() ], options);
	
		function update() {
			plot.setData([ getRandomData() ]);
			// since the axes don't change, we don't need to call plot.setupGrid()
			plot.draw();
			
			setTimeout(update, updateInterval);
		}
	
		update();	
	}
	
		
//===== CHART - BASIC PIE =====//
	if(jQuery('#basic_pie').length)/*Check if the element exists*/
	{
			// data
		/*var data = [
			{ label: "Series1",  data: 10},
			{ label: "Series2",  data: 30},
			{ label: "Series3",  data: 90},
			{ label: "Series4",  data: 70},
			{ label: "Series5",  data: 80},
			{ label: "Series6",  data: 110}
		];*/
		/*var data = [
			{ label: "Series1",  data: [[1,10]]},
			{ label: "Series2",  data: [[1,30]]},
			{ label: "Series3",  data: [[1,90]]},
			{ label: "Series4",  data: [[1,70]]},
			{ label: "Series5",  data: [[1,80]]},
			{ label: "Series6",  data: [[1,0]]}
		];*/
		var data = [];
		var series = Math.floor(Math.random()*10)+1;
		for( var i = 0; i<series; i++)
		{
			data[i] = { label: "Series"+(i+1), data: Math.floor(Math.random()*100)+1 }
		}
	
	$.plot($("#basic_pie"), data,
	{
			series: {
				pie: {
					show: true,
					radius: 1,
					label: {
						show: true,
						radius: 3/4,
						formatter: function(label, series){
							return '<div style="font-size:8pt;text-align:center;padding:2px;color:white; line-height:16px;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
						},
						background: { opacity: 0.5 }
					}
				}
			},
			legend: {
				show: false
			}
	});}
	
	
//===== CHART - PIE DONUT =====//
	if(jQuery('#pie_donut').length)/*Check if the element exists*/
	{
	
		$.plot($("#pie_donut"), data,
		{
				series: {
					pie: {
						innerRadius: 0.5,
						show: true
					}
				}
		});
	}
	
	
	
//===== SORTABLE LIBRARY QUICKSAND =====//
	
		  // get the action filter option item on page load
	  var $filterType = $('.filter_project li.selected a').attr('class');
		
	  // get and assign the ourHolder element to the
		// $holder varible for use later
	  var $holder = $('ul.project_list');
	
	  // clone all items within the pre-assigned $holder element
	  var $data = $holder.clone();
	
	  // attempt to call Quicksand when a filter option
		// item is clicked
		$('.filter_project li a').click(function(e) {
			// reset the active class on all the buttons
			$('.filter_project li').removeClass('selected');		
			// assign the class of the clicked filter option
			// element to our $filterType variable
			var $filterType = $(this).attr('class');
			$(this).parent().addClass('selected');
			
			if ($filterType == 'all') {
				// assign all li items to the $filteredData var when
				// the 'All' filter option is clicked
				var $filteredData = $data.find('li');
			} 
			else {
				// find all li elements that have our required $filterType
				// values for the data-type element
				var $filteredData = $data.find('li[data-type=' + $filterType + ']');
			}
			
			// call quicksand and assign transition parameters
			$holder.quicksand($filteredData, {duration: 800, easing: 'easeInOutQuad'}, function(){
				initTip();
				initPop();
			});
			
			return false;
		});
		
			initTip();
			initPop();
		
	
//===== MESSAGES =====//
			//Alert
		$("div.msgbar").click(function(){
			$(this).slideUp();
		});
		
//===== AUTOGROWING TEXT AREA =====//
		$("#txtInput").autoGrow();
	
		
//===== FORM ELEMENTS =====//
		$("select, input:checkbox, input:radio").uniform(); 
	
//===== WYSWIG =====//
			editor = $("#wyswig").cleditor({width:"100%", height:"100%"});
			$(window).resize();
	
	
//===== FILE UPLOADER =====//
		// <![CDATA[
		  $('#file_upload').uploadify({
			'uploader'  : './uploadify/uploadify.swf',
			'script'    : './uploadify/uploadify.php',
			'cancelImg' : './uploadify/cancel.png',
			'folder'    : './uploads',
			'fileExt'   : '*.jpg;*.gif;*.png',
			'multi'     : true,
			'sizeLimit' : 400000
		  });
		// ]]>
		
//===== JQUERY UI =====//
		$( "#slider" ).slider();
		$( "#slider_range_m" ).slider({
				value:100,
				min: 0,
				max: 500,
				step: 50,
				slide: function( event, ui ) {
					$( "#amount" ).val( "$" + ui.value );
				}
			});
			$( "#amount" ).val( "$" + $( "#slider_range_m" ).slider( "value" ) );	
			
			
			$( "#slider-range" ).slider({
				range: true,
				min: 0,
				max: 500,
				values: [ 75, 300 ],
				slide: function( event, ui ) {
					$( "#amount_range" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
				}
			});
			$( "#amount_range" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
				" - $" + $( "#slider-range" ).slider( "values", 1 ) );	
				
			$( "#slider-range-max" ).slider({
				range: "max",
				min: 1,
				max: 10,
				value: 2,
				slide: function( event, ui ) {
					$( "#amount_max" ).val( ui.value );
				}
			});
			$( "#amount_max" ).val( $( "#slider-range-max" ).slider( "value" ) );				
	
			$( "#slider-range-min" ).slider({
				range: "min",
				value: 37,
				min: 1,
				max: 700,
				slide: function( event, ui ) {
					$( "#amount_min" ).val( "$" + ui.value );
				}
			});
			$( "#amount_min" ).val( "$" + $( "#slider-range-min" ).slider( "value" ) );
			
			/*Progress Bar*/
			$( "#progressbar" ).progressbar({
				value: 37
			});
			
			/*Date Picker*/
			$( "#datepicker" ).datepicker();
			
			$( "#datepicker_inline" ).datepicker();
			
			/*Tabs*/
			
			$( "#tabs" ).tabs();
			
			/*Full Calendar*/
			$('#full_calendar').fullCalendar({
			
				// US Holidays
				events: 'http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic',
				
				eventClick: function(event) {
					// opens events in a popup window
					window.open(event.url, 'gcalevent', 'width=700,height=600');
					return false;
				},
				
				loading: function(bool) {
					if (bool) {
						$('#loading').show();
					}else{
						$('#loading').hide();
					}
				}
				
			});
			
//===== MODAL WINDOW =====//
			function modal(){
			$('#myModal').modal();
			}
			
//===== CODE HIGHLIGHTER =====//
			$('pre.code').highlight({source:1, zebra:1, indent:'space', list:'ol'});
			
//===== jQUERY DATA TABLE =====//			
			oTable = $('#jqtable').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers"
			});
			
//===== RESPONSIVE NAV =====//	
	jQuery(".res_icon").toggle(function() {
		 $('#responsive_nav').slideDown(300);	
		 }, function(){
		 $('#responsive_nav').slideUp(300);		 
	});	
	
	
	
	/**********************
	*
	* Leon Functions
	*
	**********************************************/
	
	
	$(".msgbar").click( function(e) {
	
		$.post("/php/dataServer.php", { "rex": "12", "notice_id" : $(this).attr('rel') },
			 function(data){
			 }, "json");
	});
	
	
	$(".deleteDocument").click( function(e) {
	
		var me = $(this);
		
		var answer = confirm("Are you sure you want to delete this document?");
		
		if(answer) {
			$.post("/php/dataServer.php", { "rex": "17", "document_id" : $(this).attr('rel'), "out" : 1 },
				 function(data){
					if(data.success == 1)
						me.parents("tr").fadeOut();
					else {
						alert("Unable to delete the document");
					}
				 }, "json");
		}
	});
	
	$(".logout").click( function(e) {
	
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
	
	//Set a cookie containing the GMT offset in minutes.
	d = new Date; 
	gmtoffset = d.getTimezoneOffset() *-1;
	document.cookie = 'gmtoffset=' + gmtoffset+';path=/;';	
	
	//===== CHART - STACKED BAR =====//
	if(jQuery('#stacked_bar').length)/*Check if the element exists*/
	{
		var d1 = [];
		var d2 = [];
		var lastPoint = 0;
		
		//Initialise our data at 0.
		d1.push([billingPeriodStart, 0]);
		d2.push([billingPeriodStart, planIncludedPages]);
		
		for ( var i = 0; i < dashboardGraphData.length; i++ ) {
			lastPoint += dashboardGraphData[i].pages
			d1.push([dashboardGraphData[i].created_at*1000, lastPoint]);
			d2.push([dashboardGraphData[i].created_at*1000, planIncludedPages]);
		}
		
		//Push data for the end of the month to close gaps on our graph.
		d1.push([billingPeriodEnd, lastPoint]);
		d2.push([billingPeriodEnd, planIncludedPages]);

		var stack = null, bars = false, lines = true, steps = false;
		
		function plotWithOptions() {
			$.plot($("#stacked_bar"), [ {label : "Pages Used", data: d1, color: "blue"}, 
										{label : "Monthly Page Quota", data: d2, color: "red", lines: { show: true, fill: false }}], {
				series: {
					stack: stack,
					lines: { show: lines, fill: true, steps: steps },					
					bars: { show: bars, barWidth: 0.4 }
				},
				xaxis: { mode: "time",  timeformat: "%d %b", timezone: "local", min: billingPeriodStart,  max: billingPeriodEnd},
				legend: {
							position: "nw"
						},
				points: {show: true}
			});
		}
		
		plotWithOptions();

		$(".graphControls input").click(function (e) {
			e.preventDefault();
			bars = $(this).val().indexOf("Bars") != -1;
			lines = $(this).val().indexOf("Lines") != -1;
			steps = $(this).val().indexOf("steps") != -1;
			plotWithOptions();
		});
	}
			
});

//===== TOOLTIP =====//
	function initTip()
	{
		jQuery('.tip_north').tipsy({gravity: 's'});
		jQuery('.tip_south').tipsy({gravity: 'n'});
		jQuery('.tip_east').tipsy({gravity: 'e'});
		jQuery('.tip_west').tipsy({gravity: 'w'});
	}

//===== FANCYBOX POPUP =====//
	function initPop()
	{
		jQuery("a#gallery_box").fancybox({
			'titlePosition' : 'inside'
		}); 
	}
	
	

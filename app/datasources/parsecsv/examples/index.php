<?php
	
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

//require ("../../php/sessionStartAndCheck.php"); 

# include parseCSV class.
require_once('../parsecsv.lib.php');

# create new parseCSV object.
$csv = new parseCSV();


# Example conditions:
// $csv->conditions = 'title contains paperback OR title contains hardcover';
// $csv->conditions = 'author does not contain dan brown';
// $csv->conditions = 'rating < 4 OR author is John Twelve Hawks';
// $csv->conditions = 'rating > 4';
// $csv->conditions = 'Data is empty';
// $csv->conditions = 'Data is not empty';


	$csv->parse('../out/test.csv', 0 , 1000); // At max 1000 lines.
	
	if($csv->error_info)
		print_r($csv->error_info);
		
?>
<!doctype html>
<html>
<head>
  <meta charset='utf-8'>
  <title>Conditional formatting - Handsontable</title>

  <!--
  Loading Handsontable dependencies.
  Please note that some dependencies are optional:
   - bootstrap-typeahead.js - is required only if you need the autocomplete feature
   - jquery.contextMenu.js  - is required only if you need the context menu feature
   - jquery.contextMenu.css - is required only if you need the context menu feature
   - jquery.ui.position.js  - is required only if you need the context menu feature
  -->
  <script src="../lib/jquery.min.js"></script>
  <script src="https://raw.github.com/warpech/jquery-handsontable/master/jquery.handsontable.js"></script>
  <script src="../lib/bootstrap-typeahead.js"></script>
  <script src="../lib/jQuery-contextMenu/jquery.contextMenu.js"></script>
  <script src="../lib/jQuery-contextMenu/jquery.ui.position.js"></script>
  <link rel="stylesheet" media="screen" href="../lib/jQuery-contextMenu/jquery.contextMenu.css">
  <link rel="stylesheet" media="screen" href="../jquery.handsontable.css">
 
  <!--
  Loading demo dependencies. They are used here only to enhance the examples on this page
  -->
  <script src="http://handsontable.com/demo/js/highlight/highlight.pack.js"></script>
  <link rel="stylesheet" media="screen" href="http://handsontable.com/demo/js/highlight/styles/github.css">

  
</head>

<body>

<div id="container">


  <div class="rowLayout">
    <div class="descLayout">
      <div class="pad bottomSpace650">

        <div id="example1"></div>

		<p>
          <button name="dump" data-dump="#example1">Dump to console</button>
		  
		  <button name="save" data-dump="#example1">Save data</button>
        </p>
      </div>
    </div>

        <script>
		
		if (!Array.prototype.filter)
		{
		  Array.prototype.filter = function(fun /*, thisp */)
		  {
			"use strict";
		 
			if (this == null)
			  throw new TypeError();
		 
			var t = Object(this);
			var len = t.length >>> 0;
			if (typeof fun != "function")
			  throw new TypeError();
		 
			var res = [];
			var thisp = arguments[1];
			for (var i = 0; i < len; i++)
			{
			  if (i in t)
			  {
				var val = t[i]; // in case fun mutates this
				if (fun.call(thisp, val, i, t))
				  res.push(val);
			  }
			}
		 
			return res;
		  };
		}
		
		function isBlankData(element, index, array) {
		  return (jQuery.trim(element) !== "");
		}
		
		function isBlankRow(element, index, array) {
		  var containsData = 0;
		  
		  $.map(element, function(index, key) {
			if(index !== null)
				containsData += index.length;	
			return 0;
		  });
		  
		  return (containsData === 0 ? false : true);
		}
		
		  $('button[name=dump]').on('click', function () {
			var dump = $(this).data('dump');
			var $container = $(dump);
			console.log('data of ' + dump, $container.handsontable('getData'));
		  });
		  
		   $('button[name=save]').on('click', function () {
				var dump = $(this).data('dump');
				var $container = $(dump);
			
				$.ajax({
				  type: 'POST',
				  url: 'save.php',
				  data: {data  : JSON.stringify(container.handsontable('getData', 0,0, container.handsontable('getRowHeader').length-1, container.handsontable('getData')[0].filter(isBlankData).length-1).filter(isBlankRow)),
						 titles: JSON.stringify(container.handsontable('getData')[0].filter(isBlankData))},
				  success: function() { console.log("Data saved successfully.") },
				  dataType: 'text'
				});

			});
			
		var headerData = new Array();
		var finishedLoading = 0;
  
          function negativeValueRenderer(instance, td, row, col, prop, value, cellProperties) {
            Handsontable.TextCell.renderer.apply(this, arguments);
            /*if (parseInt(value, 10) < 0) { //if row contains negative number
              td.className = 'negative'; //add class "negative"
            }
            else {
              td.className = '';
            }*/
            if(row === 0) {
              td.style.fontWeight = 'bold';
              td.style.color = 'green';
			  
			//console.log("'"+value+"'");
			  if(value != "") {
					//Update the headers.
					//var headerData = container.handsontable('getData',0,0,1,container.handsontable('getData')[0].filter(isBlankData).length-1);
					headerData[col] = value;
					
					if(finishedLoading)
						container.handsontable({colHeaders: headerData});
				}
               
            }
            /*if(cellProperties.readOnly) {
              td.style.opacity = 0.7;
            }*/

            if(!value || value === '') {
              td.style.background = '#EEE';
            }
            else {
              td.style.background = '';
            }
          }

          var data = <?=json_encode($csv->unparse($csv->data, $csv->titles, null, null, null, true))?>;

          var container = $("#example1");
          var hand = container.handsontable({
			data: data,
            startRows: data.length,  //<?=$csv->filelines('../out/test.csv')?>,
			startCols: <?=count($csv->titles)?>,
			rowHeaders: true, //turn on 1, 2, 3, ...
			colHeaders: <?=json_encode($csv->titles)?>,
            minSpareRows: 1,
			minSpareCols: 1,
            contextMenu: true,
			/*onChange: function (change, source) {
				if (source === 'loadData') {
				  return; //don't save this change
				}				
			},*/
            cells: function (row, col, prop) {

              var cellProperties = {};
              //if (row === 0 || container.handsontable('getData')[row][col] === 'readOnly') {
              //  cellProperties.readOnly = true; //make cell read-only if it is first row or the text reads 'readOnly'
              //}
			  
			  cellProperties.type = {
				renderer: negativeValueRenderer
			  }
			  return cellProperties;
            }
          });
		  
		  //Update the headers to the table.
		  container.handsontable({colHeaders: headerData});
		  finishedLoading = 1;
        </script>
	</div>

</div>
</body>
</html>


<?php 
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
	echo 'Page generated in '.$total_time.' seconds.';
?>
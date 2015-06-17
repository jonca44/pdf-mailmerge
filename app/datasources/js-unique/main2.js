		
$(function () {
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
			if(index !== null && typeof(index) !== "undefined")
				containsData += index.length;	
			return 0;
		  });
		  
		  return (containsData === 0 ? false : true);
		}
				
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
			  			  
			  if(value != "" &&  value != null) {
					//Update the headers.
					//var headerData = container.handsontable('getData',0,0,1,container.handsontable('getData')[0].filter(isBlankData).length-1);
					headerData[col] = value;
					
					//if(finishedLoading)
					//	$container.handsontable({colHeaders: headerData});
				}
               
            } else if(row > 0 && td.style.fontWeight == "bold") {
				td.style.fontWeight = 'normal';
				td.style.color = '#222';
			}
            /*if(cellProperties.readOnly) {
              td.style.opacity = 0.7;
            }*/

            if(!value || value === '') {
              //td.style.background = '#EEE';
            }
            else {
              td.style.background = '';
            }
          }

          //var data = <?=json_encode($csv->unparse($csv->data, $csv->titles, null, null, null, true))?>;

          var $container = $("#example1");
		  var $parent = $container.parent();
          $("#example1").handsontable({
			//data: data,
            startRows: 8,//data.length,  //<?=$csv->filelines('../out/test.csv')?>,
			startCols:  12,// <?=count($csv->titles)?>,
			rowHeaders: true, //turn on 1, 2, 3, ...
			colHeaders: true,//<?=json_encode($csv->titles)?>,
            minSpareRows: 8,
			minSpareCols: 8,
			stretchH: 'all', 
			manualColumnResize: true,
            contextMenu: true,
			onChange: function (change, source) {
				if (source === 'loadData') {
				  return; //don't save this change
				}
				if ($parent.find('input[name=autosave]').is(':checked')) {
				  clearTimeout(autosaveNotification);
				  /*$.ajax({
					url: "json/save.json",
					dataType: "json",
					type: "POST",
					data: change, //contains changed cells' data
					complete: function (data) {
					  $console.text('Autosaved (' + change.length + ' cell' + (change.length > 1 ? 's' : '') + ')');
					  autosaveNotification = setTimeout(function () {
						$console.text('Changes will be autosaved');
					  }, 1000);
					}
				  });*/
				}
			  },
            cells: function (row, col, prop) {

              var cellProperties = {};
              //if (row === 0 || $container.handsontable('getData')[row][col] === 'readOnly') {
              //  cellProperties.readOnly = true; //make cell read-only if it is first row or the text reads 'readOnly'
              //}
			  
			  cellProperties.type = {
				renderer: negativeValueRenderer
			  }
			  return cellProperties;
            }
          });
		  
	handsontable = $container.data('handsontable');

	//Update the headers to the table.
	//$container.handsontable({colHeaders: headerData});
	finishedLoading = 1;
		  
		  
	$('#fileupload').on("click", ".actions .edit", function(event){
		event.preventDefault();
		me = $(this).children("button");
		
		$(".csvEditorWrapper").fadeIn().css("display","inline-block");
		
		$.blockUI({ css: { 
            border: 'none', 
            padding: '15px', 
            backgroundColor: '#000', 
            '-webkit-border-radius': '10px', 
            '-moz-border-radius': '10px', 
            opacity: .5, 
            color: '#fff' 
        } }); 
		
		$.ajax({
			url: "/php/dataServer.php",
			dataType: 'json',
			data : { rex: 13, file: me.attr("filename"), subdir: me.attr("subdir") },
			type: 'POST',
			success: function (res) {
				handsontable.clear();
				handsontable.updateSettings({colHeaders: []});
				headerData = new Array();
				handsontable.deselectCell();
				
				var rowCount = (res.row_count == 1 ? 2 : res.row_count);
				var colCount = (res.header_count == 0 ? 5 : res.header_count);
				
				handsontable.updateSettings({"startCols" : colCount});
				handsontable.updateSettings({"startRows" : rowCount});
			    handsontable.loadData(res.csv_data);
				
				$('button[name=save-csv]').attr("filename", me.attr("filename"));
				$('button[name=save-csv]').attr("subdir", me.attr("subdir"));
				
				$.unblockUI();			
			}
		});
	});
	
	
	$('.create-datasource').click(function(event){
		event.preventDefault();
		me = $(this).children("button");
				
		apprise('What would you like to call your file?', {'input':true}, function(filename) {

			if(filename) { 
				$.ajax({
					url: "/php/dataServer.php",
					dataType: 'json',
					data : { rex: 14, file: filename, out: 1 },
					type: 'POST',
					success: function (res) {
					
						if(res.return == 1) {
							dataRow = $( '<tr class="template-download fade in">'+        
																'<td class="delete"><input type="checkbox" name="delete" value="1"></td>'+
																'<td class="linecount"></td>'+
																'<td class="name"><a href="" title="'+ res.data.filename + '" rel="" download="'+ res.data.filename + '">'+ res.data.filename + '</a></td>'+
																'<td class="headerfields"></td><td class="size"><span>0.00 KB</span></td>'+
																'<td class="actions"><center><span class="edit"><button class="btn btn-success" filename="'+ res.data.filename + '" subdir="'+ res.data.subdir + '"><i class="icon-trash icon-white"></i><span> Edit </span></button></span>'+
																'<span class="delete"><button class="btn btn-danger" data-type="DELETE" data-url="/datasources/server/index.php?file='+ res.data.rawFilename + '&amp;subdir='+ res.data.rawSubdir + '"><i class="icon-trash icon-white"></i><span>Delete</span></button>'+
																'</span></center></td></tr>');
							$(".files").find("tr:first").after(dataRow);							
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
	   
	$('button[name=save-csv]').click(function (e) {
		e.preventDefault();
		me = $(this);
			
		handsontable.deselectCell();
		
		$("#statusText").show().html("Saving your data...");
		
		//console.log(JSON.stringify(handsontable.getData()[0].filter(isBlankData)));
		//console.log(JSON.stringify(handsontable.getData(0,0, handsontable.countRows()-1, handsontable.getData()[0].filter(isBlankData).length-1).filter(isBlankRow));
		
		var minSpareCols = handsontable.getSettings().minSpareCols;
		var colsCount    = handsontable.countCols();
		var headersData  = handsontable.getData()[0].filter(isBlankData);
		var headerAlert  = 0;
		
		if(headersData.length != (colsCount - minSpareCols)) {
			headerAlert = 1;
		}
		
		$.ajax({
		  type: 'POST',
		  url: "/php/dataServer.php",
		  data: { rex   : 15,
						  //JSON.stringify(handsontable.getData(0,0, handsontable.countRows()-1, handsontable.getData()[0].filter(isBlankData).length-1).filter(isBlankRow))
				  data  : JSON.stringify(handsontable.getData(0,0, handsontable.countRows()-1, handsontable.getData()[0].length-minSpareCols-1).filter(isBlankRow)),
				  titles: JSON.stringify(handsontable.getData()[0].filter(isBlankData)),
				  file  : me.attr("filename"),
				  subdir: me.attr("subdir") },
		  success: function() { $("#statusText").html("Saved successfully! "+(headerAlert ? "You have data in columns with no associated header on row 1." : "")).delay(10000).fadeOut();  },
		  error: function() { $("#statusText").html("Error saving your data, please try again!").delay(10000); },
		  dataType: 'text'
		});
	});
	
	$('button[name=save-close-csv]').click(function (e) {
		e.preventDefault();
		me = $(this);
		
		$('button[name=save-csv]').click();
		$(".csvEditorWrapper").fadeOut();
	});
	
	
	$('button[name=cancel-csv]').click(function (e) {
		e.preventDefault();
		
		var answer = confirm("Are you sure you want to close the editor without saving?");
		
		if(answer) {
			$(".csvEditorWrapper").fadeOut();	
			handsontable.clear();
		}
	});
	
}); 
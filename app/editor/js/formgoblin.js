$.fn.setCursorPosition = function(position){
    if(this.length == 0) return this;
    return $(this).setSelection(position, position);
}


$.fn.setSelection = function(selectionStart, selectionEnd) {
    if(this.length == 0) return this;
    input = this[0];

    if (input.createTextRange) {
        var range = input.createTextRange();
        range.collapse(true);
        range.moveEnd('character', selectionEnd);
        range.moveStart('character', selectionStart);
        range.select();
    } else if (input.setSelectionRange) {
        input.focus();
        input.setSelectionRange(selectionStart, selectionEnd);
    }

    return this;
}
	
$.fn.focusEnd = function(){
    this.setCursorPosition(this.val().length);
            return this;
}
	
	
	function createHighlightGridLayer(stage) {
	
		var stageSize = stage.getSize();	
		var highlightGridLayer = new Kinetic.Layer({name: "highlightGridLayer"});			
		
		var horizontal = new Kinetic.Line({
			points: [-gridSize, 0, -gridSize, stageSize.height],
			stroke: 'blue',
			strokeWidth: 2,
			lineCap: 'round',
			lineJoin: 'round',
			name : 'horizontal'
		});	
		
		var vertical = new Kinetic.Line({
			points: [0, -gridSize, stageSize.width, -gridSize],
			stroke: 'blue',
			strokeWidth: 2,
			lineCap: 'round',
			lineJoin: 'round',
			name : 'vertical'
		});	

		highlightGridLayer.add(horizontal);			
		highlightGridLayer.add(vertical);	
		
		stage.add(highlightGridLayer);	

		return highlightGridLayer;
	}
	
	function highlightGrid(stage, highlightGridLayer, stageObject, unhighlight) {
		/* Draw some temporary highlighted grid lines to snap to */
		var stageSize = stage.getSize();		

		var horizontalLine = highlightGridLayer.getLayer().getChildren()[0];
		var verticalLine   = highlightGridLayer.getLayer().getChildren()[1];
		
		if(highlightGridToggle) {
			if(!unhighlight) {			
				horizontalLine.setPosition([stageObject.x+gridSize, 0, stageObject.x+gridSize, stageSize.height]);
				verticalLine.setPosition  ([0, stageObject.y+gridSize, stageSize.width, stageObject.y+gridSize]);
			} else {
				horizontalLine.setPosition([-gridSize, 0, -gridSize, stageSize.height]);
				verticalLine.setPosition  ([0, -gridSize, stageSize.width, -gridSize]);			
			}
		}
				
	}
	
	function wouldSnapToGridPoints(stageObject) {
		var topLeftGrid = {};
		var ObjTopLeft  = {x : stageObject.attrs.x, y: stageObject.attrs.y };
		var returnTopLeft = {}
		
		topLeftGrid.x = Math.floor(stageObject.attrs.x/gridSize)*gridSize ; //+50
		topLeftGrid.y = Math.floor(stageObject.attrs.y/gridSize)*gridSize ; //+50
		
		/* Find the closest intersection to snap to */
		if(ObjTopLeft.x - topLeftGrid.x  < topLeftGrid.x + gridSize - ObjTopLeft.x) {
			returnTopLeft.x = topLeftGrid.x;
		} else {
			returnTopLeft.x = topLeftGrid.x + gridSize;		
		}
		
		if(ObjTopLeft.y - topLeftGrid.y  < topLeftGrid.y + gridSize - ObjTopLeft.y) {
			returnTopLeft.y = topLeftGrid.y;
		} else {
			returnTopLeft.y = topLeftGrid.y + gridSize;		
		}
		
		return returnTopLeft;
		
	}
	
	
	function getWidthHeight(group) {
		var topLeft = group.get('.topLeft')[0];
		var topRight = group.get('.topRight')[0];
		var bottomRight = group.get('.bottomRight')[0];
		var bottomLeft = group.get('.bottomLeft')[0];
		
		var width = topRight.attrs.x - topLeft.attrs.x;
		var height = bottomLeft.attrs.y - topLeft.attrs.y;
		
		return [width, height];
	};
	
	function updateGroupDragDimensions(group, activeAnchor, originalDimensions) {
		var topLeft = group.get('.topLeft')[0];
		var topRight = group.get('.topRight')[0];
		var bottomRight = group.get('.bottomRight')[0];
		var bottomLeft = group.get('.bottomLeft')[0];
		
		var topRightClose = group.get('.topRightClose')[0];
		
		var text = group.get('.text')[0];
		var border = group.get('.border')[0];
		
		var dragging = 0;

		// update anchor positions
		
		if(typeof(activeAnchor) != "undefined") {
		
			switch (activeAnchor.getName()) {
			  case 'topLeft':
				topRight.attrs.y = activeAnchor.attrs.y;
				bottomLeft.attrs.x = activeAnchor.attrs.x;
				break;
			  case 'topRight':
				topLeft.attrs.y = activeAnchor.attrs.y;
				bottomRight.attrs.x = activeAnchor.attrs.x;
				break;
			  case 'bottomRight':
				bottomLeft.attrs.y = activeAnchor.attrs.y;
				topRight.attrs.x = activeAnchor.attrs.x;
				topRightClose.attrs.x = activeAnchor.attrs.x;
				break;
			  case 'bottomLeft':
				bottomRight.attrs.y = activeAnchor.attrs.y;
				topLeft.attrs.x = activeAnchor.attrs.x;
				break;
			}
			dragging = 1;
		} else {
			text.setHeight('auto');			
			bottomRight.attrs.y = text.getHeight();
			height = bottomLeft.attrs.y - topLeft.attrs.y;
		}

		var width = topRight.attrs.x - topLeft.attrs.x;
		var height = bottomLeft.attrs.y - topLeft.attrs.y;
		
		if(width && height) {		  
			if(dragging) {
				if(height > 30) {
				text.setHeight(height);
				border.setHeight(height);
				}
				if(width > 40) {
					text.setWidth(width);
					border.setWidth(width);
				}
			}		  
		} 
		
		if(height < (parseInt(text.getFontSize()) + (text.getPadding() *2))) {
			text.setHeight(parseInt(text.getFontSize()) + (text.getPadding() * 2));
			bottomRight.attrs.y = text.getHeight();
		}
		
		if(width < 25) {
			topRightClose.attrs.x = text.getWidth();
			bottomRight.attrs.x = text.getWidth();
		}
	}
	
	function hideTip(pageLayers) {		
		pageLayers.tooltipLayer.removeChildren();
		pageLayers.tooltipLayer.draw();
		
	}
	
	function showTip(pageLayers, x, y, text) {
	
		hideTip(pageLayers);
		
		var tip = new Kinetic.Text({
		  x: x+3,
		  y: y+3,
		  //stroke: '#555',
		  //strokeWidth: 1,
		  fill: 'black',
		  text: text,
		  fontSize: 14,
		  fontFamily: 'Calibri',
		  //textFill: '#555',
		  padding: 2,
		  align: 'center',
		  shadow: {
			color: 'black',
			blur: 1,
			offset: [2, 2],
			opacity: 0.2
		  },
		  name: 'tip'
		});
		
		var rect = new Kinetic.Rect({
			x: x,
			y: y,
			stroke: '#555',
			strokeWidth: 1,
			fill: '#ddd',
			width: tip.getWidth()+6,
			height: tip.getHeight()+6,
			shadowColor: 'black',
			shadowBlur: 10,
			shadowOffset: [10, 10],
			shadowOpacity: 0.2,
			cornerRadius: 10
		  });
					
		
	
		pageLayers.tooltipLayer.add(rect);
		pageLayers.tooltipLayer.add(tip);
		pageLayers.tooltipLayer.draw();	
	}
	
	
	function addDeleteAnchor(pageLayers, group, x, y, name, visibility) {
		var stage = group.getStage();
		var layer = group.getLayer();
		
		var imageObj = new Image();
		imageObj.onload = function() {
			  var anchor = new Kinetic.Image({
				x: x,
				y: y,
				image: imageObj,
				width: 16,
				height: 16,
				name: name,
				draggable: true,
				visible: visibility
			  });

			anchor.on('click', function(e, b) {
				var answer = confirm("Are you sure you want to delete this item?")
				if (answer){
					group.remove();
					pageLayers.variableLayer.draw();
				}
			});
			
			// add hover styling
			anchor.on('mouseover', function() {				
				showTip(pageLayers, this.parent.attrs.x + this.attrs.x+20, this.parent.attrs.y + this.attrs.y - 20, "Delete"); 			  
			});
			
			anchor.on('mouseout', function() {
				hideTip(pageLayers);
			});
							
			
			group.add(anchor);
			layer.draw();
			
		};
		
		imageObj.src = "img/icon-cross.png";  
	}
	
	function addAnchor(pageLayers, group, x, y, name, visibility) {
		var stage = group.getStage();
		var layer = group.getLayer();
		var groupOriginalDimensions = "";
		
		/*var anchor = new Kinetic.Circle({
		  x: x,
		  y: y,
		  stroke: '#666',
		  fill: '#ddd',
		  strokeWidth: 2,
		  radius: 8,
		  name: name,
		  draggable: true,
		  visible: visibility
		});
		
		var anchor = new Image();

		imageObj.onload = function() {
		  context.drawImage(imageObj, x, y, width, height);
		};
		imageObj.src = "http://www.html5canvastutorials.com/demos/assets/darth-vader.jpg";
		*/		  
	  
		var imageObj = new Image();
		imageObj.onload = function() {
			  var anchor = new Kinetic.Image({
				x: x,
				y: y,
				image: imageObj,
				width: 16,
				height: 16,
				name: name,
				draggable: true,
				visible: visibility
			  });

			anchor.on('dragstart', function() {
				groupOriginalDimensions = getWidthHeight(group);
			});
			
			anchor.on('dragmove', function() {
				updateGroupDragDimensions(group, this, groupOriginalDimensions );
				layer.draw();
			});
			anchor.on('mousedown touchstart', function() {
				hideTip(pageLayers);
				group.setDraggable(false);
				this.moveToTop();
			});
			anchor.on('dragend', function() {
				group.setDraggable(true);
				layer.draw();
			});
			// add hover styling
			anchor.on('mouseover', function() {
			
				showTip(pageLayers, this.parent.attrs.x + this.attrs.x+20, this.parent.attrs.y + this.attrs.y - 20, "Resize"); 

				var layer = this.getLayer();
				document.body.style.cursor = 'pointer';
				//this.setStrokeWidth(4);
				//this.setScale(1.2);				  
				layer.draw();
			  
			});
			
			anchor.on('mouseout', function() {
				hideTip(pageLayers);

				var layer = this.getLayer();
				document.body.style.cursor = 'default';
				//this.setStrokeWidth(2);
				//this.setScale(1);
				layer.draw();
			});
			
			
			group.add(anchor);
			layer.draw();
			
		};
		
		imageObj.src = "img/arrow-resize-135-icon.png";     
		
	}
	
	/*function loadImages(sources, callback) {
		var images = {};
		var loadedImages = 0;
		var numImages = 0;
		for(var src in sources) {
		  numImages++;
		}
		for(var src in sources) {
		  images[src] = new Image();
		  images[src].onload = function() {
			if(++loadedImages >= numImages) {
			  callback(images);
			}
		  };
		  images[src].src = sources[src];
		}
	}	*/
		
	
	function showVGButtons(group, visibility) {
		var layer = group.getLayer();
		var stage = group.getStage();
		
		var bottomRightMove  = group.get('.bottomRight')[0];			
		var topRightClose    = group.get('.topRightClose')[0];
		
		if(!visibility) {
			//setTimeout(function(){ bottomRightMove.hide(); topRightClose.hide(); layer.draw(); }, 2000);
			bottomRightMove.hide(); 
			topRightClose.hide();
		} else {
			bottomRightMove.show();
			topRightClose.show();
		}
		
		layer.draw();
	
	}
	
	//Leon - Removed id variable.
	editorConstruct.prototype.addVariable = function(pageLayers, x, y, width, height, text, font_family, font_size, font_style, font_padding, font_align, font_color) {
		/*var variable1Group = new Kinetic.Group({
			x: x,
			y: (y),
			draggable: true,
			name: "variable ",
			active: 0
		});

		pageLayers.variableLayer.add(variable1Group);

		if(typeof(font_color) == "undefined")
			font_color = "rgb(0, 0, 0)";
						
		// darth vader
		var complexText = new Kinetic.Text({
		  stroke: '#A9A9A9',
		  //strokeWidth: 5,
		  //fill: '#ddd',
		  text: text,
		  fontSize: (font_size),
		  fontFamily: font_family,
		  textFill: font_color,
		  width: (width),
		  height: (height),
		  padding: (font_padding),
		  align: font_align,
		  fontStyle: font_style,
		  cornerRadius: 10,
		  opacity: 1,
		  name: 'text',
		  lineHeight: 1.5
		}); */
		
		
		var variable1Group = new Kinetic.Group({
			x: x,
			y: (y),
			draggable: true,
			name: "variable ",
			active: 0
		});

		pageLayers.variableLayer.add(variable1Group);

		if(typeof(font_color) == "undefined")
			font_color = "rgb(0, 0, 0)";
						
		// darth vader
		var complexText = new Kinetic.Text({
		  //stroke: '#A9A9A9',
		  //strokeWidth: 5,
		  //fill: '#ddd',
		  x: 0,
		  y: -2,
		  text: text,
		  fontSize: (font_size) * (96 * (1/72)), 
		  fontFamily: font_family,
		  fill: font_color, //Font color
		  width: (width),
		  height: (height),
		  padding: (font_padding),
		  align: font_align,
		  fontStyle: font_style,
		  cornerRadius: 10,
		  //opacity: .7,
		  opacity: 1,
		  name: 'text',
		  lineHeight: 1.12
		});
		
		var rect1 = new Kinetic.Rect({
			x: 0,
			y: 0,
			stroke: '#555',
			strokeWidth: 1,
			fill: 'transparent',
			width: complexText.getWidth(),
			height: complexText.getHeight(),
			cornerRadius: 10,
			name: 'border'
		  });
					
		
		variable1Group.add(rect1);
		variable1Group.add(complexText);
		addAnchor(pageLayers, variable1Group, 0, 0, 'topLeft', 0);
		addAnchor(pageLayers, variable1Group, width, 0, 'topRight', 0);
		addAnchor(pageLayers, variable1Group, width,height, 'bottomRight', 0);
		addAnchor(pageLayers, variable1Group, 0, height, 'bottomLeft', 0);
		
		addDeleteAnchor(pageLayers, variable1Group, width+2, -12, 'topRightClose', 0);

		variable1Group.on('dragstart', function() {
			this.moveToTop();
			for( var pg in pageKeys ) {
				deactivateAllVariables(pageStages[pageKeys[pg]]);
			}
			activateVariable(this);
		});
		
		variable1Group.on("click", function(){
			this.moveToTop();
			for( var pg in pageKeys ) {
				deactivateAllVariables(pageStages[pageKeys[pg]]);
			}			
			activateVariable(this);
		});
		
		variable1Group.on("dragmove", function(){
			var snapToPoint = wouldSnapToGridPoints(this);
			highlightGrid(pageLayers.stage, pageLayers.highlightGridLayer, snapToPoint);
			pageLayers.highlightGridLayer.draw(); 
		});
		
		variable1Group.on("dragend", function(){
			var snapToPoint = wouldSnapToGridPoints(this);
			if(snapToGrid) {
				this.attrs.x = snapToPoint.x
				this.attrs.y = snapToPoint.y  
			}
			highlightGrid(pageLayers.stage, pageLayers.highlightGridLayer, snapToPoint, 1);
			
			pageLayers.stage.draw();
		});		
		
		pageLayers.stage.draw();
	}
		
	function createAndDrawGrid(stage, boxSize, visibility) {
	
		var stageSize = stage.getSize();
		
		var horizontalLineCount = stageSize.width/boxSize;
		var verticalLineCount = stageSize.height/boxSize;
		
		var gridLayer = new Kinetic.Layer({name: "gridLayer"});
		for(count = 1; count < horizontalLineCount; count++) {
			var horizontal = new Kinetic.Line({
			  points: [count*gridSize, 0, count*gridSize, stageSize.height],
			  stroke: 'red',
			  strokeWidth: 2,
			  lineCap: 'round',
			  lineJoin: 'round'
			});	

			gridLayer.add(horizontal);			
		}
		
		for(count = 1; count < verticalLineCount; count++) {
			var horizontal = new Kinetic.Line({
			  points: [0, count*gridSize, stageSize.width, count*gridSize],
			  stroke: 'red',
			  strokeWidth: 2,
			  lineCap: 'round',
			  lineJoin: 'round'
			});	

			gridLayer.add(horizontal);			
		}
        
		stage.add(gridLayer);		
		gridLayer.setAttrs({visible : visibility});
		
		return gridLayer;
	}
	
	function activateVariable(group) {
		
		if(group.getChildren().length > 0) {
			
			activePage = parseInt(group.getStage().getAttrs().pageID);
			
			$(".container").removeClass("activePage");			
			$(".container[rel='"+(activePage)+"']").addClass("activePage");
						
			showVGButtons(group, 1);
			group.setAttrs({active : 1});
			$("#fontTextArea").val(group.get('.text')[0].getText());
			$("#fontTextArea").focusEnd();
			
			//Select bold or italics buttons.
			$(".fontBIU a").each( function(key, index) {
				
				$(this).removeClass("active");
				
				if(group.get('.text')[0].getFontStyle().indexOf($(index).attr("rel")) != -1)
					$(this).addClass("active");				
			});
			
			//Select Left, center right align.
			$(".fontAlign a").each( function(key, index) {
				
				$(this).removeClass("active");
				
				if(group.get('.text')[0].getAlign().indexOf($(index).attr("rel")) != -1) {
					$(this).addClass("active");				
					$("#fontTextArea").css("text-align", $(index).attr("rel"));
				}
			});
			
			$("#fontSizeText").html(Math.round(group.get('.text')[0].getFontSize() / (96 * (1/72))) );
			
			var fontFamily = group.get('.text')[0].getFontFamily();
			if(fontFamily == "Times")
				fontFamily = "Times New Roman";
				
			$("#fontFamilyText").html(fontFamily);
			
			$("#textColor").siblings('span').css('background-color', group.get('.text')[0].getFill());
						
			//group.get('.text')[0].setStroke("#8B0000"); //dark red
			//group.get('.text')[0].setFill("#ddd");
		}
	}
	
	function getActiveVariable() {
		var foundVariable = {};
			
		$.each(pageStages[activePage].variableLayer.getChildren(), function(key, index) {	
			var attrs = index.getAttrs();
			if(attrs.active == 1) {
				foundVariable = index;
				return;
			}
		});
		
		return foundVariable;
	}
	
	function deactivateAllVariables(pageLayers) {
		
		$.each(pageLayers.variableLayer.getChildren(), function(key, index) {	
			try {				
				vars = index.getAttrs();
				if(vars.active) {
					//index.getChildren()[0].setStroke("#A9A9A9");
					//index.getChildren()[0].setFill("transparent");
					showVGButtons(this, 0);
				}
				index.setAttrs({active : 0});
			} catch(e) {
				//Will throw an error when the item is deleted.
			}		
						
		});
		
		pageLayers.variableLayer.draw();
		
	}
	
	
	//Leon - Removed id variable.
	editorConstruct.prototype.fetchPageStages = function() {
		return(this.pageStages);
	}
	
	editorConstruct.prototype.addPageStages = function(pageID, pageLayers) {
		this.pageStages[pageID] = pageLayers;
	}
	
	
	editorConstruct.prototype.getNewPageID = function() {
		
		var pageKeys = Object.keys(documentData);
		
		var newPageID = parseInt(pageKeys[pageKeys.length-1]) + 1;		
		return(newPageID);
	}
	
	editorConstruct.prototype.initNewPageLayers = function(pageID) {
				
		var stage = new Kinetic.Stage({
			container: 'container_'+pageID,
			width: Math.round( documentData[pageID].width * 4.7609),
			height: Math.round( documentData[pageID].height * 4.7609),
			pageID: pageID
		});
	
		stage.draw();
				
		//Clicking on a stage, deactivate the old page and highlight this new page.
		$('#container_'+pageID).on("click", {pgNum : pageID}, function(e){

				activePage = parseInt(e.data.pgNum);
							
				$(".container").removeClass("activePage");			
				$(".container[rel='"+(activePage)+"']").addClass("activePage");
				
		});
		
		var tooltipLayer = new Kinetic.Layer({name: "tooltipLayer"});
		stage.add(tooltipLayer);
		
		var variableLayer = new Kinetic.Layer({name: "variableLayer"});
		stage.add(variableLayer);
		
		var gridLayer = createAndDrawGrid(stage,gridSize, false);		
		var highlightGridLayer = createHighlightGridLayer(stage,gridSize);	
		
		var pageLayers = { pageID : pageID,
						   stage : stage,
						   variableLayer : variableLayer,
						   gridLayer : gridLayer,
						   highlightGridLayer : highlightGridLayer,
						   tooltipLayer : tooltipLayer };
		
		if(documentData[pageID].variables !== null) {
						
			try { 			
				var variableData = (documentData[pageID].variables);
				if(variableData instanceof Array) {
					for(var c = 0; c < variableData.length; c++) {
						
						var font_align = "";
						switch(variableData[c].font_align) {
							case "R" : font_align = "right"; break;
							case "C" : font_align = "center"; break;
							case "L" : font_align = "left"; break;
							default  : font_align = "left";
						}
										
						this.addVariable(pageLayers, parseFloat(variableData[c].x), parseFloat(variableData[c].y), parseFloat(variableData[c].width), parseFloat(variableData[c].height), variableData[c].text, variableData[c].font_family, parseFloat(variableData[c].font_size), variableData[c].font_style, parseFloat(variableData[c].font_padding), font_align, variableData[c].font_color)
					}
				}
			} catch (e) {
				console.log(e);
			}
		}
		
		variableLayer.moveToTop();	
		
		return pageLayers;			
	}
		
	function editorConstruct( pageKeys ) {
		this.pageStages = new Array();
						
		for( var pg in pageKeys ) {
							
			var pageLayers = this.initNewPageLayers(pageKeys[pg]);									  
			this.pageStages[pageKeys[pg]] = pageLayers;
			
		}
	}
	   
	  
	$(function() {
	  
		gridSize = 50;
		snapToGrid = 0;
		highlightGridToggle = 1;
		pageKeys = Object.keys(documentData);
		activePage = 1;
		
		editor	   = new editorConstruct(pageKeys);
		pageStages = editor.fetchPageStages();
		
		assignButtonClicks();
		
	});
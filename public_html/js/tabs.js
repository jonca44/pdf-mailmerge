// JavaScript Document

function selTab(tabNo){
	for(var i=1;i<=5;i++){
		document.getElementById("tabSeq"+i).className ="deseltab";
		document.getElementById("contentSeq"+i).className = "desedes";	
	}
	document.getElementById("tabSeq"+tabNo).className ="seltab";	
	document.getElementById("contentSeq"+tabNo).className = "sedes";	
}

function sel2Tab(tabNo){
	for(var i=1;i<=5;i++){
		document.getElementById("tab2Seq"+i).className ="desetab2";
		document.getElementById("content2Seq"+i).className = "desetabdes2";	
	}
	document.getElementById("tab2Seq"+tabNo).className ="setab2";	
	document.getElementById("content2Seq"+tabNo).className = "setabdes2";	
}

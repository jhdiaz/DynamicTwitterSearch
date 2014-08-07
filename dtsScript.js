$(document).ready(function(){
	function initPage(){//Setting page up
		$("#title").hide();
		$("#p1").hide();
		$("#searchTerms").hide();
		$("#b1").hide();
		$("#list1").hide();
		
		$("#title").fadeIn(1000);
		$("#p1").fadeIn(2000);
		$("#searchTerms").fadeIn(3000);
		$("#b1").fadeIn(4000);
	}
	
	initPage();
	
	$("#searchTerms").focus(
		function(){
			$(this).css("background-color","#CCCCCC");
		}
	);
	
	$("#searchTerms").blur(
		function(){
			$(this).css("background-color", "#FFFFFF");
		}
	);
	
	$("#b1").click(
		function(){
			$("#l1").hide();
			$("#l2").hide();
			$("#l3").hide();
			$("#l4").hide();
			$("#l5").hide();

			var searchTerms = document.getElementById("searchTerms").value;
			var data;
			
			if (window.XMLHttpRequest)
			{// Newer browsers
				data = new XMLHttpRequest();
			}
			else
			{// Older browsers
				data = new ActiveXObject("Microsoft.XMLHTTP");
			}

			//Search terms are sent to server, processed and then the results are received.

			data.onreadystatechange = function(){
				if (data.readyState==4 && data.status==200){
					updateList(data.responseText);
				}
			}

			data.open("GET", "dtsServer.php?terms="+searchTerms, true);
			data.send();
		}
	);
	
	//Iterates through each result and updates top 5 list accordingly.
	function updateList(results){
		$("#list1").fadeIn();
		
		if(results==""){
			$("#l1").text("No results were found");
			$("#l1").fadeIn(1000);
			return;
		}

		var lines = results.split("\n");

		for(i=0;i<lines.length;i++){
			$("#l"+(i+1).toString()).text(lines[i]);
			$("#l"+(i+1).toString()).fadeIn(i+1*1000);
		}
	}
});

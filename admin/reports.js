function generateReport()
{
	var xhttp;
	if (window.XMLHttpRequest) {
		// code for modern browsers
		xhttp = new XMLHttpRequest();
		} else {
		// code for IE6, IE5
		xhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
		document.getElementById("results").innerHTML = this.responseText;
		}
	}
	var inputs = document.querySelectorAll("input");
	var data = "";
	for (var i = 0;i < inputs.length; i++) {
		if (inputs[i].id == "start_date" || inputs[i].id == "end_date")
			data += "&" + inputs[i].id + "=" + inputs[i].value;
		else
			data += "&" + inputs[i].id + "=" + inputs[i].checked;
	}
	var area = document.getElementById("area").value;
	var buslist = document.getElementById("buslist").value;
	var camp = document.getElementById("camp").value;
	//var buslist_type = document.getElementById("buslist_type").value;
	buslist_type = null;
	xhttp.open("GET", "/wp-content/plugins/SRBC/report_query.php?camp=" + camp + "&buslist=" + buslist + "&buslist_type="+buslist_type+ "&area="+area+data, true);
	xhttp.send();
}

//Toast Notification
function showToast(text) {
    // Get the snackbar DIV
    var x = document.getElementById("snackbar");
	x.innerText = text;
    // Add the "show" class to DIV
    x.className = "show";

    // After 3 seconds, remove the show class from DIV
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
} 
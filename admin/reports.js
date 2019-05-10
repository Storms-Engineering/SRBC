function generateReport(data)
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
		initSort();
		}
	}
	var inputs = document.querySelectorAll("input");
	hasStartDate = false;
	for (var i = 0;i < inputs.length; i++) {
		if (inputs[i].id == "start_date" || inputs[i].id == "end_date")
		{
			data += "&" + inputs[i].id + "=" + inputs[i].value;
			if (inputs[i].id == "start_date" && inputs[i].value !== "")
				hasStartDate = true;
		}
		else
			data += "&" + inputs[i].id + "=" + inputs[i].checked;
	}
	//TODO reimplement code above for this?
	var area = document.getElementById("area").value;
	var camp = document.getElementById("camp").value;
	//var start_date = document.getElementById("camp").value;
	//var end_Date = document.getElementById("camp").value;
	var buslist_type = document.getElementById("buslist_type").value;
	url =  "/wp-content/plugins/SRBC/report_query.php?camp=" + camp + "&buslist_type="+buslist_type+ "&area="+area+"&"+data;
	if (data.includes("mailing_list") && hasStartDate )
		window.open(url, '_blank');
	else if(data.includes("mailing_list"))
	{
		alert("Please pick a start date");
		return;
	}
	else
	{
	xhttp.open("GET",url, true);
	xhttp.send();
	}
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
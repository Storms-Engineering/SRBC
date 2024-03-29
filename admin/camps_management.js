function sendBalanceEmails(camp_id)
{
	if(confirm("Are you sure you want to send a balance due email to this entire camp?"))
	{
		obj = {"emails_camp_id" : camp_id}
		param = JSON.stringify(obj);
		xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				var txt = this.responseText;1
				//If an error occurs show the error from the php properly so it doesn't go away in a toast
				if (txt.includes("Error") || txt.includes("Notice") || txt.includes("Warning")){
					showToast("Error occured, please let Website Administrator know");
					document.getElementById("error").innerHTML = txt;
				}
				else
					showToast(txt);
				}
		};
	xmlhttp.open("POST", "../wp-content/plugins/SRBC/handlers/camps_handler.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("x=" + encodeURIComponent(param));
	}
	
}

function deleteCamp(ev,cmpid,nonce)
{
	if(confirm('Are you sure you want to delete?')){
		postCampAjax({'deleteid': cmpid , '_wpnonce' : nonce});
	}
	ev.stopPropagation();
}

//Opens modal showing the ability to edit a camp
function openCampModal(cmp_id) {
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
		document.getElementById('modal-content').innerHTML = this.responseText;
		modal.style.display = "block";
		}
	};
	xhttp.open("GET", "/wp-content/plugins/SRBC/camps_modal_query.php?camp_id="+cmp_id, true);
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
function postCampAjax(obj) {
	param = JSON.stringify(obj);
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
		var txt = this.responseText;1
		//If an error occurs show the error from the php properly so it doesn't go away in a toast
        if (txt.includes("Error") || txt.includes("Notice") || txt.includes("Warning")){
			showToast("Error occured, please let Website Administrator know");
			document.getElementById("error").innerHTML = txt;
		}
		else
			showToast(txt);
    }
};
xmlhttp.open("POST", "../wp-content/plugins/SRBC/update_camps.php", true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("x=" + encodeURIComponent(param));
}
function saveCampInfo(cmp_id)
{
	// Get the container element
	//This is the JSON object that we will pass to the server to store in the database
	var info = {};
	info["camp_id"] = cmp_id;
	//Get all the inputs for a camp
	var container = document.getElementsByClassName("modal-body");
	// Find its child `input` elements
	var inputs = container[0].getElementsByTagName('input');
	info["description"] = encodeURI(container[0].getElementsByClassName("description")[0].value);
	for (var j = 0; j < inputs.length; ++j) {
			info[inputs[j].name] = inputs[j].value;
			if(inputs[j].name == "closed_to_registrations" )
			{
				if(inputs[j].checked)
					info[inputs[j].name] = 1;
				else
					info[inputs[j].name] = 0;
			}
			if(inputs[j].name == "day_camp" )
			{
				if(inputs[j].checked)
					info[inputs[j].name] = 1;
				else
					info[inputs[j].name] = 0;
			}
			if(inputs[j].name == "hidden" )
			{
				if(inputs[j].checked)
					info[inputs[j].name] = 1;
				else
					info[inputs[j].name] = 0;
			}
				
	}
	console.log(info);
	postCampAjax(info);
	closeModal();
}
function addNewCamp()
{
	// Get the container element
	//This is the JSON object that we will pass to the server to store in the database
	//At the moment it will be a 2 dimensional array with the registration_id as the key to the registration data
	var info = {};
	//Get all the inputs for a camp
	var container = document.getElementById("New");
	//Get the corresponding registration ids
	// Find its child `input` elements
	var inputs = container.getElementsByTagName('input');
	info["area"] = document.querySelectorAll("select[name=area]")[0].value;
	info["description"] = container.getElementsByClassName("description")[0].value;
	for (var j = 0; j < inputs.length; ++j) {
			info[inputs[j].name] = inputs[j].value;
			if(inputs[j].name == "day_camp" )
			{
				if(inputs[j].checked)
					info[inputs[j].name] = 1;
				else
					info[inputs[j].name] = 0;
			}
			if(inputs[j].name == "hidden" )
			{
				if(inputs[j].checked)
					info[inputs[j].name] = 1;
				else
					info[inputs[j].name] = 0;
			}
	}
	console.log(info);
	postCampAjax(info);
}

function openPage(pageName,elmnt,color) {
		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for (i = 0; i < tabcontent.length; i++) {
			tabcontent[i].style.display = "none";
		}
		tablinks = document.getElementsByClassName("tablink");
		for (i = 0; i < tablinks.length; i++) {
			tablinks[i].style.backgroundColor = "";
		}
		document.getElementById(pageName).style.display = "block";
		elmnt.style.backgroundColor = color;

	}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
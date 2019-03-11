//Resend the confirmation email
function resendEmail(r_id){
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
		showToast(this.responseText);
		}
	};
	xhttp.open("GET", "/wp-content/plugins/SRBC/resend_email.php?r_id="+r_id, true);
	xhttp.send();
	
}

//Custom Modal Function
function openModal(cmpr_id) {
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
		collapsible_stuff();
		//Calculate the totals because nothing has happened
		addListeners();
		calculate_totals();
		}
	};
	xhttp.open("GET", "/wp-content/plugins/SRBC/camper_modal_query.php?camper_id="+cmpr_id, true);
	xhttp.send();
	
}

function search()
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
	};
	var query = document.getElementById("search").value;
	xhttp.open("GET", "/wp-content/plugins/SRBC/camper_database_query.php?query="+query, true);
	xhttp.send();
}

//Collapsible script
function collapsible_stuff(){
	var coll = document.getElementsByClassName("collapsible");
	var i;
	for (i = 0; i < coll.length; i++) {
	  coll[i].addEventListener("click", function() {
		this.classList.toggle("active");
		var content = this.nextElementSibling;
		if (content.style.display === "block") {
		  content.style.display = "none";
		} else {
		  content.style.display = "block";
		}
	  });
	}
}

function postAjax(obj) {
	param = JSON.stringify(obj);
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
		var txt = this.responseText;1
		//If an error occurs show the error from the php properly so it doesn't go away in a toast
		//TODO CHANGE THIS CAUSE THIS DOESN"T WORK GREAT
        if (txt.includes("Error") || txt.includes("Notice") || txt.includes("Warning")){
			showToast("Error occured, please let Website Administrator know");
			document.getElementById("results").innerHTML = txt;
		}
		else
			showToast(txt);
    }
};
xmlhttp.open("POST", "../wp-content/plugins/SRBC/update_registration.php", true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("x=" + encodeURIComponent(param));
}
function changeCamp(r_id,camper_id,old_id){
	var info = {"change_to_id" : document.getElementById(r_id).value, "registration_id" : r_id,"camper_id":camper_id,"old_id":old_id};
	console.log(info);
	postAjax(info);
	setTimeout(function(){ closeModal(); openModal(camper_id); }, 500);
}

function saveInfo()
{
	// Get the container element
	//This is the JSON object that we will pass to the server to store in the database
	//At the moment it will be a 2 dimensional array with the registration_id as the key to the registration data
	var info = {};
	//Insert camper data into json object
	var info_c = {};
	var inputs = document.getElementById("information").getElementsByTagName("input");
	for (var j = 0; j < inputs.length; ++j) {
		info_c[inputs[j].name] = inputs[j].value;
	}
	info_c["notes"] = document.getElementById("notes").value;
	info_c["id"] = document.getElementById("camper_id").innerHTML;
	info["camper"] = info_c;
	//Get all the inputs for a camp
	var containers = document.getElementsByClassName("content");
	//Get the corresponding registration ids
	var registration_ids = document.querySelectorAll("span[id=registration_id]");	
	for (var i = 0; i < containers.length; ++i) 
	{
		var info_child = {};
		// Find its child `input` elements
		var inputs = containers[i].getElementsByTagName('input');
		for (var j = 0; j < inputs.length; ++j) {
			
			if (inputs[j].name == "busride_cost"){
				//We just want to store the busride to, from or both
				info_child["busride"] = document.querySelectorAll("select[name=busride]")[i].value;
			}
			else if(inputs[j].name == "checked_in"){
				//Check the checked value
				$value = 0;
				if(inputs[j].checked)
					$value = 1;
				info_child[inputs[j].name] = $value;
			}
			else
				info_child[inputs[j].name] = inputs[j].value;
		}
		info_child["amount_due"] = containers[i].getElementsByClassName("amount_due")[0].innerText;
		info_child["payment_type"] = containers[i].getElementsByClassName("payment_type")[0].value;
		info_child["fee_type"] = containers[i].getElementsByClassName("fee_type")[0].value;
		//Make sure they enetered a fee type but exclude payments from camps that they aren't enetering information for
		if (info_child["fee_type"] == "none" && info_child["payment_type"] != "none")
		{
			alert("Please choose a fee type!");
			return;
		}
		info[registration_ids[i].innerText.toString()] = info_child;
	}
	
	console.log(info);
	postAjax(info);
	var selects = document.querySelectorAll("option[id=default]");
	for (var i=0;i<selects.length;i++){
		selects[i].selected = true;
	}
}

//Add event listeners to all the fields we want to watch for calculate_totals
function addListeners()
{
	var containers = document.getElementsByClassName("content");
	for (var i = 0; i < containers.length; ++i) 
	{
		//Special case for this select
		document.querySelectorAll("select[name=busride]")[i].addEventListener("change", calculate_totals);
		var inputs = containers[i].getElementsByTagName('input');
		for (var j = 0; j < inputs.length; ++j) 
		{
			inputs[j].addEventListener("keyup", calculate_totals);
		}
	}
	
}

function deleteRegistration(regid,camperid,campid)
{
	if(!confirm("Are you sure you want to delete?"))
		return;
	var obj = {};
	obj["deleteid"] = regid;
	obj["camp_id"] = campid;
	postAjax(obj);
	showToast("Reloading data....");
	setTimeout(function(){ closeModal(); openModal(camperid); }, 500);
}
//Recalculate costs based on what fields have been populated
function calculate_totals()
{
	var containers = document.getElementsByClassName("content");
	//Get the corresponding registration ids
	var campCosts = document.querySelectorAll("span[id=camp_cost]");
	var amount_dues = document.querySelectorAll("span[class=amount_due]");
	for (var i = 0; i < containers.length; ++i) 
	{
		var localAmountDue = -parseFloat(campCosts[i].innerHTML); 
		// Find its child `input` elements
		var inputs = containers[i].getElementsByTagName('input');
		for (var j = 0; j < inputs.length; ++j) {
			//We don't want to grab text fields and parseInt doesn't like empty strings
			if(inputs[j].name != "scholarship_type" && inputs[j].name != "counselor" && 
				inputs[j].name != "cabin" && inputs[j].name != "checked_in" && inputs[j].name != "payment_amt" &&
				inputs[j].name != "note" && inputs[j].value != "")
			{				
				if(inputs[j].name == "horse_opt")
					localAmountDue -= parseFloat(inputs[j].value);
				else if (inputs[j].name == "busride_cost")
				{
					var busride = document.querySelectorAll("select[name=busride]")[i].value;
					if (busride == "none")
						inputs[j].value = 0;
					else if(busride == "both")
						inputs[j].value = 60;
					else
						inputs[j].value = 35;
					localAmountDue -= parseFloat(inputs[j].value);
				}
				else
					localAmountDue += parseFloat(inputs[j].value);
			}
		}
		amount_dues[i].innerHTML = localAmountDue.toFixed(2);
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
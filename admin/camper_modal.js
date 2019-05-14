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

function postAjax(obj,camper_id) {
	param = JSON.stringify(obj);
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
		var txt = this.responseText;
		if(camper_id !== 0)
			openModal(camper_id);
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
	moveForward = confirm("Please note that all payments will also be changed over.	But if you are changing program areas, then the base camp fees will be assigned the wrong fee area.  Do you want to continue?");
	if(!moveForward)
		return;
	
	document.getElementById("popup_camps_background").style.display = "block";
	document.getElementById("popup_camps_button").onclick = function(){
					var info = {"change_to_id" : document.getElementById("camps").value, "registration_id" : r_id,"camper_id":camper_id,"old_id":old_id};
					console.log(info);
					postAjax(info,camper_id);
	};
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
			
			if(inputs[j].type == "checkbox"){
				//Check the checked value
				checkd = 0;
				if(inputs[j].checked)
					checkd = 1;
				info_child[inputs[j].name] = checkd;
			}
			else if(inputs[j].name !== "" && inputs[j].name !== "busride_cost" && inputs[j].name !== "horse_opt")
				info_child[inputs[j].name] = inputs[j].value;
			//We only save a 1 or 0 for the horse option
			else if(inputs[j].name == "horse_opt")
			{
				if (inputs[j].value > 0)
					info_child[inputs[j].name] = 1;
				else 
					info_child[inputs[j].name] = 0;
			}
		}
		selects = containers[i].querySelectorAll("select");
		for (var j = 0; j < selects.length; ++j) {
			if (selects[j].name !== "horse_opt")
				info_child[selects[j].name] = selects[j].value;
		}

		//Make sure they entered a fee type but exclude payments from camps that they aren't entering information for
		if (info_child["fee_type"] == "none" && info_child["payment_type"] != "none")
		{
			alert("Please choose a fee type!");
			return;
		}
		else if(info_child["auto_payment_type"] == "none" && info_child["auto_payment_amt"] != "")
		{
			alert("Please choose a payment type!");
			return;
		}
		info[registration_ids[i].innerText.toString()] = info_child;
	}
	
	console.log(info);
	postAjax(info,info_c["id"]);
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
		//Special cases for thiese selects
		document.querySelectorAll("select[name=busride]")[i].addEventListener("change", calculate_totals);
		document.querySelectorAll("select[name=horse_opt]")[i].addEventListener("change", calculate_totals);
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
	postAjax(obj,camperid);
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
		//Amount due should be positive if they owe money
		var localAmountDue = parseFloat(campCosts[i].innerHTML); 
		// Find its child `input` elements
		var inputs = containers[i].getElementsByTagName('input');
		for (var j = 0; j < inputs.length; ++j) {
			//We don't want to grab text fields and parseInt doesn't like empty strings
			//TODO: change this to loop  through an array or use a foreach this is terrible
			//TODO just check the type too duhhh
			if(inputs[j].name != "scholarship_type" && inputs[j].name != "discount_type" && inputs[j].name != "counselor" && 
				inputs[j].name != "cabin" && inputs[j].name != "checked_in" && inputs[j].name != "horse_waitlist" && inputs[j].name != "packing_list_sent" &&
				inputs[j].name != "auto_note" && inputs[j].name != "health_form" && inputs[j].name != "waitlist" && 
				inputs[j].name != "note" && inputs[j].value != "")
			{				
				
				if (inputs[j].name == "busride_cost")
				{
					var busride = document.querySelectorAll("select[name=busride]")[i].value;
					if (busride == "none")
						inputs[j].value = 0;
					else if(busride == "both")
						inputs[j].value = 60;
					else
						inputs[j].value = 35;
					localAmountDue += parseFloat(inputs[j].value);
				}
				else if(inputs[j].name == "horse_opt")
				{
					//Set the input to the value of the current select on horse_opt
					inputs[j].value = document.querySelectorAll("select[name=horse_opt]")[i].value;
					localAmountDue += parseFloat(inputs[j].value);
				}
				else
					localAmountDue -= parseFloat(inputs[j].value);
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
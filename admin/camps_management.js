var modal = document.getElementById('myModal');
function deleteCamp(ev,cmpid)
{
	
	if(confirm('Are you sure you want to delete?')){
		postAjax({'deleteid': cmpid });
	}
	ev.stopPropagation();
}

//Opens modal showing the ability to edit a camp
function openModal(cmp_id) {
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
// When the user clicks on <span> (x), close the modal
function closeModal() {
	modal.style.display = "none";
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target == document.getElementById('myModal')) {
		modal.style.display = "none";
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
function postAjax(obj) {
	param = JSON.stringify(obj);
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
		var txt = this.responseText;1
		//If an error occurs show the error from the php properly so it doesn't go away in a toast
        if (txt.includes("Saved"))
			showToast(txt);
		else
		{
			showToast("Error occured, please let Website Administrator know");
			document.getElementById("error").innerHTML = txt;
			
		}
    }
};
xmlhttp.open("POST", "../wp-content/plugins/SRBC/update_camps.php", true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("x=" + encodeURIComponent(param));
}
function saveInfo(cmp_id)
{
	// Get the container element
	//This is the JSON object that we will pass to the server to store in the database
	var info = {};
	info["camp_id"] = cmp_id;
	//Get all the inputs for a camp
	var container = document.getElementsByClassName("modal-body");
	// Find its child `input` elements
	var inputs = container[0].getElementsByTagName('input');
	for (var j = 0; j < inputs.length; ++j) {
			info[inputs[j].name] = inputs[j].value;
	}
	console.log(info);
	postAjax(info);
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
	for (var j = 0; j < inputs.length; ++j) {
			info[inputs[j].name] = inputs[j].value;
	}
	console.log(info);
	postAjax(info);
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
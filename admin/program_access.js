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

function addNoteToCamper(registration_id)
{
	note = prompt("Enter a note for this camper");
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
		//TODO show snackbar for program acesss
		//alert(this.responseText);
		showLodging();
		}
	};
	xhttp.open("GET", "/wp-content/plugins/SRBC/update_lodging.php?registration_id=" + registration_id +"&registration_notes=" + note, true);
	xhttp.send();
	
}

function removeCamper(index,object)
{
	//There is no ajax request because I assume they will be added to a different lodge which will overwrite this
	if(confirm("Are you sure you want to delete this camper from this lodge?"))
		object.deleteRow(index);
}

function updateCamperLodging(lodge,counselorPos){
	counselor = document.getElementsByName("counselor")[counselorPos].value;
	if(counselor === "")
	{
		alert("Please enter a counselor for this cabin");
		return;
	}
	assistant_counselor = document.getElementsByName("assistant_counselor")[counselorPos].value;
	
	popup.style.display = "block";
	//Callback for getting the selected cameper
	document.getElementById("popup_button").onclick = function(){
				names = document.getElementsByName("nameToAdd");
				camper_id = 0;
				for(i=0;i<names.length;i++)
				{
					if(names[i].checked)
					{
						camper_id = names[i].value;
						//Plus one because the first row is the header
						cells = document.getElementById("results_table").rows[i+1].cells;
						name = cells[0].innerHTML + " " + cells[1].innerHTML; 
						break;
					}
				}
				//We are passing counselorPos to get which table
				changeLodgingTo(lodge,camper_id,counselor,assistant_counselor,counselorPos,name);
				popup.style.display = "none";
	};
}
//TODO change camper_id to registration_id?
//BODY We are actually passing the registration_id I believe
function changeLodgingTo(lodge,camper_id,counselor,assistant_counselor,count,name)
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
		showToast(this.responseText);
		showLodging();
		}
	};
	xhttp.open("GET", "/wp-content/plugins/SRBC/update_lodging.php?cabin="+lodge + "&registration_id=" + camper_id +"&counselor=" + counselor 
					+ "&assistant_counselor="+ assistant_counselor, true);
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
		document.getElementById("results_campers").innerHTML = this.responseText;
		}
	};
	var query = document.getElementById("search").value;
	camp_id = document.getElementById("camp").value;
	xhttp.open("GET", "/wp-content/plugins/SRBC/camper_database_query.php?inner&query="+query + "&camp_id=" + camp_id , true);
	xhttp.send();
}

function showLodging()
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
	var area = document.getElementById("area").value;
	var camp_id = document.getElementById("camp").value;
	xhttp.open("GET", "/wp-content/plugins/SRBC/update_lodging.php?area="+area + "&camp_id=" + camp_id, true);
	xhttp.send();
}
//Show when loaded
showLodging();
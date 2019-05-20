function updateCamperLodging(lodge,counselorPos){
	counselor = document.getElementsByName("counselor")[counselorPos].value;
	if(counselor === "")
	{
		alert("Please enter a counselor for this cabin");
		return;
	}
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
				changeLodgingTo(lodge,camper_id,counselor,counselorPos,name);
				popup.style.display = "none";
	};
}

function changeLodgingTo(lodge,camper_id,counselor,count,name)
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
		//document.getElementById("results").innerHTML = this.responseText;
		//TODO show snackbar for program acesss
		//showLodging();
		//Add the cell to a certain table
		//Insert the new camper
		table = document.getElementsByName("cabins")[count];
		row = table.insertRow(table.rows.length);
		cell = row.insertCell(0);
		cell.innerHTML = '<span style="color:blue;font-size:medium;">' + name + '</span>';
		//Update the count using this magic number.
		table.rows[2].cells[0].innerHTML = '<span style="color:red">Total: ' + (table.rows.length  - 4) + '</span>';
		}
	};
	xhttp.open("GET", "/wp-content/plugins/SRBC/update_lodging.php?lodge="+lodge + "&registration_id=" + camper_id +"&counselor=" + counselor, true);
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
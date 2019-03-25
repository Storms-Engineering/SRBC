//Sort table function from w3schools
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("cc_table");
  switching = true;
  //Set the sorting direction to ascending:
  dir = "asc"; 
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /*check if the two rows should switch place,
      based on the direction, asc or desc:*/
      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          //if so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          //if so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      //Each time a switch is done, increase this count by 1:
      switchcount ++;      
    } else {
      /*If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again.*/
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}

function postAjax(obj) {
	param = JSON.stringify(obj);
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
		var txt = this.responseText;
		location.reload();
    }
};
xmlhttp.open("POST", "../wp-content/plugins/SRBC/update_cc.php", true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("x=" + param);
}

function allowDrop(ev) {
  ev.preventDefault();
}

function drag(ev) {
  ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
	ev.preventDefault();	
	  var file = ev.dataTransfer.files[0]; // FileList object
	  var data2 = null;
      var reader = new FileReader();
      // Closure to capture the file information.
      reader.onload = (function(theFile) {
        return function(e) {
		//This is an async function so we have to do everything in here
		// Decrypt private key using passphrase
		decryptCCs(e.target.result);
        };
      })(file);
	  reader.readAsText(file);
}
var crypt = new JSEncrypt();
async function decryptCCs(data2) {
	var passphrase = document.getElementById("pwd").value;
	
	var cells = document.getElementsByTagName("td")
	if (crypt.decrypt(cells[1].innerText) == false){
		alert("Bad password");
		location.reload();
	}
	progressBar = document.getElementById("progress");
	encrypted_text = [];
	myWorker = new Worker('../wp-content/plugins/SRBC/admin/decrypter.js');
	myWorker.onmessage = function(e) {
			cells = document.getElementsByTagName("td")
			cells[e.data[1]].innerText = e.data[0];
			progressBar.value = (e.data[1]/cells.length);
			if (cells.length == (e.data[1] + 6))
				progressBar.value = 100;
		}
	//7 is how many cells in a row
	for (i = 1; i < cells.length; i+=7){
		
		encrypted_text.push(cells[i].innerText);
	}
	myWorker.postMessage([data2,passphrase,encrypted_text]);
}
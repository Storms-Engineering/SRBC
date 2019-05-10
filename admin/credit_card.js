window.onload = initSort();

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
			//Bad password
			if(e.data[0] == false)
			{
				alert("Bad password");
				location.reload();
			}
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
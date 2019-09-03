//TODO update this javascript library to a different encryption library
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
xmlhttp.open("POST", "../wp-content/plugins/SRBC/update_staff_app.php", true);
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
		decryptSSNs(e.target.result);
        };
      })(file);
	  reader.readAsText(file);
}
async function decryptSSNs(privateKey) {
	crypt = new JSEncrypt();
	password = document.getElementById("pwd").value;
	decPKHex =  KEYUTIL.getDecryptedKeyHex(privateKey, password) 
    // Convert to PEM format for JSEncrypt
	decPKPEM =  KJUR.asn1.ASN1Util.getPEMStringFromHex(decPKHex);
	// Decrypt the tokenized data

	crypt.setPrivateKey(decPKPEM);
	var cells = document.querySelectorAll(".ssn")
	progressBar = document.getElementById("progress");
	for (i = 0; i < cells.length; i++)
	{
		//Using await since it is rather time consuming
		cells[i].innerText =  await crypt.decrypt(cells[i].innerText);
		//If it is done use 100% else, use the current percentage
		progressBar.value = i +1 == cells.length ? 100 : i/cells.length;
	}
}
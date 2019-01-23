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
        data2 = e.target.result;
		var passphrase = document.getElementById("pwd").value;
		// Decrypt private key using passphrase
		decPKHex = KEYUTIL.getDecryptedKeyHex(data2, passphrase) 
		// Convert to PEM format for JSEncrypt
		decPKPEM = KJUR.asn1.ASN1Util.getPEMStringFromHex(decPKHex);
		// Decrypt the tokenized data
		var crypt = new JSEncrypt();
		crypt.setPrivateKey(decPKPEM);
		var cells = document.getElementsByTagName("td")
		if (crypt.decrypt(cells[0].innerText) == false){
			alert("Bad password");
			location.reload();
		}
		for (i = 1; i < cells.length; i+=3){
			cells[i].innerText = crypt.decrypt(cells[i].innerText);
		}
        };
      })(file);
	  reader.readAsText(file);
     
}


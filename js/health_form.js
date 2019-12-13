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
		decryptHealthForms(e.target.result);
        };
      })(file);
	  reader.readAsText(file);
}

//shoudl be async
function decryptHealthForms(privateKey) {
	crypt = new JSEncrypt();
	password = document.getElementById("pwd").value;
	decPKHex =  KEYUTIL.getDecryptedKeyHex(privateKey, password) 
    // Convert to PEM format for JSEncrypt
	decPKPEM =  KJUR.asn1.ASN1Util.getPEMStringFromHex(decPKHex);
	// Decrypt the tokenized data

	crypt.setPrivateKey(decPKPEM);
    var progressBar = document.getElementById("progress");

    var aesKeys = document.querySelectorAll("[name=aesKey]");
    var IVs = document.querySelectorAll("[name=IV]");
    var datas = document.querySelectorAll("[name=data]");
	for (i = 0; i < aesKeys.length; i++)
	{
		//Using await since it is rather time consuming
        currentAESKey =  crypt.decrypt(aesKeys[i].value);
        console.log(currentAESKey);
        currentIV = IVs[i].value;


        var cipher = forge.cipher.createDecipher('AES-CBC', currentAESKey);
        cipher.start({iv: currentIV});
        cipher.update(forge.util.createBuffer(atob(datas[i].value)));
        cipher.finish();
        var decrypted = cipher.output.getBytes().replace(/\\/g,"");
        // outputs encrypted hex
        console.log(decrypted);
        healthObj = JSON.parse(decrypted);
        objectKeys = Object.keys(healthObj);
        for(j=0;j<objectKeys.length;j++)
        {
          element = document.querySelectorAll("[name=" + objectKeys[j] + "]")[i];
          if(objectKeys[j] === "signature_img")
            element.src = healthObj[objectKeys[j]];
          else
            element.innerText = healthObj[objectKeys[j]];
        }
        //console.log(toUTF8Array("Yeet"));


		//If it is done use 100% else, use the current percentage
		progressBar.value = i +1 == aesKeys.length ? 100 : i/aesKeys.length;
	}
}
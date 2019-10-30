//TODO update this javascript library to a different encryption library
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
    privateKey = "-----BEGIN RSA PRIVATE KEY-----\nProc-Type: 4,ENCRYPTED\nDEK-Info: DES-EDE3-CBC,81F801CE6DDBC2B5\n\nP9EwMdAZsTv3IExLbT0cEGcEakxyOH5hKRoYgIOKCBU/ZyxHgErGoAuX5DFhqORM\nAqDdGaNkk3JA8V4r/NHCiSU6ew5K4rTjyhw3GYyAtAsqwGyitrlc1Xzx8Z1cZupw\n4rPNuUOXPXy5hRSIQy3Ao6qUbmzJwxChPeXAmrOiMwNifpSht9G2szAtoIvOu6Bn\nA7astL4iWJd1baMi7z+XwuoBlDrmlfY2GB6BRL34TYcDt8vPNeYtsSTWetXcLEOM\nMNi6QuBNxIPVgJt0QURwNzggcZN3IciXRL/HYRgNRgcZLs38D/mEoin6fH9eUyIp\ne3RC4uhD/1LHbN0IpKQ3zVni3gNxhNiZcB5N3GaXmfRzHbknaZYB2z1CP06/zdFP\nlXotwOzwm36v9y3/LJXMLhrEyaP5UDyraum0IB9+dgxwgx8boyII6V/at6iCDjwn\nJhrL++CCop9u94S1X0j4w5eiW5QqNujm9iC+SpMy5Dc9FMF9S+MKyqS5AzkNYRGD\n0KFFNDW7AD1P5pWzVFnOBCKpT4HlMbP0AlSTkADF9XJGbsCAwj1IDXonajBc9Vcq\nOdLEWy95nUe2dLLR+ayashChF1n/nBxp9N850zdlhQNcL1lGV7fIv6rDslYbTa4Y\nGJXRHNYDKD94AezGgXNNPcanbxg5ZIIUmga4ScaiVJSyeiSMIRVYUaFmQHv0pWbA\nej7OycElB7uURlLCd+9puB4poYa39XyvOopIYEPPVgQX6XEeTktUknKDvUAdys7w\no5MGmC5KQIrRM0GG2rKN0pP2kvaGh8PUDkMHuLX0fX2dg7zlkV2OUvgob93D8g9l\n1aC3Xs2JOD1pM3SGDBV1SeDUhVQV3phlXETjiyPeO+sbAiyCwBsrJ7pWdFGE1oxR\nM65FNdZvSW8T8+ceJZEIFimpHG8hnQ7JJ/h1A6wg8iZ60DM2hZn75d1QmavcRQ6r\n+fksw7kqDSWiJ4MSkR4MAEN1w/In2itifVM89mS1Sh+AmYpwCw0g5MDBzL7/vkkQ\ncdW/WkdQ4Vom2AFDo5xW2trNwb9merWQZMOIDOGaI1V7mZ+XPj+lnx1xGfb1kL2C\nG/IyPKzY424dVBX1EaGr7nV44Se8Nup8YlyO8OWdSIxQstiUdEYfR7TfEWw86sgg\nI+eJWPs29TXCXUcog9GyjIk8Is3BMcsTCnmpmLXYifF3Ia72WGESd7nkqRuGFRHT\nohkUVd+Y5OFQtzX15e+29xb0G/Vo7GeOuaZ+0CHwPdJcvbT1d66cKzrbS9ggctvF\narJMucAOjOhUMLwUs8eSNlnzdZjsTS5JBGA9+8fvcPU5KKbpiBwCJJ7IMlNg8wUK\n/Eno1z6NRpDOsBkDFko3voWsQzIpQ2K7NK0a9COo+Blu49YGwiOWfkqGXoZzm6pI\njbZTCVZrNgkaBFEFInDJKHsHt4RuSHQhJ1T8YoyR/gBv8gw/VXk+jaud9RSiWAR2\nPPmGAspXnHWGZEhYqZTlB69YVEoDU32ujI0mngoPbYxzYgerHmFb/GpxQ9x17rpJ\nHHPO27juLDZJdwDOxgT5BMXREljpvaNA4DlAqsLlWr3N/6jXZ6p+SQ==\n-----END RSA PRIVATE KEY-----\n";
	crypt = new JSEncrypt();
	password = "with God all things are possible";//document.getElementById("pwd").value;
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
          else if(objectKeys[j] === "explanations")
            element.innerText = healthObj[objectKeys[j]];
          element.value = healthObj[objectKeys[j]];
        }
        //console.log(toUTF8Array("Yeet"));


		//If it is done use 100% else, use the current percentage
		progressBar.value = i +1 == aesKeys.length ? 100 : i/aesKeys.length;
	}
}
importScripts("../JSEncrypt/jsencrypt.min.js");
importScripts("../Jsrsasign/jsrsasign-all-min.js");
onmessage = function(e) {
	  console.log('Message received from main script');
	  //data[0] is the key, data[1] is the password, data[2] is the text to decrpyt
	  var crypt = new JSEncrypt();
	  var key = e.data[0];
	  var password = e.data[1];
	  var encrypted_data = e.data[2];
	  
	  
	  decPKHex = KEYUTIL.getDecryptedKeyHex(key, password) 
      // Convert to PEM format for JSEncrypt
	  decPKPEM = KJUR.asn1.ASN1Util.getPEMStringFromHex(decPKHex);
	  // Decrypt the tokenized data
		
	  crypt.setPrivateKey(decPKPEM);
	  j = 1;
	  for (i = 0; i < encrypted_data.length; i++){
		  console.log(j);
			postMessage([crypt.decrypt(encrypted_data[i]),j]);
			//7 is how many cells
			j+=7;
	}
	  
}

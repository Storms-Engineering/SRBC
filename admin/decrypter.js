importScripts("../JSEncrypt/jsencrypt.min.js");
importScripts("../Jsrsasign/jsrsasign-all-min.js");
onmessage = function(e) {
	  console.log('Message received from main script');
	  //data[0] is the crypt library data[1] is the text to decrpyt
	  var crypt = new JSEncrypt();
	  var key = e.data[0];
	  var password = e.data[1];
	  var encrypted_data = e.data[2];
	  
	  
	  decPKHex = KEYUTIL.getDecryptedKeyHex(data2, passphrase) 
      // Convert to PEM format for JSEncrypt
	  decPKPEM = KJUR.asn1.ASN1Util.getPEMStringFromHex(decPKHex);
	  // Decrypt the tokenized data
		
	  crypt.setPrivateKey(decPKPEM);
	  encrypted_data.map(function(text) {
			postMessage(crypt.decrypt(text));
		});
}

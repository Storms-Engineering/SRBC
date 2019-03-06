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
	if (crypt.decrypt(cells[0].innerText) == false){
		alert("Bad password");
		location.reload();
	}
	progressBar = document.getElementById("progress");
	//Testing so that the broswer doesn't freeze
	//TODO: this still isn't quite working
	//var wait = ms => new Promise(resolve => setTimeout(resolve, ms));
	encrypted_text = [];
	myWorker = new Worker('../wp-content/plugins/SRBC/admin/decrypter.js');
	myWorker.onmessage = function(e) {
			console.log('Message received from worker');
			console.log(e.data);
			cells = document.getElementsByTagName("td")
			cells[e.data[1]].innerText = e.data[0];
			progressBar.value = (e.data[1]/cells.length)*100;
		}
	for (i = 1; i < cells.length; i+=6){
		
		//cells[i].innerText = await decrypt(cells[i].innerText);
		encrypted_text.push(cells[i].innerText);
		//progressBar.value = (i/cells.length);
		//encrypted_text.push(cells[i].innerText);
	}
	myWorker.postMessage([data2,passphrase,encrypted_text]);
	//encrypted_text.reduce((p, i) => p.then(() => decrypt(i)).then(() => wait(5)),
   //              Promise.resolve());
	progressBar.value = 100;

}
async function decrypt(text) {
  return crypt.decrypt(text);
}
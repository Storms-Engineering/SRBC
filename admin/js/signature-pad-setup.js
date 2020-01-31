//Signature pad code
//Initialize canvas
var canvas = document.querySelector("#canvas")

var signaturePad = new SignaturePad(canvas  ,{ maxWidth : 1 } );

signaturePad.onEnd = storeImg;

function undo()
{
	var data = signaturePad.toData();
	if (data) {
		data.pop(); // remove the last dot or line
		signaturePad.fromData(data);
	}
}

//This function puts the base64 equivelent of the img in a hidden field so its gets caught in the form submittal
function storeImg()
{
	document.querySelector("[name=signature_img]").value = signaturePad.toDataURL();
}

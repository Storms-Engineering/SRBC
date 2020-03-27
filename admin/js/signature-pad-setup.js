//Signature pad code
//Initialize canvas
var canvas = document.querySelector("#canvas");
var pa_canvas = document.querySelector("#pa_canvas");

var signaturePad = new SignaturePad(canvas  ,{ maxWidth : 1 } );
var pa_signaturePad = new SignaturePad(pa_canvas  ,{ maxWidth : 1 } );

signaturePad.onEnd = storeImg;
pa_signaturePad.onEnd = pa_storeImg;

function undo()
{
	var data = signaturePad.toData();
	if (data) {
		data.pop(); // remove the last dot or line
		signaturePad.fromData(data);
	}
}

function pa_undo()
{
	var data = pa_signaturePad.toData();
	if (data) {
		data.pop(); // remove the last dot or line
		pa_signaturePad.fromData(data);
	}
}

//This function puts the base64 equivelent of the img in a hidden field so its gets caught in the form submittal
function storeImg()
{
	document.querySelector("[name=signature_img]").value = signaturePad.toDataURL();
}

//This function puts the base64 equivelent of the img in a hidden field so its gets caught in the form submittal
function pa_storeImg()
{
	document.querySelector("[name=pa_signature_img]").value = pa_signaturePad.toDataURL();
}

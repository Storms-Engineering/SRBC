//Signature pad code
//Initialize canvas
var canvas = document.querySelector("canvas");

var signaturePad = new SignaturePad(canvas);

function undo()
{
	var data = signaturePad.toData();
	if (data) {
		data.pop(); // remove the last dot or line
		signaturePad.fromData(data);
	}
}

// Adjust canvas coordinate space taking into account pixel ratio,
// to make it look crisp on mobile devices.
// This also causes canvas to be cleared.
function resizeCanvas() {
	// When zoomed out to less than 100%, for some very strange reason,
	// some browsers report devicePixelRatio as less than 1
	// and only part of the canvas is cleared then.
	var ratio =  Math.max(window.devicePixelRatio || 1, 1);
  
	// This part causes the canvas to be cleared
	canvas.width = canvas.offsetWidth * ratio;
	canvas.height = canvas.offsetHeight * ratio;
	canvas.getContext("2d").scale(ratio, ratio);
  
	// This library does not listen for canvas changes, so after the canvas is automatically
	// cleared by the browser, SignaturePad#isEmpty might still return false, even though the
	// canvas looks empty, because the internal data of this library wasn't cleared. To make sure
	// that the state of this library is consistent with visual state of the canvas, you
	// have to clear it manually.
	signaturePad.clear();
  }
  
  // On mobile devices it might make more sense to listen to orientation change,
  // rather than window resize events.
  window.onresize = resizeCanvas;
  resizeCanvas();


function calculateTotal()
{
	bus = document.getElementById("busride").value;
	//check if horses option is here
	if (document.getElementById("horse_opt") != null)
		horses = document.getElementById("horse_opt");
	else 
		horses = null
	//Add each option off of the base cost
	total = parseInt(document.getElementById("camp_cost").innerText);
	if(bus === "to" || bus === "from")
		total += 35;
	else if (bus === "both")
		total += 60;
	else if(bus === "none")
		total = parseInt(document.getElementById("camp_cost").innerText);
	//Dealing with checkbox now
	if(horses && horses.checked)
		total += parseInt(document.getElementById("horse_opt_cost").innerText);		
	document.getElementById("total").innerText = total;	
	document.getElementById("cc_amount").value = total;
}
var names = ["cc_amount", "cc_number", "cc_name", "cc_zipcode", "cc_vcode", "cc_month", "cc_year" ];
	
function validateForm()
{
	
	if (document.getElementById("retyped_email").value != document.getElementsByName("email")[0].value)
	{
		alert("Please check emails to make sure that they match!");
		return false;
	}
	if(signaturePad.isEmpty())
	{
		alert("Please draw your signature");
		return false;
	}
	if (document.getElementById("use_check").checked || document.getElementById("waitlist").checked || document.getElementById("code").value == "warden")
	{
		return true;
	}
	else
	{
		var numValidated = 0;
		for (let name of names)
		{
				 if (document.getElementsByName(name)[0].value != "")
				 {
					  numValidated++;
				 }
		}
		if (numValidated == 7)
		{
			return true;
		}
		else 
		{
			alert("Please use a credit card or check!");
			return false;
		}
	}
}

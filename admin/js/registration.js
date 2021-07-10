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
	success = checkForm();
	if(success)
	{
		//Disable submit button to hopefully stop those double clickers
		button = document.getElementById("submitButton");
		button.value = "Submitting..."; 
		setTimeout(function(){ button.disabled=true; }, 100);
		//If the request really did not work then stop and enable the submit button again
		setTimeout(function(){ window.stop(); button.disabled=false; button.value="Submit"; }, 10000);
	}
	return success;
}

function checkForm()
{
	if (document.getElementById("retyped_email").value != document.getElementsByName("email")[0].value)
	{
		alert("Please check emails to make sure that they match!");
		return false;
	}
	if(signaturePad.isEmpty())
	{
		alert("Please fill in your signature for the Health Form");
		return false;
	}
	if(pa_signaturePad.isEmpty())
	{
		alert("Please fill in your signature for the Parental Agreement");
		return false;
	}
	if (document.getElementById("use_check").checked || document.getElementById("waitlist").checked || document.getElementById("code").value == "warden")
	{
		var numValidated = 0;
		for (let name of names)
		{
				 if (document.getElementsByName(name)[0].value != "")
				 {
					  numValidated++;
				 }
		}
		if(document.getElementById("use_check").checked && numValidated == 7)
		{
			alert("Please use a check or credit card not both!")
			return false;
		}
		else
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

function moveAddress()
{
	// Get the checkbox
	var checkBox = document.getElementById("same_cc_address");
	var addresses = ["address", "zipcode", "state", "city"];
  
	for(i=0;i<addresses.length;i++)
	{
		text = document.getElementsByName(addresses[i])[0].value;
		if(checkBox.checked)
			document.getElementsByName("cc_" + addresses[i])[0].value = text;
		else
		document.getElementsByName("cc_" + addresses[i])[0].value = "";
	}
}


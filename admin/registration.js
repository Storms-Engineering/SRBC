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
	if (document.getElementById("use_check").checked || document.getElementById("waitlist").checked)
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
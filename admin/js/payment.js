function calculateTotal()
{
    total = parseInt(document.getElementById("cc_amount").value) + 
            parseInt(document.getElementById("snackshop_amt").value);	
	document.getElementById("total").innerHTML = total;
}
calculateTotal();

//Maybe will help with double clickers?
function disableButton(btn){
    button = document.getElementById(btn.id);
    button.value = "Submitting..."; 
    setTimeout(function(){ button.disabled=true; }, 100);
}
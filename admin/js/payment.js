function calculateTotal()
{
    total = parseInt(document.getElementById("cc_amount").value) + 
            parseInt(document.getElementById("snackshop_amt").value);	
	document.getElementById("total").innerHTML = total;
}
calculateTotal();
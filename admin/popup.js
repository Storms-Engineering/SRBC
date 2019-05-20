//Popup Function
// Get the popup
//
var popup = document.getElementById('popup_background');
//The open popup usually needs to be custom so we leave it out here
// When the user clicks on <span> (x), close the popup
function closePopup() {
	popup.style.display = "none";
}

// When the user clicks anywhere outside of the popup, close it
window.onclick = function(event) {
	if (event.target == document.getElementById('mypopup')) {
		popup.style.display = "none";
	}
	else if (event.target == document.getElementById('popup_background')) {
		document.getElementById('popup_background').style.display = "none";
	}
}

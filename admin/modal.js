//Modal Function
// Get the modal
//
var modal = document.getElementById('myModal');
//The open modal usually needs to be custom so we leave it out here
// When the user clicks on <span> (x), close the modal
function closeModal() {
	modal.style.display = "none";
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target == document.getElementById('myModal')) {
		modal.style.display = "none";
	}
}

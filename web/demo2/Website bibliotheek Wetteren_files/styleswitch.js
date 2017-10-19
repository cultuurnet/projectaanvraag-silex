window.onload = bindEvents;

/**
 * Alle events binden
 */
function bindEvents() {
	var textSmaller = document.getElementById('text-small');
	var textNormal = document.getElementById('text-normal');
	var textLarger = document.getElementById('text-large');
	
	if (textSmaller != null) {textSmaller.onclick = setTextSmall;}
	if (textNormal != null) {textNormal.onclick = setTextNormal;}	
	if (textLarger != null) {textLarger.onclick = setTextLarge;}	

	setTextNormal();
}


function setTextSmall() {
	setActiveStyleSheet('textSmaller'); 
	return false;
}

function setTextLarge() {
	setActiveStyleSheet('textLarger'); 
	return false;
}

function setTextNormal() {
	setActiveStyleSheet('styles'); 
	return false;
}
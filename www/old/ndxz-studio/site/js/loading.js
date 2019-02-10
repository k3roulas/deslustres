/***************************/
//@website: www.yensdesign.com
//@customization by pln				
/***************************/

var LoadBar = function(){
	this.value = 0;
	this.total = 0;
	this.loaded = 0;
};

LoadBar.prototype.setTotal = function(_total) {
	this.total = _total;
}

//Show the loading bar interface
LoadBar.prototype.show = function() {
	this.locate();
	document.getElementById("loadingZone").style.display = "block";
};
//Hide the loading bar interface
LoadBar.prototype.hide = function() {
	document.getElementById("loadingZone").style.display = "none";
};

LoadBar.prototype.run = function(){
	this.show();
};
//Center in the screen remember it from old tutorials? ;)
LoadBar.prototype.locate = function(){
	var loadingZone = document.getElementById("loadingZone");
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = loadingZone.clientHeight;
	var popupWidth = loadingZone.clientWidth;
	loadingZone.style.position = "absolute";
	loadingZone.style.top = parseInt(windowHeight/2-popupHeight/2) + "px";
	loadingZone.style.left = parseInt(windowWidth/2-popupWidth/2) + "px";
};
//Set the value position of the bar (Only 0-100 values are allowed)
LoadBar.prototype.setValue = function(value){
	if(value >= 0 && value <= 100){
		document.getElementById("progressBar").style.width = value + "%";
		document.getElementById("infoProgress").innerHTML = parseInt(value) + "%";
	}
};
//Set the bottom text value
LoadBar.prototype.setAction = function(action){
	document.getElementById("infoLoading").innerHTML = action;
};

//Called when a script is loaded. Increment the progress value and check if all files are loaded
LoadBar.prototype.loadedFile = function() {
	this.loaded++;
	var pc = (this.loaded * 100) / this.total;
	this.setValue(pc);
	//this.setAction(file + " loaded");
	//Are all files loaded?
	if(this.loaded == this.total){
		//setTimeout("progressbar.hide()",300);
	}
};

//Global var to reference from other scripts
var progressbar = new LoadBar();

//Checking resize window to recenter :)
window.onresize = function(){
	progressbar.locate();
};

//Called on body load
start = function(){
	/* progressbar.setNb(86); */
};
//Called on click reset button
restart = function(){
	window.location.reload();
};

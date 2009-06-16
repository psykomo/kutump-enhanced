/* ================================================================ 
This copyright notice must be untouched at all times.

The original version of this script and the associated (x)html
is available at http://www.stunicholls.com/various/tabbed_pages.html
Copyright (c) 2005-2007 Stu Nicholls. All rights reserved.
This script and the associated (x)html may be modified in any 
way to fit your requirements.
=================================================================== */


onload = function() {
	var e, i = 0;
	while (e = document.getElementById('lilmenu1').getElementsByTagName ('DIV') [i++]) {
		if (e.className == 'onILD' || e.className == 'offILD') {
		e.onclick = function () {
			var getEls = document.getElementsByTagName('DIV');
				for (var z=0; z<getEls.length; z++) {
				getEls[z].className=getEls[z].className.replace('show1', 'hide1');
				getEls[z].className=getEls[z].className.replace('onILD', 'offILD');
				}
			this.className = 'onILD';
			var max = this.getAttribute('title');
			document.getElementById(max).className = "show1";
			}	
			}
			}
	
	var f, a = 0;		
	while (f = document.getElementById('lilmenu2').getElementsByTagName ('DIV') [a++]) {	
		if (f.className == 'onILB' || f.className == 'offILB') {
		f.onclick = function () {
			var getEls = document.getElementsByTagName('DIV');
				for (var z=0; z<getEls.length; z++) {
				getEls[z].className=getEls[z].className.replace('show2', 'hide2');
				getEls[z].className=getEls[z].className.replace('onILB', 'offILB');
				}
			this.className = 'onILB';
			var max = this.getAttribute('title');
			document.getElementById(max).className = "show2";
			}	
			}
			}
	
	var g, u = 0;
	while (g = document.getElementById('lilmenu3').getElementsByTagName ('DIV') [u++]) {
		if (g.className == 'onNEWS' || g.className == 'offNEWS') {
		g.onclick = function () {
			var getEls = document.getElementsByTagName('DIV');
				for (var z=0; z<getEls.length; z++) {
				getEls[z].className=getEls[z].className.replace('show3', 'hide3');
				getEls[z].className=getEls[z].className.replace('onNEWS', 'offNEWS');
				}
			this.className = 'onNEWS';
			var max = this.getAttribute('title');
			document.getElementById(max).className = "show3";
			}	
			}
			}
			
	
}
var $j = jQuery.noConflict();
$j(document).ready(function(){
 function _message(obj){
  var details = '';
  if (obj!=''){
   obj = (typeof obj=='object') ? JSON.parse(obj) : obj;
   $j.each(obj, function(k, v){
    if (k=='error'){
     $j('#message').html('<div class="error">'+v+'</div>').fadeIn(1000);
    }
    if (k=='warning'){
     $j('#message').html('<div class="warning">'+v+'</div>').fadeIn(1000);
    }
    if (k=='info'){
     $j('#message').html('<div class="info">'+v+'</div>').fadeIn(1000);
    }
    if (k=='success'){
     $j('#message').html('<div class="success">'+v+'</div>').fadeIn(1000);
    }
   });
  } else {
   $j('#message').html('<div class="warning">Empty response for request</div>').fadeIn(1000);
  }
 }
 function _load(){
  // load a spinner or something
 }
});

function bust () {
	document.write = "";
	window.top.location = window.self.location;
	setTimeout(function() {
		document.body.innerHTML = '';
	}, 0);
	window.self.onload = function(evt) {
		document.body.innerHTML = '';
	};
}

if (window.top !== window.self) { // are you trying to put self in an iframe?
	try {
		if (window.top.location.host) { // this is illegal to access unless you share a non-spoofable document domain
		// fun times
		} else {
			bust(); // chrome executes this
		}
	} catch (ex) {
		bust(); // everyone executes this
	}
}

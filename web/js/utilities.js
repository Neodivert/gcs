/*
	SendPOSTRequest: Send POST parameters 'parameters' to url 'url'. Also set 
	the function 'responseFunction' as the response's handle.
*/
var request_url = 'controller.php';

function SendPOSTRequest( url, parameters, responseFunction, async ){
	// Create a new HTML request.
	if (window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{
		// code for IE6, IE5
  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  	}

	// Set the response function.
	xmlhttp.onreadystatechange = responseFunction;

	// Prepaer the request and send it.
	if( async == null ){ async == true; }
	xmlhttp.open("POST",url,async);

	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", parameters.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.send( parameters );
}


/*
	RemoveElement: Get the DOM element whose id is 'id' and remove it from
	document.
*/
function RemoveElement( id ){
	var element = document.getElementById( id );
	element.parentNode.removeChild( element );
}


/*
	CreateDiv: Create a div element with id 'id' and content 'html'. When 
	created, append it as a child of 'parent' DOM element.
*/
function CreateDiv( parent, id, html ){
	var newDiv = document.createElement('div');

	newDiv.setAttribute('id', id);
	   
	if (html) {
		newDiv.innerHTML = html;
	}

	parent.appendChild( newDiv );
}




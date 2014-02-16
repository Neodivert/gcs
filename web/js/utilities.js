/***
 * Copyright 2012, 2014
 * Garoe Dorta Perez
 * Moises J. Bonilla Caraballo (Neodivert)
 *
 * This file is part of GCS.
 *
 * GCS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * GCS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCS.  If not, see <http://www.gnu.org/licenses/>.
***/

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




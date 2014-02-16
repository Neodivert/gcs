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

/*****************************************************************************
- users.js: user's related JS-functions.
******************************************************************************/

/*
	Set div with id='newUserDialog' as a JQuery-UI Dialog for user's 
	registration.
*/
function LoadNewUserDialog()
{  
   $(function(){
		console.log(jQuery.ui);

	   // Set the div "newUserDialog" div as a dialog
		$('#newUserDialog').dialog({
		   autoOpen: false,
		   width: 600,
		   buttons: {
			   "Crear Usuario": function() {
					var name = document.getElementById('registerUserName').value;
					var pass = document.getElementById('registerUserPassword').value;

					if( (name == null) || (name == '') || (pass == null) || (pass == '') ){
						alert( 'ERROR: El nombre y/o la contrase\u00f1a estan vacios' );
					}else if( name.indexOf(' ') != -1 ){
						alert( 'El nombre no puede contener espacios' );
					}else{
						$(this).dialog( 'close' );
						// Get MD5 hash of password before send it.
						document.getElementById('registerUserPassword').value = hex_md5( pass );
						$('#newUserForm').submit();
					}

				},
				"Cerrar": function() {
					$(this).dialog( 'close' );
				}
			}
	   });

		// When the user click on "newUserButton", open the dialog.
		$('#newUserButton').click(function(){
			// Clear user's name and password inputs.
			document.getElementById('registerUserName').value = '';
			document.getElementById('registerUserPassword').value = '';

			// Open dialog.
		   	$('#newUserDialog').dialog('open');
            return false;
	      });
	   });
}


/*
	If login's name and password are not empty, submit login form.
*/
function tryLogin()
{
	// Get the name and password inserted in login.
	name = document.getElementById("loginUserName").value;
	password = document.getElementById("loginUserPassword").value;

	// Check if the name and password are not empty.
	if  ( (name == null || name =="") ||
		 (password == null || password =="") ){
		alert("La contrase\u00f1a y/o el nombre estan vacios" );
		return -1;
	}else{
		// Get MD5 hash of password before send it.
		$('#loginUserPassword').val( hex_md5( password ) );
		document.forms["loginForm"].submit();
		return 0;
	}
}


function DeleteUser( userName )
{
	// Ask for confirmation.
	var r = confirm( '¿Esta seguro de querer eliminar el usuario [' + userName + ']?' );
	if( r==false ){
		return;
	}

	document.forms["deleteUserForm"].submit();
}


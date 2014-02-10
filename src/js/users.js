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


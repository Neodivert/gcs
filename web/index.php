<!DOCTYPE html> 
<?php
	include_once "php/users.php";

	// Did the user try to login
	if (isset($_POST['loginUserName']) && isset($_POST['loginUserPassword'])){    
		// Try to login user.
		if( LoginUser($_POST['loginUserName'], $_POST['loginUserPassword']) ){
			// Login successful, go to html/main.php
			header('Location: main.php');
		}else{
			// Error when loging, show a alert.
			echo "<script language='JavaScript'>
                alert('ERROR: El usuario o la contrase\u00f1a no coinciden');
                </script>";
		}
	// Did the user try to register?
	}else if( isset($_POST['registerUserName']) && isset($_POST['registerUserPassword']) ){
		// Try to create user   
		if( CreateUser( $_POST['registerUserName'], $_POST['registerUserPassword'] )){
			// Registration successful, go to main.php
			header('Location: main.php');
		}else{
			// Error when registering, show a alert.
			echo "<script language='JavaScript'>
                alert( 'ERROR: El usuario ya existe' );
                </script>";
		}
	}
?>

<html>
	<!--                                  HEAD                                -->
	<head>
		<title>GCS - Compilador Online - Login</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="./css/login.css" />
		<link rel="stylesheet" type="text/css" href="./css/general.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.20.custom.css" /> 
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"> </script>
		<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"> </script>
		<script type="text/javascript" src="js/md5.js"> </script>
		<script type="text/javascript" src="js/users.js"> </script>
		<script type="text/javascript">
        		LoadNewUserDialog();
		</script>
	</head>

	<!--                                  BODY                                -->
	<body>
		<div class="loginPanel">
			<h1>GCS - Compilador Online</h1>
			<!--                            Login form                             -->
			<form id="loginForm" action="index.php" method="POST">
				<h3>Login</h3>
				<div id="loginTextFields">
					<p><label for="loginUserName">Nombre: </label><input type="text" id="loginUserName" name="loginUserName" /> </p>
					<p><label for="loginUserPassword">Contrase&ntilde;a: </label>
					<input type="password" id="loginUserPassword" name="loginUserPassword" /></p>
				</div>
					<input type="button" name="login" onclick="return tryLogin()" value="login" />
			</form>
			<a id="newUserButton" href="#">Registrar nuevo usuario</a>
		</div>

		<!--                             Register form                          --> 
		<div id="newUserDialog" title="Registro de nuevo usuario">
			<form id="newUserForm" action="index.php" method="POST">
				<p>Nombre: <input id="registerUserName" name="registerUserName" type="text" /></p>
				<p>Contrase&ntilde;a: <input type="password" id="registerUserPassword"  name="registerUserPassword" /></p>
			</form>
		</div>
	</body>
</html>


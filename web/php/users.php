<?php
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

	include_once "utilities.php";


	/*******************************************
	* Functions
	*******************************************/
	/*
		CreateUser: Tries to register a new user.
		On success, it sets two session variables with the user's name and id, 
		and returns 1.
		If the username is already taken, this function returns 0.
		Otherwise (error), it kills the execution.
	*/
	function CreateUser ( $name, $password )
	{
		global $users_dir;
		$user_dir = $users_dir . '/' . $name;
		
		$db_connection = ConnectToDB();

		// Prevent SQL injections
		$name = mysql_real_escape_string( $name );
		$password = mysql_real_escape_string( $password );
		 
		// Username already taken?
		$sql = mysql_query("SELECT name FROM users WHERE name = '".$name."'");
		if ($sql && mysql_num_rows($sql) >= 1 ){
			mysql_close( $db_connection );
			return 0;
		}

		// Creates the user's dir.
		$res = mkdir(  $user_dir, 0755, false );

		if( !$res ){
			$error = error_get_last();
    		die( $error['message'] );
		}

		// Creates the user's bin dir.
		$res = mkdir(  $user_dir . '/.bin', 0755, false );

		if( !$res ){
			$error = error_get_last();
    		die( $error['message'] );
		}
		 
		// Try to insert the new user in the db.
		mysql_query("INSERT INTO users (name, password) VALUES ( '$name', '$password')") or die (mysql_error());

		// Get the username id.
		$sql = mysql_query( "SELECT id FROM users where name = '".$name."'" ) or die( mysql_error() );

		// Set the %_SESSION variables and return the user id.
		$_SESSION['name'] = $name;
		$result = mysql_fetch_array($sql);
		$_SESSION['id'] = $result['id'];
		$_SESSION['dir'] = $users_dir . '/' . $_SESSION['name'];
			
		return $_SESSION['id'];
	}

	/*
		LoginUser: Check if the pair (name, password) is in the db.
		In success, this function sets two session variables with the user's
		name and id, and returns 1. Otherwhise, it returns 0.
	*/
	function LoginUser ($name, $password)
	{
		global $users_dir;
		$db_connection = ConnectToDB();

		// Prevent SQL injections
		$name = mysql_real_escape_string($name);
		$password = mysql_real_escape_string($password);


		// Search for the pair (name, password) in the db
		$sql = mysql_query("SELECT * FROM users WHERE name = '".$name."' AND password = '".	$password."' LIMIT 1") or die("Error en busqueda: " . mysql_error());

		mysql_close( $db_connection );

		// User found?
		if ( $sql && mysql_num_rows($sql) >= 1 ){
			// Success on login, set 2 session variables with the user's name and
			// id.
			$_SESSION['name'] = $name;
			$result = mysql_fetch_array($sql);
			$_SESSION['id'] = $result['id'];
			$_SESSION['dir'] = $users_dir . '/' . $_SESSION['name'];

			return 1;
		}else{
			return 0;		
		}
	}


	/*
		DeleteUser: Delete user 'userName'.
	*/
	function DeleteUser( $userName )
	{
		global $users_dir;
		$db_connection = ConnectToDB();

		// Prevent SQL injections
		$userName = mysql_real_escape_string( $userName );

		// Delete user from db.
		$sql = mysql_query("DELETE FROM users WHERE name = '".$userName."'") or die(mysql_error());

		// Delete user's dir.
		DeleteDirectory( $_SESSION['dir'] );

		mysql_close( $db_connection );
	}
	
?>

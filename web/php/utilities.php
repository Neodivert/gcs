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

	session_start();

	/*
		Configuration parameters
	*/
	$users_dir = 'users_dirs';
	

	/*
		ConnectToDB: Try to connect to "gcs" db on localhost.
	  	Returns a MySQL link identifier on success, or kills the execution
	  	on failure.
	*/
	function ConnectToDB ()
	{
		// DB connection parameters
		$db_name = ~~DB_NAME~~;
		$db_user_name = ~~DB_USER_NAME~~;
		$db_user_password = ~~DB_USER_PASSWORD~~;

		// Connect to the database server
		$db_connection = mysql_connect("localhost", $db_user_name, $db_user_password );
		if( !$db_connection ){ 
			die( 'ERROR when trying to connect to DB' );
		}

		// Select the GCS database.
		if( !mysql_select_db( $db_name ) ){
			mysql_close( $db_connection );
			die( 'ERROR when trying to select DB' );
		}

		return $db_connection;
	}


	/*
		GetUserProjects: Return an array of projects records of user with
		id 'userId'.
	*/
	function GetUserProjects ( $userId )
	{
		$db_connection = ConnectToDB();

		$res = mysql_query("SELECT * FROM projects WHERE ownerId = '".$userId."'");

		mysql_close( $db_connection );

		return $res;
	}


	/*
		CreateButton: Create a button with text 'text'. When pressed, button
		will call javascript function 'function'.
	*/
	function CreateButton( $text, $function )
	{
		echo '<input type="button" value="' . $text . '" onClick="' . $function . ';return false;" />';
	}


	/*
		CreateJSLink: Create a link ('a' element) with text 'text'. When pressed, 
		javascript function 'function' will be called.
	*/
	function CreateJSLink( $text, $function )
	{
		echo '<a href="#" onclick="' . $function . ';return false;">';
		echo $text;
		echo '</a>';
	}


	/*
		DeleteDirectory: Recursively delete directory whose path is path 'path'.
		Code taken from 
		http://www.barattalo.it/2010/02/02/recursive-remove-directory-rmdir-in-php/
	*/	
	function DeleteDirectory( $path )
	{
		$path = rtrim( $path, '/' ).'/';
		$handle = opendir( $path );

		if( !$handle ) return false;

		while( $file = readdir($handle) ){
		    if( ($file != '.') && ($file != '..') ) {
		        $fullpath = $path.$file;
				
		        if( is_dir( $fullpath ) ){ 
					DeleteDirectory( $fullpath ); 
				}else{ 
					unlink( $fullpath );
				}
		    }
		}
		closedir( $handle );
		rmdir( $path );
	}

?>
